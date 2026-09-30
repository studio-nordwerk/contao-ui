import { defineConfig } from "vite-plus";

export default defineConfig({
  lint: { ignorePatterns: ["packages/**/public/vendor/**", "app/**", "app6/**"] },
  fmt: { ignorePatterns: ["packages/**/public/vendor/**", "app/**", "app6/**", "pnpm-lock.yaml"] },
});
