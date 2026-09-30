import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";
import { mkdir } from "node:fs/promises";

for (const colorScheme of ["light", "dark"] as const) {
  for (const bundle of ["carousel", "sheet", "gallery"] as const) {
    test(`${bundle}: ${colorScheme} release screenshot and accessibility`, async ({ page }) => {
      await page.emulateMedia({ colorScheme, reducedMotion: "reduce" });
      await page.setViewportSize({ width: 1120, height: 1200 });
      const errors: string[] = [];
      page.on("pageerror", (error) => errors.push(error.message));
      await page.goto(`/${bundle}.html`);
      let target = page.locator(`.content-nw-${bundle}`).first();
      if (bundle === "carousel") {
        await expect(target.locator("[data-nw-carousel]")).toHaveAttribute("data-sc-ready", "");
      } else if (bundle === "sheet") {
        await page.getByRole("button", { name: "Zentrierter Dialog öffnen", exact: true }).click();
        target = page.getByRole("dialog", { name: "Zentrierter Dialog", exact: true });
        await expect(target).toBeVisible();
        await expect(target.locator(".ss-panel")).toHaveCSS(
          "background-color",
          colorScheme === "dark" ? "rgb(18, 18, 18)" : "rgb(255, 255, 255)",
        );
      } else {
        await expect(target.locator("[data-nw-gallery]")).toHaveAttribute(
          "data-nw-gallery-ready",
          "",
        );
        await target.scrollIntoViewIfNeeded();
        for (const image of await target.locator(".nw-gallery-item img").all()) {
          await image.scrollIntoViewIfNeeded();
          await expect
            .poll(() =>
              image.evaluate((node: HTMLImageElement) => node.complete && node.naturalWidth > 0),
            )
            .toBe(true);
        }
      }
      const audit = await new AxeBuilder({ page }).analyze();
      expect(
        audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
      ).toEqual([]);
      expect(errors).toEqual([]);
      await mkdir(`packages/${bundle}/docs`, { recursive: true });
      await target.screenshot({
        path: `packages/${bundle}/docs/${bundle}${colorScheme === "dark" ? "-dark" : ""}.png`,
      });
    });
  }

  test(`backend: ${colorScheme} native widgets, previews and icons`, async ({ page }, testInfo) => {
    await page.emulateMedia({ colorScheme, reducedMotion: "reduce" });
    await page.setViewportSize({ width: 1280, height: 900 });
    const errors: string[] = [];
    const failedAssets: string[] = [];
    page.on("pageerror", (error) => errors.push(error.message));
    page.on("response", (response) => {
      if (/\/(assets|bundles)\//.test(response.url()) && response.status() >= 400)
        failedAssets.push(response.url());
    });
    await page.goto("/contao/login");
    await page.getByLabel(/^(Username|Benutzername)$/).fill("admin@example.test");
    await page.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-local-demo");
    await page.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
    await expect(page.getByRole("heading", { name: "Dashboard", exact: true })).toBeVisible();
    await expect(page.locator("html")).toHaveAttribute("data-color-scheme", colorScheme);
    for (const [article, bundle, field] of [
      [2, "carousel", "nwCarouselLabel"],
      [3, "sheet", "nwSheetLabel"],
      [4, "gallery", "nwGalleryLabel"],
    ] as const) {
      await page.goto(`/contao?do=article&table=tl_content&id=${article}`);
      const icon = page.locator(`.content-nw-${bundle} [data-nw-icon="${bundle}"]`).first();
      await expect(icon).toBeVisible();
      await expect(icon).toHaveCSS(
        "background-color",
        colorScheme === "dark" ? "rgb(221, 221, 221)" : "rgb(34, 34, 34)",
      );
      await expect(icon).toHaveCSS("mask-image", new RegExp(`${bundle}\\.svg`));
      const record = icon.locator("xpath=ancestor::li[@data-id]").first();
      const id = await record.getAttribute("data-id");
      expect(id).toMatch(/^\d+$/);
      await page
        .getByRole("link", { name: `Inhaltselement ID ${id} bearbeiten`, exact: true })
        .click();
      await expect(page.locator(`[name="${field}"]`)).toBeVisible();
      await expect(page.locator(".tl_error")).toHaveCount(0);
      if (bundle === "gallery") {
        await expect(page.getByLabel("Galeriebezeichnung", { exact: false })).toBeVisible();
        await expect(page.locator("[name=nwGalleryLayout]")).toHaveValue("grid");
        await expect(page.locator("[name=multiSRC]")).toBeAttached();
      }
      await page.screenshot({
        path: testInfo.outputPath(`backend-${bundle}-${colorScheme}.png`),
        fullPage: true,
      });
    }
    expect(errors).toEqual([]);
    expect(failedAssets).toEqual([]);
  });
}
