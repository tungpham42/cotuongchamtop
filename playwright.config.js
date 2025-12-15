const { defineConfig, devices } = require('@playwright/test');

const HOST = process.env.PLAYWRIGHT_HOST || '127.0.0.1';
const PORT = process.env.PLAYWRIGHT_PORT || 8001;
const baseURL = process.env.PLAYWRIGHT_BASE_URL || `http://${HOST}:${PORT}`;

module.exports = defineConfig({
  testDir: './playwright/tests',
  timeout: 60_000,
  expect: {
    timeout: 15_000,
  },
  use: {
    baseURL,
    headless: true,
    trace: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: process.env.PLAYWRIGHT_NO_SERVER
    ? undefined
    : {
        command: `php artisan serve --host=${HOST} --port=${PORT}`,
        url: baseURL,
        reuseExistingServer: true,
        timeout: 120_000,
      },
});
