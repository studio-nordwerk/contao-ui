import { execFileSync } from "node:child_process";
import { expect, test } from "@playwright/test";

const fixtureCommand = (args: string[]) =>
  execFileSync(
    (process.env.DC ?? "docker compose").split(" ")[0]!,
    [
      ...(process.env.DC ?? "docker compose").split(" ").slice(1),
      "exec",
      "-T",
      "php",
      "php",
      "/workspace/scripts/audit-fixtures.php",
      ...args,
    ],
    { encoding: "utf8" },
  );
let fixture: {
  records: Record<string, number[]>;
  token: string;
  payload: string;
  articleId: number;
  buttonId: number;
  sheetId: number;
  foreignSheetId: number;
};

test.beforeAll(() => {
  fixture = JSON.parse(fixtureCommand([]));
});
test.afterAll(() => {
  if (fixture) fixtureCommand(["remove", JSON.stringify(fixture.records)]);
});

test("labels stay text and protected nested content is never shared with guests", async ({
  page,
  browser,
}) => {
  const url = `/${fixture.token}.html`;
  const anonymous = await page.goto(url);
  expect(anonymous?.status()).toBe(200);
  expect(await anonymous!.text()).not.toContain(`Private nw_`);
  await expect(
    page.locator("[data-nw-carousel]").first().locator(":scope > [data-sc-track]"),
  ).toHaveAttribute("aria-label", fixture.payload);
  await expect(page.locator("dialog h2")).toHaveText(fixture.payload);
  await expect(page.locator('img[src="x"], [onerror]')).toHaveCount(0);

  await page.goto(`/${fixture.token}-login.html`);
  await page.locator('input[name="username"]').fill(fixture.token);
  await page.locator('input[name="password"]').fill("contao-ui-audit-only");
  await page.locator('button[type="submit"], input[type="submit"]').click();
  await expect(page).toHaveURL(new RegExp(`${fixture.token}\\.html$`));
  const personal = await page.reload();
  expect(await personal!.text()).toContain(`Private nw_carousel ${fixture.token}`);
  expect(await personal!.text()).toContain(`Private nw_sheet ${fixture.token}`);
  expect(personal!.headers()["cache-control"]).toMatch(/private|no-store/);
  expect(personal!.headers()["cache-control"]).not.toMatch(/(?:^|,)\s*public\b/);

  const guest = await browser.newContext();
  try {
    const otherPage = await guest.newPage();
    const response = await otherPage.goto(url);
    expect(response?.status()).toBe(200);
    expect(await response!.text()).not.toContain("Private nw_");
  } finally {
    await guest.close();
  }
});

test("a mounted editor cannot select or submit a foreign sheet", async ({ page }) => {
  await page.goto("/contao/login");
  await page.getByLabel(/^(Username|Benutzername)$/).fill(`${fixture.token}-editor`);
  await page.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-audit-only");
  await page.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
  await page.goto(`/contao?do=article&table=tl_content&id=${fixture.articleId}`);
  await page
    .getByRole("link", { name: `Inhaltselement ID ${fixture.buttonId} bearbeiten`, exact: true })
    .click();
  const target = page.locator('select[name="nwSheetTarget"]');
  await expect(target).toHaveValue(String(fixture.sheetId));
  await expect(target.locator(`option[value="${fixture.foreignSheetId}"]`)).toHaveCount(0);
  await target.evaluate((select: HTMLSelectElement, foreignId) => {
    select.add(new Option("Tampered foreign target", String(foreignId)));
    select.value = String(foreignId);
  }, fixture.foreignSheetId);
  await page.getByRole("button", { name: "Speichern", exact: true }).click();
  await expect(page.locator(".tl_error").first()).toBeVisible();
  await page.reload();
  await expect(target).toHaveValue(String(fixture.sheetId));

  const form = await page
    .locator("#tl_content")
    .evaluate((element: HTMLFormElement) =>
      Object.fromEntries([...new FormData(element)].map(([name, value]) => [name, String(value)])),
    );
  const rejected = await page.request.post(page.url(), {
    form: {
      ...form,
      REQUEST_TOKEN: "invalid-audit-token",
      nwSheetButtonLabel: "CSRF must not save",
      save: "Speichern",
    },
  });
  expect(rejected.status()).toBe(400);
  await page.reload();
  await expect(page.locator('[name="nwSheetButtonLabel"]')).toHaveValue("Audit open");
});
