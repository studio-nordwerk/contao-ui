import { expect, test } from "@playwright/test";

const selector = "[data-nw-carousel]";

test("three published children, arrows, dots and keyboard work", async ({ page }) => {
  await page.goto("/carousel.html");
  const carousel = page.locator(selector).first();
  const track = carousel.locator("[data-sc-track]");
  await expect(carousel).toHaveAttribute("data-sc-ready", "");
  await expect(track.locator(":scope > .nw-carousel-slide")).toHaveCount(3);
  await expect(page.getByText("Hidden child must not render.")).toHaveCount(0);
  await carousel.getByRole("button", { name: "Weiter", exact: true }).click();
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await track.focus();
  await track.press("End");
  await expect(carousel).toHaveAttribute("data-sc-end", "");
  await expect
    .poll(() =>
      track.evaluate((node) => Math.abs(node.scrollWidth - node.clientWidth - node.scrollLeft)),
    )
    .toBeLessThan(2);
  await track.press("Home");
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeLessThan(2);
  await track.press("ArrowRight");
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await carousel.getByRole("button", { name: "Seite 3 von 3" }).click();
  await expect(carousel).toHaveAttribute("data-sc-end", "");
});

test("responsive views follow the container width", async ({ page }) => {
  await page.goto("/carousel.html");
  const track = page.locator(selector).nth(1).locator("[data-sc-track]");
  for (const [width, views] of [
    [480, "1"],
    [800, "2"],
    [1280, "3"],
  ]) {
    await page.setViewportSize({ width: Number(width), height: 900 });
    await expect
      .poll(() =>
        track.evaluate((node) => getComputedStyle(node).getPropertyValue("--sc-per-view").trim()),
      )
      .toBe(views);
  }
});

test("without JavaScript the row scrolls with native wheel input", async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  await page.goto("/carousel.html");
  const carousel = page.locator(selector).first();
  const track = carousel.locator("[data-sc-track]");
  await expect(carousel.getByRole("button", { name: "Weiter", exact: true })).toBeHidden();
  await track.hover();
  await page.mouse.wheel(1100, 0);
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await context.close();
});

test("touch swipe uses the native scroller", async ({ browser }) => {
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    hasTouch: true,
    isMobile: true,
  });
  const page = await context.newPage();
  await page.goto("/carousel.html");
  const track = page.locator(selector).first().locator("[data-sc-track]");
  await track.scrollIntoViewIfNeeded();
  const box = await track.boundingBox();
  expect(box).not.toBeNull();
  const client = await context.newCDPSession(page);
  const y = box!.y + box!.height * 0.8;
  await client.send("Input.dispatchTouchEvent", {
    type: "touchStart",
    touchPoints: [{ x: box!.x + box!.width - 80, y }],
  });
  for (let step = 1; step <= 6; step++) {
    await page.waitForTimeout(25);
    await client.send("Input.dispatchTouchEvent", {
      type: "touchMove",
      touchPoints: [{ x: box!.x + box!.width - 80 - step * 40, y }],
    });
  }
  await client.send("Input.dispatchTouchEvent", { type: "touchEnd", touchPoints: [] });
  await expect.poll(() => track.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await context.close();
});

test("reduced motion starts autoplay paused and the pause button stays usable", async ({
  page,
}) => {
  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.goto("/carousel.html");
  const carousel = page.locator(selector).nth(2);
  await carousel.scrollIntoViewIfNeeded();
  await expect(carousel).toHaveAttribute("data-sc-ready", "");
  await expect(carousel).not.toHaveAttribute("data-sc-playing", "");
  await carousel.getByRole("button", { name: "Automatisches Blättern starten" }).click();
  await expect(carousel).toHaveAttribute("data-sc-playing", "");
  await carousel.getByRole("button", { name: "Automatisches Blättern pausieren" }).click();
  await expect(carousel).not.toHaveAttribute("data-sc-playing", "");
});

