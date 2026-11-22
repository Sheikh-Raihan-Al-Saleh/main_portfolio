/// <reference types="node" />
import { ready, settle, test } from './support/fixtures';

/**
 * Full-page screenshots for a visual review. Skipped unless SCREENSHOT_DIR
 * is set, e.g. `SCREENSHOT_DIR=shots npx playwright test screenshots`.
 */
const dir = process.env.SCREENSHOT_DIR;

const pages = [
    ['home', '/'],
    ['about', '/about'],
    ['founder', '/founder'],
    ['projects', '/projects'],
    ['login', '/login'],
] as const;

for (const [name, path] of pages) {
    test(`screenshot ${name}`, async ({ page }, info) => {
        test.skip(!dir, 'Set SCREENSHOT_DIR to capture screenshots.');

        await page.goto(path);

        if (path !== '/login') {
            await ready(page);
            // Scroll through once so every reveal animation has played.
            const height = await page.evaluate(
                () => document.body.scrollHeight,
            );

            for (let y = 0; y < height; y += 600) {
                await page.evaluate((top) => window.scrollTo(0, top), y);
                await page.waitForTimeout(80);
            }

            await page.evaluate(() => window.scrollTo(0, 0));
        }

        await settle(page);
        await page.screenshot({
            path: `${dir}/${info.project.name}-${name}.png`,
            fullPage: path !== '/login',
        });
    });
}
