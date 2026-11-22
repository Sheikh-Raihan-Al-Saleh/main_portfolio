/// <reference types="node" />
import { defineConfig, devices } from '@playwright/test';

/**
 * End-to-end tests in a real browser against an isolated database.
 *
 * The suite never touches the development database: global setup builds
 * `portfolio_e2e` from E2eSeeder, and a dedicated PHP server
 * serves the app (port 8011) with DB_DATABASE pointed at it. Assets come from
 * `npm run build`, so build before running: `npm run test:e2e`.
 */
const PORT = 8011;
const BASE_URL = `http://127.0.0.1:${PORT}`;

export const e2eEnv = {
    DB_DATABASE: 'portfolio_e2e',
    APP_URL: BASE_URL,
    // Sessions and cache live in the e2e database, never the real one.
    SESSION_DRIVER: 'database',
    CACHE_STORE: 'database',
    MAIL_MAILER: 'array',
};

export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/support/global-setup.ts',
    fullyParallel: true,
    // PHP's built-in server handles one request at a time on Windows, so
    // more browsers only queue up behind it.
    workers: process.platform === 'win32' ? 2 : 4,
    retries: process.env.CI ? 1 : 0,
    timeout: 45_000,
    expect: { timeout: 10_000 },
    reporter: [
        ['list'],
        ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ],
    use: {
        baseURL: BASE_URL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'desktop',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 900 },
            },
            testIgnore: /motion\.spec\.ts/,
        },
        {
            name: 'mobile',
            use: { ...devices['Pixel 7'] },
            testIgnore: /motion\.spec\.ts/,
        },
        {
            name: 'reduced-motion',
            use: {
                ...devices['Desktop Chrome'],
                contextOptions: { reducedMotion: 'reduce' },
            },
            testMatch: /motion\.spec\.ts/,
        },
    ],
    webServer: {
        // php -S directly (not artisan serve), so the env below reaches PHP.
        // Laravel's router script resolves index.php from the working
        // directory, so the server runs from inside public/ (as artisan serve does).
        command: `php -S 127.0.0.1:${PORT} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
        cwd: 'public',
        stdout: 'ignore',
        stderr: 'pipe',
        url: `${BASE_URL}/up`,
        reuseExistingServer: false,
        timeout: 60_000,
        env: { ...e2eEnv, PHP_CLI_SERVER_WORKERS: '8' },
    },
});
