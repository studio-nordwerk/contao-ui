import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: "./e2e",
  workers: 1,
  reporter: "list",
  use: { baseURL: "http://127.0.0.1:8102", locale: "de-DE", trace: "retain-on-failure" },
});
