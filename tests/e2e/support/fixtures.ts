import { test as base, expect } from '@playwright/test';
import type { Locator, Page } from '@playwright/test';

/**
 * Shared fixtures:
 *
 * - `issues` collects every console error, uncaught exception and failed
 *   same-origin request, and the test fails at the end if any were seen, so a
 *   page that "looks fine" but throws is still caught.
 * - The boot screen is marked as seen, so tests start on the page itself;
 *   loader.spec.ts opts back in to test the loader.
 */
type Issues = { list: string[]; allow: (pattern: RegExp) => void };

export const test = base.extend<{ issues: Issues; skipLoader: boolean }>({
    skipLoader: [true, { option: true }],

    page: async ({ page, skipLoader }, use) => {
        if (skipLoader) {
            await page.addInitScript(() => {
                try {
                    sessionStorage.setItem('site-loader-seen', '1');
                } catch {
                    // ignore
                }
            });
        }

        await use(page);
    },

    issues: async ({ page, baseURL }, use) => {
        const list: string[] = [];
        const allowed: RegExp[] = [];

        page.on('console', (message) => {
            if (message.type() === 'error') {
                list.push(`console: ${message.text()}`);
            }
        });
        page.on('pageerror', (error) =>
            list.push(`pageerror: ${error.message}`),
        );
        page.on('response', (response) => {
            if (
                response.status() >= 400 &&
                response.url().startsWith(baseURL ?? '')
            ) {
                list.push(`http ${response.status()}: ${response.url()}`);
            }
        });

        await use({ list, allow: (pattern) => allowed.push(pattern) });

        const unexpected = list.filter(
            (issue) => !allowed.some((p) => p.test(issue)),
        );
        expect(
            unexpected,
            'console errors, page errors or failed requests',
        ).toEqual([]);
    },
});

export { expect };

/** The Inertia page object the server sent, parsed from the document. */
export async function inertiaPage(
    page: Page,
): Promise<{ component: string; props: Record<string, unknown> }> {
    return page.evaluate(() => {
        const script = document.querySelector('script[data-page]');
        const raw =
            script?.textContent ??
            document.getElementById('app')?.dataset.page ??
            '{}';

        return JSON.parse(raw);
    });
}

/** Wait until the app has hydrated and the loader (if any) has left. */
export async function ready(page: Page): Promise<void> {
    await page.locator('#main').waitFor();
    await expect(page.getByTestId('site-loader')).toHaveCount(0, {
        timeout: 8_000,
    });
}

/** True when the page scrolls sideways — a classic mobile layout bug. */
export async function hasHorizontalOverflow(page: Page): Promise<boolean> {
    return page.evaluate(
        () => document.documentElement.scrollWidth > window.innerWidth + 1,
    );
}

/** Every image currently in the viewport has finished loading with real pixels. */
export async function brokenVisibleImages(page: Page): Promise<string[]> {
    return page.evaluate(async () => {
        const inView = [...document.images].filter((img) => {
            const r = img.getBoundingClientRect();
            const style = getComputedStyle(img);

            return (
                r.width > 0 &&
                r.height > 0 &&
                r.bottom > 0 &&
                r.top < window.innerHeight &&
                style.visibility !== 'hidden' &&
                style.display !== 'none'
            );
        });

        // Give pending images a moment, but never hang on a lazy one.
        await Promise.race([
            Promise.all(
                inView.map((img) =>
                    img.complete ? null : img.decode().catch(() => null),
                ),
            ),
            new Promise((resolve) => setTimeout(resolve, 5_000)),
        ]);

        // Broken = finished loading with no pixels (404, bad data).
        return inView
            .filter((img) => img.complete && img.naturalWidth === 0)
            .map((img) => img.currentSrc || img.src);
    });
}

/**
 * Scroll an element into view without Playwright's stability wait: floating
 * cards and marquees animate forever, so they never count as "stable".
 */
export async function scrollTo(locator: Locator): Promise<void> {
    await locator.evaluate((el) =>
        el.scrollIntoView({ block: 'center', behavior: 'instant' }),
    );
}

/**
 * Wait for entrance animations (page fade, scroll reveals) to finish. Colour
 * contrast measured mid-fade is meaningless: text at 5% opacity "fails".
 * Infinite loops (floating cards, marquees) are ignored.
 */
export async function settle(page: Page): Promise<void> {
    await page.evaluate(async () => {
        const finite = document
            .getAnimations()
            .filter(
                (a) => a.effect?.getComputedTiming().iterations !== Infinity,
            );
        await Promise.race([
            Promise.all(finite.map((a) => a.finished.catch(() => null))),
            new Promise((resolve) => setTimeout(resolve, 4_000)),
        ]);
    });
    // motion-v reveals that run on the JS thread rather than WAAPI.
    await page.waitForTimeout(800);
}
