import { execFileSync } from "node:child_process";
import { expect, test } from "@playwright/test";
import AxeBuilder from "@axe-core/playwright";

const php = (args: string[]) => {
  const dc = (process.env.DC ?? "docker compose").split(" ");
  return execFileSync(dc[0]!, [...dc.slice(1), "exec", "-T", "php", "php", ...args], {
    encoding: "utf8",
  });
};
const fixture = (action = "info", name = "") =>
  JSON.parse(php(["/workspace/scripts/teaser-fixtures.php", action, name]));
const importer = (execute = false) =>
  php([
    "bin/console",
    "nordwerk:testimonials:import-oveleon",
    "91001",
    String(fixture().importArchive),
    ...(execute ? ["--execute"] : []),
  ]);

test.beforeAll(() => fixture("cleanup"));
test.afterAll(() => fixture("cleanup"));

test("sources: news grid/carousel, next three events, categories and testimonials", async ({
  page,
}) => {
  const dependencies: string[] = [];
  page.on("request", (request) => {
    if (/swiper|jquery/i.test(request.url())) dependencies.push(request.url());
  });
  const response = await page.goto("/teasers.html");
  expect(response?.status()).toBe(200);
  const grid = page.getByRole("list", { name: "Nachrichten im Raster", exact: true });
  await expect(grid.locator("h3")).toHaveText(["Nachricht 1", "Nachricht 2", "Nachricht 3"]);
  await expect(grid.locator("img")).toHaveCount(3);
  for (const image of await grid.locator("img").all()) {
    await image.scrollIntoViewIfNeeded();
    await expect
      .poll(() => image.evaluate((img: HTMLImageElement) => img.complete && img.naturalWidth > 0))
      .toBe(true);
  }
  const carousel = page
    .locator("[data-nw-carousel]")
    .filter({ has: page.locator('[aria-label="Nachrichten im Carousel"]') });
  await expect(carousel).toHaveAttribute("data-sc-ready", "");
  await expect(carousel.locator("h3")).toHaveText(["Nachricht 1", "Nachricht 2", "Nachricht 3"]);
  await expect(
    page.getByRole("list", { name: "Die nächsten drei Termine", exact: true }).locator("h3"),
  ).toHaveText(["Termin 1", "Termin 2", "Termin 3"]);
  const recurring = page.getByRole("list", { name: "Wiederkehrende Termine", exact: true });
  await expect(recurring.locator("h3")).toHaveCount(3);
  for (const time of await recurring.locator("time").all()) {
    expect(Date.parse((await time.getAttribute("datetime"))!)).toBeGreaterThan(Date.now());
  }
  await expect(
    page.getByRole("list", { name: "Nachrichten einer Kategorie", exact: true }).locator("h3"),
  ).toHaveText(["Nachricht 1", "Nachricht 2"]);
  await expect(page.getByText("Geschützte Nachricht", { exact: true })).toHaveCount(0);
  await expect(page.getByText("Nicht veröffentlicht", { exact: true })).toHaveCount(0);
  await expect(page.getByText("Zukünftig veröffentlicht", { exact: true })).toHaveCount(0);
  await expect(page.getByText("Abgelaufen", { exact: true })).toHaveCount(0);
  await expect(page.getByText("Vergangener Termin", { exact: true })).toHaveCount(0);
  expect(await response!.text()).not.toMatch(/AggregateRating|itemprop="reviewRating"/);
  const audit = await new AxeBuilder({ page }).analyze();
  expect(audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? ""))).toEqual(
    [],
  );
  expect(dependencies).toEqual([]);
  await grid.getByRole("link", { name: "Nachricht 1", exact: true }).click();
  await expect(page.getByRole("heading", { name: "Nachricht 1", exact: true })).toBeVisible();
});

