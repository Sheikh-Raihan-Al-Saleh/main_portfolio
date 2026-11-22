import { expect, inertiaPage, test } from './support/fixtures';

// These tests are about the boot screen itself, so it is not pre-dismissed.
test.use({ skipLoader: false });

/**
 * Records every progress value the loader displays, so the test can check it
 * reached 100% even though that frame is only on screen for a moment.
 */
test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
        const seen: string[] = [];
        (window as unknown as { __loaderProgress: string[] }).__loaderProgress =
            seen;
        new MutationObserver(() => {
            const text = document
                .querySelector('[data-testid="site-loader"] .tabular-nums')
                ?.textContent?.trim();

            if (text && seen.at(-1) !== text) {
                seen.push(text);
            }
        }).observe(document, {
            subtree: true,
            childList: true,
            characterData: true,
        });
    });
});

test.describe('initial loader', () => {
    test('shows real brand data, completes, and gets out of the way', async ({
        page,
        issues,
    }) => {
        await page.goto('/');

        const loader = page.getByTestId('site-loader');
        await expect(loader).toBeVisible();

        const { props } = await inertiaPage(page);
        const company = props.company as { name: string };
        const stats = props.stats as { projects: number | null };

        // Brand name, the uploaded logo, and boot lines built from live data.
        await expect(
            loader.getByText(company.name, { exact: true }),
        ).toBeVisible();
        await expect(loader.locator('img[src*="e2e/logo"]')).toBeVisible();

        if (stats.projects) {
            await expect(
                loader.getByText(`${stats.projects} products in production`),
            ).toBeVisible();
        }

        await expect(loader).toHaveCount(0, { timeout: 8_000 });

        // Progress climbed and finished at 100% before it left.
        const progress = await page.evaluate(
            () =>
                (window as unknown as { __loaderProgress: string[] })
                    .__loaderProgress,
        );
        expect(progress.length).toBeGreaterThan(2);
        expect(progress.at(-1)).toBe('100%');

        // The page underneath is usable once it leaves.
        await page.locator('header a').first().hover();
        void issues;
    });

    test('is shown once per session, not on every navigation', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await expect(page.getByTestId('site-loader')).toHaveCount(0, {
            timeout: 10_000,
        });

        await page.goto('/about');
        await page.locator('#main').waitFor();
        // A brief server-rendered frame is fine; it must be gone almost at once.
        await expect(page.getByTestId('site-loader')).toHaveCount(0, {
            timeout: 1_000,
        });
        void issues;
    });

    test('the founder face boots with founder data', async ({
        page,
        issues,
    }) => {
        await page.goto('/founder');

        const loader = page.getByTestId('site-loader');
        const { props } = await inertiaPage(page);
        const name = (props.profile as { name: string }).name;

        await expect(loader.getByText(`whoami → ${name}`)).toBeVisible();
        await expect(loader).toHaveCount(0, { timeout: 10_000 });
        void issues;
    });

    test('never blocks the page longer than its cap', async ({
        page,
        issues,
    }) => {
        // Hold every image open so the window load event is very late.
        await page.route('**/uploads/**', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 8_000));
            await route.continue().catch(() => undefined);
        });

        const started = Date.now();
        await page.goto('/', { waitUntil: 'commit' });
        await expect(page.getByTestId('site-loader')).toHaveCount(0, {
            timeout: 7_000,
        });

        expect(Date.now() - started).toBeLessThan(7_000);
        issues.allow(/.*/);
    });
});