test("the landing page does not request bundle assets", async ({ page }) => {
  const requests: string[] = [];
  page.on("request", (request) => {
    if (/bundles\/nordwerk/.test(request.url())) requests.push(request.url());
  });
  await page.goto("/home.html");
  await expect(page.getByRole("heading", { level: 1 })).toBeVisible();
  expect(requests).toEqual([]);
});

test("the opt-in core Swiper keeps its children and renders without Swiper", async ({ page }) => {
  const dependencies: string[] = [];
  page.on("request", (request) => {
    if (/swiper|jquery/i.test(request.url())) dependencies.push(request.url());
  });
  await page.goto("/carousel.html");
  const carousel = page.locator(".content-swiper [data-nw-carousel]");
  await expect(carousel).toHaveAttribute("data-sc-ready", "");
  await expect(carousel.locator("[data-sc-track] > .nw-carousel-slide")).toHaveCount(3);
  await carousel.getByRole("button", { name: "Weiter", exact: true }).click();
  await expect
    .poll(() => carousel.locator("[data-sc-track]").evaluate((node) => node.scrollLeft))
    .toBeGreaterThan(100);
  expect(dependencies).toEqual([]);
});

test("nested carousels keep their own arrows, dots and status", async ({ page }) => {
  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.goto("/carousel.html");
  const outer = page.locator(selector).first();
  await expect(outer).toHaveAttribute("data-sc-ready", "");
  await outer.evaluate(async (root) => {
    const base = "/bundles/nordwerkcarousel/";
    const { getCarousel } = await import(base + "vendor/index.js");
    getCarousel(root).destroy();
    const inner = root.cloneNode(true) as HTMLElement;
    inner.id = "nested-carousel";
    root.querySelector(".nw-carousel-slide")!.replaceChildren(inner);
    const { enhanceCarousels } = await import(base + "carousel.js");
    await enhanceCarousels();
  });
  const inner = page.locator("#nested-carousel");
  const outerTrack = outer.locator(":scope > [data-sc-track]");
  const innerTrack = inner.locator(":scope > [data-sc-track]");
  await expect(outer.locator(":scope > [data-sc-dots] > button")).toHaveCount(3);
  await inner.locator(":scope > [data-sc-next]").click();
  await expect.poll(() => innerTrack.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await expect.poll(() => outerTrack.evaluate((node) => node.scrollLeft)).toBeLessThan(2);
  await expect(inner.locator(":scope > [data-sc-status]")).toContainText("2");
  await expect(outer.locator(":scope > [data-sc-status]")).not.toContainText("2");
  await outer.locator(":scope > [data-sc-next]").click();
  await expect.poll(() => outerTrack.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
});

test("dragging a nested carousel leaves its parent in place", async ({ page }) => {
  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.goto("/carousel.html");
  const outer = page.locator(selector).first();
  await expect(outer).toHaveAttribute("data-sc-ready", "");
  await outer.evaluate(async (root) => {
    const base = "/bundles/nordwerkcarousel/";
    const { getCarousel } = await import(base + "vendor/index.js");
    getCarousel(root).destroy();
    const inner = root.cloneNode(true) as HTMLElement;
    inner.id = "nested-carousel";
    root.querySelector(".nw-carousel-slide")!.replaceChildren(inner);
    const { enhanceCarousels } = await import(base + "carousel.js");
    await enhanceCarousels();
  });
  const innerTrack = page.locator("#nested-carousel > [data-sc-track]");
  await innerTrack.scrollIntoViewIfNeeded();
  const box = (await innerTrack.boundingBox())!;
  await page.mouse.move(box.x + box.width * 0.8, box.y + box.height * 0.5);
  await page.mouse.down();
  await page.mouse.move(box.x + box.width * 0.2, box.y + box.height * 0.5, { steps: 12 });
  await page.mouse.up();
  await expect.poll(() => innerTrack.evaluate((node) => node.scrollLeft)).toBeGreaterThan(100);
  await expect
    .poll(() => outer.locator(":scope > [data-sc-track]").evaluate((node) => node.scrollLeft))
    .toBeLessThan(2);
});
