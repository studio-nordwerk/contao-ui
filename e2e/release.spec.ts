import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";
import { mkdir } from "node:fs/promises";

test("Template Studio offers overrides for all bundle entry templates", async ({ page }) => {
  await page.goto("/contao/login");
  await page.getByLabel(/^(Username|Benutzername)$/).fill("admin@example.test");
  await page.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-local-demo");
  await page.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
  await page.goto("/contao/template-studio");
  for (const template of [
    "content_element/nw_carousel",
    "content_element/nw_sheet",
    "content_element/nw_sheet_button",
    "content_element/nw_gallery",
    "frontend_module/nw_offcanvas_navigation",
    "content_element/nw_page_head",
    "content_element/nw_team",
    "component/_nw_section_media",
    "component/_nw_carousel",
    "component/_nw_sheet",
    "component/_nw_gallery",
  ]) {
    await page.getByText(template, { exact: true }).first().click();
    await expect(page.getByText("Ihr Template erstellen", { exact: true })).toBeVisible();
  }
});

for (const colorScheme of ["light", "dark"] as const) {
  for (const bundle of ["carousel", "sheet", "gallery", "sections"] as const) {
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
      } else if (bundle === "sections") {
        await page.setViewportSize({ width: 1120, height: 1500 });
        target = page.locator("#main");
        for (const image of await page.locator("img[loading=lazy]").all())
          await image.scrollIntoViewIfNeeded();
        await page.evaluate(() => scrollTo(0, 0));
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
      const path = `packages/${bundle}/docs/${bundle}${colorScheme === "dark" ? "-dark" : ""}.png`;
      // Sections: the first screen of the demo page, not the whole long article.
      if (bundle === "sections") await page.screenshot({ path });
      else await target.screenshot({ path });
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
      [5, "sections", "nwEyebrow"],
    ] as const) {
      await page.goto(`/contao?do=article&table=tl_content&id=${article}`);
      // Sections has one element type per section; the article starts with the page head.
      const element = bundle === "sections" ? "page-head" : bundle;
      const icon = page.locator(`.content-nw-${element} [data-nw-icon="${bundle}"]`).first();
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