test("submission, consent, CSRF, mail and backend moderation work without JavaScript", async ({
  browser,
  request,
}) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  const name = `E2E Teaser ${Date.now()}`;
  try {
    const response = await page.goto("/submit.html");
    expect(response?.headers()["cache-control"]).toMatch(/private|no-store/);
    await expect(page.locator('[name="consent"]')).not.toBeChecked();
    const form = await page
      .locator("form")
      .evaluate((element: HTMLFormElement) =>
        Object.fromEntries([...new FormData(element)].map(([key, value]) => [key, String(value)])),
      );
    const rejected = await page.request.post("/submit.html", {
      form: { ...form, name, text: "Experience", REQUEST_TOKEN: "invalid-token", consent: "1" },
    });
    expect(rejected.status()).toBe(400);
    expect(fixture("inspect", name)).toBe(false);
    const missingConsent = await page.request.post("/submit.html", {
      form: { ...form, name, text: "Experience", consent: "" },
    });
    expect(await missingConsent.text()).toContain("Bitte stimmen Sie");
    expect(fixture("inspect", name)).toBe(false);
    const invalidStars = await page.request.post("/submit.html", {
      form: { ...form, name, text: "Experience", consent: "1", stars: "6", published: "1" },
    });
    expect(await invalidStars.text()).toContain("Sterne");
    expect(fixture("inspect", name)).toBe(false);
    await page.reload();
    await page.getByLabel("Name (Pflichtfeld)", { exact: true }).fill(name);
    await page
      .getByLabel("Ihre Erfahrung (Pflichtfeld)")
      .fill('Eine gute Erfahrung. <img src="x" onerror="alert(1)">');
    await page
      .getByLabel("E-Mail (optional, wird nicht veröffentlicht)")
      .fill("fiction-e2e@example.test");
    await page.getByLabel("Sterne (optional)", { exact: true }).selectOption("4");
    await page.locator('[name="consent"]').check();
    const submitted = await page
      .locator("form")
      .evaluate((element: HTMLFormElement) =>
        Object.fromEntries([...new FormData(element)].map(([key, value]) => [key, String(value)])),
      );
    await page.getByRole("button", { name: "Kundenstimme einreichen", exact: true }).click();
    await expect(page.getByRole("status")).toContainText("wird geprüft");
    const record = fixture("inspect", name);
    expect(record.published).toBe("");
    expect(Number(record.pid)).toBe(fixture().archive);
    expect(Number(record.consentedAt)).toBeGreaterThan(0);
    expect(record.consentText).toContain("/privacy.html");
    expect(Number(record.notifiedAt)).toBeGreaterThan(0);
    const replay = await page.request.post("/submit.html", { form: submitted });
    expect(await replay.text()).toContain("bereits gesendet");
    expect(fixture("inspect", name).id).toBe(record.id);
    const mail = await request.get("http://127.0.0.1:8122/api/v1/messages");
    expect(
      (await mail.json()).messages.some(
        (message: { Subject: string }) => message.Subject === "Neue Kundenstimme zur Prüfung",
      ),
    ).toBe(true);
    await page.goto("/teasers.html");
    await expect(page.getByRole("heading", { name, exact: true })).toHaveCount(0);
    await expect(
      page.getByRole("link", { name: "Nachricht 1", exact: true }).first(),
    ).toBeVisible();
    await expect(
      page.locator('[data-sc-track][aria-label="Kundenstimmen"] .nw-teaser-card'),
    ).toHaveCount(3);

    const admin = await browser.newPage();
    await admin.goto("/contao/login");
    await admin.getByLabel(/^(Username|Benutzername)$/).fill("admin@example.test");
    await admin.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-local-demo");
    await admin.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
    await admin.goto(`/contao?do=nw_testimonials&table=tl_nw_testimonial&act=edit&id=${record.id}`);
    await admin.locator('[name="published"][type="checkbox"]').check();
    await admin.getByRole("button", { name: "Speichern", exact: true }).click();
    await expect(admin.locator(".tl_error").first()).toBeVisible();
    expect(fixture("inspect", name).published).toBe("");
    await admin.reload();
    await admin
      .locator('[name="reviewNotes"]')
      .fill("Fiktive E2E-Stimme: Herkunft und Einwilligung geprüft.");
    await admin.locator('[name="published"][type="checkbox"]').check();
    await admin.getByRole("button", { name: "Speichern", exact: true }).click();
    await expect(admin.locator(".tl_error")).toHaveCount(0);
    expect(fixture("inspect", name).published).toBe("1");
    await page.reload();
    await expect(page.getByRole("heading", { name, exact: true })).toBeVisible();
    await expect(page.locator('[onerror], img[src="x"]')).toHaveCount(0);
    await expect(page.getByText("fiction-e2e@example.test", { exact: true })).toHaveCount(0);
    await admin.close();
  } finally {
    await context.close();
  }
});

