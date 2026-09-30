import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

test.use({ reducedMotion: "reduce" });

for (const title of [
  "Sheet von unten",
  "Seitenleiste links",
  "Seitenleiste rechts",
  "Zentrierter Dialog",
]) {
  test(`${title}: opens, closes with Escape and restores focus`, async ({ page }) => {
    await page.goto("/sheet.html");
    const trigger = page.getByRole("button", { name: `${title} öffnen`, exact: true });
    const id = await trigger.getAttribute("commandfor");
    await expect(page.locator(`#${id}`)).toHaveAttribute("data-ss-ready", "");
    await trigger.click();
    const dialog = page.getByRole("dialog", { name: title, exact: true });
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole("link", { name: "Zum Carousel" })).toBeVisible();
    const audit = await new AxeBuilder({ page }).analyze();
    expect(
      audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
    ).toEqual([]);
    await page.keyboard.press("Escape");
    await expect(dialog).toBeHidden();
    await expect(trigger).toBeFocused();
  });
}

test("backdrop tap closes and restores focus", async ({ page }) => {
  await page.goto("/sheet.html");
  const trigger = page.getByRole("button", { name: "Zentrierter Dialog öffnen", exact: true });
  await trigger.click();
  const dialog = page.getByRole("dialog", { name: "Zentrierter Dialog", exact: true });
  await expect(dialog).toBeVisible();
  await page.mouse.click(5, 5);
  await expect(dialog).toBeHidden();
  await expect(trigger).toBeFocused();
});

test("native invoker commands open and close without JavaScript", async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, reducedMotion: "reduce" });
  const page = await context.newPage();
  await page.goto("/sheet.html");
  const trigger = page.getByRole("button", { name: "Zentrierter Dialog öffnen", exact: true });
  await trigger.click();
  const dialog = page.getByRole("dialog", { name: "Zentrierter Dialog", exact: true });
  await expect(dialog).toBeVisible();
  await dialog.getByRole("button", { name: "Schließen", exact: true }).click();
  await expect(dialog).toBeHidden();
  await expect(trigger).toBeFocused();
  await trigger.click();
  await page.keyboard.press("Escape");
  await expect(dialog).toBeHidden();
  await context.close();
});

test("non-dismissible dialogs require an explicit completion button", async ({ page }) => {
  await page.goto("/sheet.html");
  await page.getByRole("button", { name: "Bewusst schließen öffnen" }).click();
  const dialog = page.getByRole("dialog", { name: "Bewusst schließen", exact: true });
  await expect(dialog).toBeVisible();
  await page.keyboard.press("Escape");
  await expect(dialog).toBeVisible();
  await page.mouse.click(5, 5);
  await expect(dialog).toBeVisible();
  await dialog.getByRole("button", { name: "Abschließen" }).click();
  await expect(dialog).toBeHidden();
});

test("mobile offcanvas renders core navigation and back closes it", async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto("/sheet.html");
  const menu = page.getByRole("button", { name: "Menü", exact: true });
  const id = await menu.getAttribute("commandfor");
  await expect(page.locator(`#${id}`)).toHaveAttribute("data-ss-ready", "");
  await menu.click();
  const dialog = page.getByRole("dialog", { name: "Navigation", exact: true });
  await expect(dialog.getByRole("link", { name: "Carousel", exact: true })).toBeVisible();
  await page.goBack();
  await expect(dialog).toBeHidden();
  await expect(menu).toBeFocused();
  await expect(page).toHaveURL(/\/sheet\.html$/);
  await menu.click();
  await dialog.getByRole("link", { name: "Carousel", exact: true }).click();
  await expect(page).toHaveURL(/\/carousel\.html$/);
});
