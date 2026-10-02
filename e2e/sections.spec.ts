import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

const elements = [
  "nw-page-head",
  "nw-promises",
  "nw-split",
  "nw-split",
  "nw-figures",
  "nw-features",
  "nw-steps",
  "nw-team",
  "nw-team",
  "nw-logos",
  "nw-contact",
  "nw-callout",
];

for (const colorScheme of ["light", "dark"] as const) {
  for (const width of [390, 1120]) {
    test(`sections: ${colorScheme}, ${width}px, neutral theme`, async ({ page }) => {
      await page.emulateMedia({ colorScheme, reducedMotion: "reduce" });
      await page.setViewportSize({ width, height: 900 });
      const errors: string[] = [];
      page.on("pageerror", (error) => errors.push(error.message));
      await page.goto("/sections.html");
      const types = await page
        .locator("#main .mod_article > [class*='content-']")
        .evaluateAll((nodes) =>
          nodes.map((node) => [...node.classList].find((name) => name.startsWith("content-"))),
        );
      expect(types).toEqual(elements.map((name) => `content-${name}`));
      await expect(page.locator("link[href*='nordwerksections/sections.css']")).toHaveCount(1);
      await expect(page.getByRole("heading", { level: 1 })).toHaveText("Eine kleine Werkstatt.");
      await expect(page.locator(".nw-steps__number")).toHaveText(["1", "2", "3", "4"]);
      await expect(page.locator(".nw-features__list svg")).toHaveCount(4);
      await expect(page.locator(".nw-team--grid .nw-person")).toHaveCount(3);
      await expect(page.locator(".nw-team--carousel [data-nw-carousel]")).toHaveAttribute(
        "data-sc-ready",
        "",
      );
      await expect(page.locator(".nw-logos img")).toHaveCount(4);
      const mail = page.locator(".nw-contact a[href^='mailto:']");
      await expect(mail).toHaveText("werkstatt@example.test");
      await expect(page.locator(".nw-contact__route a")).toHaveAttribute(
        "href",
        "https://www.openstreetmap.org/search?query=Musterweg%201%2C%2010115%20Berlin",
      );
      // The neutral fallbacks follow the text colour: the button keeps contrast in both schemes.
      const button = page.locator(".nw-callout .nw-button");
      const [background, color] = await button.evaluate((node) => [
        getComputedStyle(node).backgroundColor,
        getComputedStyle(node).color,
      ]);
      expect(background).not.toEqual(color);
      for (const image of await page.locator("img[loading=lazy]").all())
        await image.scrollIntoViewIfNeeded();
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(
        true,
      );
      const audit = await new AxeBuilder({ page }).analyze();
      expect(
        audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
      ).toEqual([]);
      expect(errors).toEqual([]);
    });
  }
}

test("sections: hero has one eager picture and two buttons", async ({ page }) => {
  await page.goto("/sections-hero.html");
  await expect(page.getByRole("heading", { level: 1 })).toHaveText("Ein Hero für die Startseite.");
  await expect(page.locator(".nw-hero img")).toHaveAttribute("fetchpriority", "high");
  await expect(page.locator(".nw-section__actions a")).toHaveText(["Carousel", "Sheet"]);
  const audit = await new AxeBuilder({ page }).analyze();
  expect(audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? ""))).toEqual(
    [],
  );
});

test("sections: backend forms open and save in German and English", async ({ page }) => {
  await page.goto("/contao/login");
  await page.getByLabel(/^(Username|Benutzername)$/).fill("admin@example.test");
  await page.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-local-demo");
  await page.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
  await page.goto("/contao?do=article&table=tl_content&id=5");
  await expect(page.locator('[data-nw-icon="sections"]').first()).toBeVisible();
  const ids = await page
    .locator("li[data-id]")
    .evaluateAll((nodes) => nodes.map((node) => node.getAttribute("data-id")));
  expect(ids.length).toBeGreaterThanOrEqual(elements.length);
  for (const id of ids) {
    await page.goto(`/contao?do=article&table=tl_content&id=${id}&act=edit`);
    await expect(page.locator(".tl_error")).toHaveCount(0);
    await page.locator("#save").click();
    await expect(page.locator(".tl_error")).toHaveCount(0);
  }
  await page.goto(`/contao?do=article&table=tl_content&id=${ids[5]}&act=edit`);
  await expect(page.getByText("Merkmale", { exact: true }).first()).toBeVisible();
  await expect(page.locator("select[name$='[icon]']").first()).toHaveValue("leaf");
});