test("Oveleon import is transactional, idempotent and always pending review", async ({ page }) => {
  expect(importer()).toContain("Dry run: 2 imported, 0 skipped");
  expect(fixture().importCount).toBe(0);
  fixture("invalid-import");
  try {
    expect(() => importer(true)).toThrow();
    expect(fixture().importCount).toBe(0);
  } finally {
    fixture("remove-invalid-import");
  }
  expect(importer(true)).toContain("Import: 2 imported, 0 skipped");
  expect(importer(true)).toContain("Import: 0 imported, 2 skipped");
  const record = fixture("inspect", "Import Demo Kundin");
  expect(record.published).toBe("");
  expect(Number(record.consentedAt)).toBe(0);
  expect(JSON.parse(record.provenance).verified).toBe("1");
  expect(Number(record.stars)).toBe(4);
  expect(record.text).toBe("Fiktive importierte Erfahrung.\nMit zweitem Absatz.");
  await page.goto("/teasers.html");
  await expect(page.getByRole("heading", { name: "Import Demo Kundin", exact: true })).toHaveCount(
    0,
  );
});

for (const colorScheme of ["light", "dark"] as const) {
  test(`new bundles: ${colorScheme} screenshots and axe`, async ({ page }) => {
    fixture("cleanup");
    await page.emulateMedia({ colorScheme, reducedMotion: "reduce" });
    await page.setViewportSize({ width: 1120, height: 1100 });
    for (const [path, bundle] of [
      ["teasers", "teasers"],
      ["submit", "testimonials"],
    ] as const) {
      await page.goto(`/${path}.html`);
      const audit = await new AxeBuilder({ page }).analyze();
      expect(
        audit.violations.filter((v) => ["serious", "critical"].includes(v.impact ?? "")),
      ).toEqual([]);
      await page.locator("#main").screenshot({
        path: `packages/${bundle}/docs/${bundle}${colorScheme === "dark" ? "-dark" : ""}.png`,
      });
    }
  });
}

test("new templates and German teaser settings are available in the backend", async ({ page }) => {
  await page.goto("/contao/login");
  await page.getByLabel(/^(Username|Benutzername)$/).fill("admin@example.test");
  await page.getByLabel(/^(Password|Passwort)$/).fill("contao-ui-local-demo");
  await page.getByRole("button", { name: /^(Login|Anmelden)$/ }).click();
  await page.goto("/contao/template-studio");
  for (const template of [
    "content_element/nw_teaser",
    "frontend_module/nw_teaser",
    "frontend_module/nw_testimonial_form",
    "component/_nw_teasers",
    "component/_nw_teaser_card",
  ]) {
    await page.getByText(template, { exact: true }).first().click();
    await expect(page.getByText("Ihr Template erstellen", { exact: true })).toBeVisible();
  }
  await page.goto(`/contao?do=article&table=tl_content&id=${fixture().article}`);
  const record = page
    .locator(".content-nw-teaser")
    .first()
    .locator("xpath=ancestor::li[@data-id]")
    .first();
  const id = await record.getAttribute("data-id");
  await page.getByRole("link", { name: `Inhaltselement ID ${id} bearbeiten`, exact: true }).click();
  await expect(page.getByRole("heading", { name: /^Pflichtfeld Quelle/ })).toBeVisible();
  await expect(page.locator('[name="nwTeaserSource"]')).toHaveValue("news");
  for (const value of ["news", "events", "testimonials"]) {
    await expect(page.locator(`[name="nwTeaserSource"] option[value="${value}"]`)).toHaveCount(1);
  }
  await expect(page.locator(".tl_error")).toHaveCount(0);
});

test("protected local images are omitted from public cards", async ({ page }) => {
  fixture("private-image");
  try {
    await page.goto("/teasers.html");
    const grid = page.getByRole("list", { name: "Nachrichten im Raster", exact: true });
    await expect(grid.locator("h3")).toHaveCount(3);
    await expect(grid.locator("img")).toHaveCount(2);
  } finally {
    fixture("restore-image");
  }
});
