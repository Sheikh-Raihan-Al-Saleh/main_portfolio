import { expect, ready, scrollTo, test } from './support/fixtures';

/**
 * Runs only in the `reduced-motion` project (prefers-reduced-motion: reduce).
 * Visitors who asked for less motion get the content without the WebGL
 * liquid layer or the custom cursor: plain images and a static gradient.
 */
test.describe('reduced motion', () => {
    test('skips the WebGL liquid layer', async ({ page, issues }) => {
        for (const path of ['/', '/founder']) {
            await page.goto(path);
            await ready(page);
            await page.waitForTimeout(2_000);
            await expect(
                page.locator('canvas[data-liquid-surface].opacity-100'),
            ).toHaveCount(0);
            await expect(page.getByTestId('custom-cursor')).toHaveCount(0);
        }

        void issues;
    });

    test('still shows the hero, the real covers and the founder photo', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await ready(page);

        await expect(page.locator('h1').first()).toBeVisible();

        const featured = page.locator('#work [data-liquid-image] img').first();
        await scrollTo(featured);
        await expect(featured).toBeVisible();
        await expect(featured).not.toHaveClass(/opacity-0/);

        await page.goto('/founder');
        await ready(page);
        await expect(
            page.locator('main img[src*="e2e/avatar"]').first(),
        ).not.toHaveClass(/opacity-0/);
        void issues;
    });
});
