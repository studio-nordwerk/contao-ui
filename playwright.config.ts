import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: "./e2e",
  workers: 1,
  reporter: "list",
  use: {
    // Lokal mit Chromium-Sandbox (Playwright schaltet sie sonst ab), CI unverändert.
    launchOptions: { chromiumSandbox: !process.env.CI },
    baseURL: `http://127.0.0.1:${process.env.CONTAO_UI_PORT ?? "8101"}`,
    locale: "de-DE",
    trace: "retain-on-failure",
  },
});
