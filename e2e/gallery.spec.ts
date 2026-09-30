import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

test.use({ reducedMotion: "reduce" });

for (const [index, label, first] of [
  [0, "Raster", "Geometrische Studie 8"],
  [1, "Wechselnde Formate", "Geometrische Studie 1"],
  [2, "Bilderleiste", "Geometrische Studie 8"],
] as const) {
  test(`${label}: eight images, Contao order, responsive pictures and lightbox`, async ({
    page,
  }) => {
    await page.goto("/gallery.html");
    const gallery = page.locator("[data-nw-gallery]").nth(index);
    await expect(gallery).toHaveAttribute("data-nw-gallery-ready", "");
    const items = gallery.locator(".nw-gallery-item");
    await expect(items).toHaveCount(8);
    await expect(items.first().getByRole("img")).toHaveAttribute("alt", first);
    await expect(items.first().locator("source").first()).toHaveAttribute("srcset", /\S+/);
    await expect(items.first().getByRole("img")).toHaveAttribute("loading", "lazy");
    await expect(items.first().locator("figcaption")).toContainText(/Studie [18] – Form und Farbe/);
    const trigger = gallery.locator("[data-nw-gallery-open]").first();
    await trigger.click();
    const dialog = page.getByRole("dialog", { name: label, exact: true });
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole("img")).toHaveCount(1);
    await expect(dialog.getByRole("img")).toHaveAttribute("alt", first);
    const box = await dialog.locator(".ss-panel").boundingBox();
    expect(box?.width).toBe(page.viewportSize()?.width);
    expect(box?.height).toBe(page.viewportSize()?.height);
    await dialog.getByRole("button", { name: "Weiter", exact: true }).click();
    await expect(dialog.getByRole("img")).not.toHaveAttribute("alt", first);
    await page.keyboard.press("End");
    await expect(dialog.locator("[data-nw-carousel]")).toHaveAttribute("data-sc-end", "");
    await page.keyboard.press("Home");
    await expect(dialog.getByRole("img")).toHaveAttribute("alt", first);
    const audit = await new AxeBuilder({ page }).analyze();
    expect(
      audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
    ).toEqual([]);
    await page.keyboard.press("Escape");
    await expect(dialog).toBeHidden();
    await expect(trigger).toBeFocused();
  });
}

test("a later image opens directly and reopening resets the chosen position", async ({ page }) => {
  await page.goto("/gallery.html");
  const gallery = page.locator("[data-nw-gallery]").first();
  await expect(gallery).toHaveAttribute("data-nw-gallery-ready", "");
  const dialog = gallery.locator("dialog");
  for (const index of [4, 1, 7]) {
    const trigger = gallery.locator("[data-nw-gallery-open]").nth(index);
    const alt = await trigger.locator("img").getAttribute("alt");
    await trigger.click();
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole("img")).toHaveAttribute("alt", alt!);
    await dialog.getByRole("button", { name: "Schließen", exact: true }).click();
    await expect(dialog).toBeHidden();
    await expect(trigger).toBeFocused();
  }
});

test("lightbox navigation leaves modified keyboard shortcuts to the browser", async ({ page }) => {
  await page.goto("/gallery.html");
  const gallery = page.locator("[data-nw-gallery]").first();
  await expect(gallery).toHaveAttribute("data-nw-gallery-ready", "");
  const trigger = gallery.locator("[data-nw-gallery-open]").nth(4);
  const alt = await trigger.locator("img").getAttribute("alt");
  await trigger.click();
  const dialog = gallery.locator("dialog");
  await dialog.getByRole("button", { name: "Schließen", exact: true }).focus();
  const prevented = await dialog.evaluate((element) => {
    const event = new KeyboardEvent("keydown", {
      key: "Home",
      ctrlKey: true,
      bubbles: true,
      cancelable: true,
    });
    element.dispatchEvent(event);
    return event.defaultPrevented;
  });
  expect(prevented).toBe(false);
  await expect(dialog.getByRole("img")).toHaveAttribute("alt", alt!);
  await page.keyboard.press("Home");
  await expect(dialog.getByRole("img")).not.toHaveAttribute("alt", alt!);
});

test("the mobile lightbox swipes through images and fits the viewport", async ({ browser }) => {
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    hasTouch: true,
    isMobile: true,
    reducedMotion: "reduce",
  });
  const page = await context.newPage();
  await page.goto("/gallery.html");
  await expect(page.locator("[data-nw-gallery]").first()).toHaveAttribute(
    "data-nw-gallery-ready",
    "",
  );
  await page.locator("[data-nw-gallery-open]").first().click();
  const dialog = page.getByRole("dialog", { name: "Raster", exact: true });
  const track = dialog.locator("[data-sc-track]");
  const box = await track.boundingBox();
  expect(box).not.toBeNull();
  const client = await context.newCDPSession(page);
  const y = box!.y + box!.height * 0.8;
  await client.send("Input.dispatchTouchEvent", {
    type: "touchStart",
    touchPoints: [{ x: box!.x + box!.width - 50, y }],
  });
  for (let step = 1; step <= 6; step++) {
    await page.waitForTimeout(25);
    await client.send("Input.dispatchTouchEvent", {
      type: "touchMove",
      touchPoints: [{ x: box!.x + box!.width - 50 - step * 40, y }],
    });
  }
  await client.send("Input.dispatchTouchEvent", { type: "touchEnd", touchPoints: [] });
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await expect(dialog.getByRole("img")).not.toHaveAttribute("alt", "Geometrische Studie 8");
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBe(390);
  await dialog.getByRole("button", { name: "Schließen", exact: true }).click();
  await expect(dialog).toBeHidden();
  await context.close();
});

test("without JavaScript thumbnails link to the full image and the rail scrolls", async ({
  browser,
}) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  await page.goto("/gallery.html");
  const link = page.locator("[data-nw-gallery-open]").first();
  const href = await link.getAttribute("href");
  await link.click();
  await expect(page).toHaveURL(new RegExp(href!.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")));
  expect((await page.request.get(page.url())).headers()["content-type"]).toMatch(/^image\//);
  await page.goto("/gallery.html");
  const track = page.locator(".nw-gallery--rail > .sc > [data-sc-track]");
  await track.hover();
  await page.mouse.wheel(1100, 0);
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await context.close();
});

test("disabled lightbox and plain grids need no interactive assets of their own", async ({
  page,
}) => {
  await page.goto("/gallery.html");
  const gallery = page.locator("[data-nw-gallery]").last();
  await expect(gallery.locator(".nw-gallery-item")).toHaveCount(1);
  await expect(gallery.locator("dialog, [data-nw-gallery-open]")).toHaveCount(0);
  for (const asset of [
    "nordwerkcarousel/carousel.js",
    "nordwerksheet/sheet.js",
    "nordwerkgallery/gallery.js",
  ]) {
    await expect(page.locator(`script[src*="${asset}"]`)).toHaveCount(1);
  }
});
