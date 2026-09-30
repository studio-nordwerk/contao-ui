import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

for (const [path, heading] of [
  ["home", "Native Bausteine für Contao"],
  ["carousel", "Carousel"],
  ["sheet", "Dialog / Sheet"],
  ["gallery", "Galerie"],
]) {
  test(`${path}: Contao frontend is accessible and dependency-free`, async ({ page }) => {
    const dependencies: string[] = [];
    page.on("request", (request) => {
      if (/swiper|jquery/i.test(request.url())) dependencies.push(request.url());
    });
    const response = await page.goto(`/${path}.html`);
    expect(response?.status()).toBe(200);
    await expect(page.getByRole("heading", { name: heading, exact: true })).toBeVisible();
    const audit = await new AxeBuilder({ page }).analyze();
    expect(
      audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
    ).toEqual([]);
    expect(dependencies).toEqual([]);
  });
}
