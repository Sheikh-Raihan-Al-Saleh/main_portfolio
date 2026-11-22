import type { Page } from '@playwright/test';
import {
    brokenVisibleImages,
    expect,
    hasHorizontalOverflow,
    inertiaPage,
    ready,
    test,
    scrollTo,
} from './support/fixtures';

const PAGES = [
    { path: '/', component: 'public/Home' },
    { path: '/about', component: 'public/About' },
    { path: '/projects', component: 'public/Projects' },
    { path: '/founder', component: 'public/Founder' },
];

test.describe('public pages', () => {
    for (const { path, component } of PAGES) {
        test(`${path} renders cleanly`, async ({ page, issues }) => {
            const response = await page.goto(path);
            expect(response?.status()).toBe(200);
            await ready(page);

            expect((await inertiaPage(page)).component).toBe(component);
            await expect(page.locator('h1').first()).toBeVisible();
            await expect(page.getByRole('banner')).toBeVisible();
            await expect(page.getByRole('contentinfo')).toBeVisible();

            expect(
                await hasHorizontalOverflow(page),
                'page scrolls sideways',
            ).toBe(false);
            expect(await brokenVisibleImages(page)).toEqual([]);
            void issues;
        });
    }

    test('a project detail page opens from the archive', async ({
        page,
        issues,
    }) => {
        await page.goto('/projects');
        await ready(page);

        const link = page.locator('main a[href^="/projects/"]').first();
        const href = await link.getAttribute('href');
        expect(href).toBeTruthy();

        await link.click();
        await expect(page).toHaveURL(new RegExp(`${href}$`));
        await expect(page.locator('h1').first()).toBeVisible();
        expect(await brokenVisibleImages(page)).toEqual([]);
        void issues;
    });

    test('the project landing page shows all three hero device images', async ({
        page,
        issues,
    }) => {
        await page.goto('/projects');
        const props = (await inertiaPage(page)).props as {
            projects: { landing_url: string | null }[];
        };
        const landing = props.projects.find(
            (project) => project.landing_url,
        )?.landing_url;
        expect(landing, 'seeded landing page').toBeTruthy();

        await page.goto(landing!);
        await ready(page);

        const { props: landingProps } = await inertiaPage(page);
        const urls = (landingProps.landingPage as { hero_media_urls: string[] })
            .hero_media_urls;
        expect(urls).toHaveLength(3);

        for (const url of urls) {
            const res = await page.request.get(url);
            expect(res.status(), url).toBe(200);
        }

        expect(await hasHorizontalOverflow(page)).toBe(false);
        void issues;
    });

    test('unknown pages return 404', async ({ page, issues }) => {
        issues.allow(/http 404/);
        issues.allow(/Failed to load resource.*404/);
        const response = await page.goto('/this-page-does-not-exist');
        expect(response?.status()).toBe(404);
    });
});

test.describe('branding and dynamic images', () => {
    test('the uploaded logo is large at the top and compacts on scroll', async ({
        page,
        issues,
        isMobile,
    }) => {
        await page.goto('/');
        await ready(page);

        const logo = page.locator('header img').first();
        await expect(logo).toBeVisible();
        await expect
            .poll(() =>
                logo.evaluate((img: HTMLImageElement) => img.naturalWidth),
            )
            .toBeGreaterThan(0);

        const top = (await logo.boundingBox())!.height;
        expect(top).toBeGreaterThanOrEqual(isMobile ? 54 : 70);

        await page.mouse.wheel(0, 800);
        await expect
            .poll(async () => (await logo.boundingBox())!.height)
            .toBeLessThan(top);
        void issues;
    });

    test('the footer shows the logo, not the initials fallback', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await ready(page);
        await page.locator('footer').scrollIntoViewIfNeeded();

        await expect(page.locator('footer img').first()).toBeVisible();
        await expect(page.locator('footer .footer-logo-icon')).toHaveCount(0);
        void issues;
    });

    test('the work index previews a cover beside the cursor', async ({
        page,
        issues,
        isMobile,
    }) => {
        test.skip(isMobile, 'the floating preview is desktop only');
        await page.goto('/');
        await ready(page);

        const row = page.getByTestId('work-row').first();
        await scrollTo(row);
        const box = (await row.boundingBox())!;
        await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);

        const preview = page.getByTestId('work-preview');
        await expect(preview.locator('img')).toHaveAttribute(
            'src',
            /uploads\/e2e\/cover-/,
        );
        void issues;
    });

    test('the custom cursor follows the mouse on desktop only', async ({
        page,
        issues,
        isMobile,
    }) => {
        await page.goto('/');
        await ready(page);

        if (isMobile) {
            await expect(page.getByTestId('custom-cursor')).toHaveCount(0);
        } else {
            await page.mouse.move(400, 400);
            await expect(page.getByTestId('custom-cursor')).toHaveCount(1);
        }

        void issues;
    });

    test('the work marquee links to real case studies', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await ready(page);

        const marquee = page.getByTestId('project-marquee');
        await scrollTo(marquee);
        await expect(marquee).toBeVisible();

        const focusable = marquee.locator('a:not([tabindex="-1"])');
        expect(await focusable.count()).toBeGreaterThanOrEqual(2);

        const href = await focusable.first().getAttribute('href');
        const res = await page.request.get(href!);
        expect(res.status()).toBe(200);
        expect(await brokenVisibleImages(page)).toEqual([]);
        void issues;
    });

    test('the founder page shows the avatar and his project strip', async ({
        page,
        issues,
    }) => {
        await page.goto('/founder');
        await ready(page);

        // The real <img> stays in the page (for alt text and no-WebGL visitors).
        const avatar = page.locator('main img[src*="e2e/avatar"]').first();
        await expect(avatar).toBeAttached();
        await expect(avatar).toHaveAttribute('alt', /.+/);
        await expect(page.getByTestId('project-marquee')).toHaveCount(1);
        void issues;
    });
});

/** A liquid surface counts as live once its canvas has faded in over the <img>. */
function liveSurfaces(page: Page) {
    return page.locator('canvas[data-liquid-surface].opacity-100');
}

test.describe('three.js liquid layer', () => {
    test('the studio hero runs its liquid shape', async ({ page, issues }) => {
        await page.goto('/');
        await ready(page);

        await expect(
            page.locator('[data-liquid-blob] canvas.opacity-100'),
        ).toHaveCount(1, { timeout: 15_000 });
        void issues;
    });

    test('project covers turn into liquid images', async ({ page, issues }) => {
        await page.goto('/');
        await ready(page);

        const featured = page.locator('#work [data-liquid-image]').first();
        await scrollTo(featured);

        // Once live, the WebGL copy is shown and the <img> fades under it.
        await expect(featured.locator('canvas.opacity-100')).toHaveCount(1, {
            timeout: 15_000,
        });
        await expect(featured.locator('img')).toHaveClass(/opacity-0/);
        void issues;
    });

    test('the founder portrait is a liquid image', async ({ page, issues }) => {
        await page.goto('/founder');
        await ready(page);

        await expect(liveSurfaces(page).first()).toBeAttached({
            timeout: 15_000,
        });
        void issues;
    });

    test('the whole site shares a single WebGL context', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await ready(page);
        await expect(liveSurfaces(page).first()).toBeAttached({
            timeout: 15_000,
        });

        // Every visible surface is a 2D canvas; the one GL canvas is offscreen.
        const contexts = await page.evaluate(
            () =>
                [...document.querySelectorAll('canvas')].filter(
                    (canvas) => !canvas.hasAttribute('data-liquid-surface'),
                ).length,
        );
        expect(contexts).toBe(0);
        void issues;
    });

    test('the landing hero runs its liquid shape', async ({ page, issues }) => {
        await page.goto('/projects');
        const props = (await inertiaPage(page)).props as {
            projects: { landing_url: string | null }[];
        };
        await page.goto(
            props.projects.find((p) => p.landing_url)!.landing_url!,
        );
        await ready(page);
        await expect(
            page.locator('[data-liquid-blob] canvas.opacity-100'),
        ).toHaveCount(1, { timeout: 15_000 });
        void issues;
    });
});

test.describe('navigation and interaction', () => {
    test('the scroll progress bar follows the page', async ({
        page,
        issues,
    }) => {
        await page.goto('/');
        await ready(page);

        const bar = page.getByTestId('scroll-progress');
        const scale = () =>
            bar.evaluate(
                (el) => new DOMMatrix(getComputedStyle(el).transform).a,
            );

        await page.evaluate(() =>
            window.scrollTo(0, document.body.scrollHeight),
        );
        await expect.poll(scale).toBeGreaterThan(0.9);
        void issues;
    });

    test('the mobile menu opens and navigates', async ({
        page,
        issues,
        isMobile,
    }) => {
        test.skip(!isMobile, 'mobile only');
        await page.goto('/');
        await ready(page);

        await page.getByRole('button', { name: 'Open menu' }).click();
        const menu = page.getByRole('navigation', { name: 'Mobile' });
        await expect(menu).toBeVisible();
        await expect(menu.locator('a').first()).toBeVisible();
        void issues;
    });

    test('the theme toggle switches to dark mode', async ({
        page,
        issues,
        isMobile,
    }) => {
        await page.goto('/');
        await ready(page);

        // On phones the toggle lives in the menu sheet.
        if (isMobile) {
            await page.getByRole('button', { name: 'Open menu' }).click();
        }

        await page
            .getByRole('radiogroup', { name: 'Colour theme' })
            .locator('visible=true')
            .first()
            .getByRole('radio', { name: 'Dark' })
            .click();
        await expect(page.locator('html')).toHaveClass(/dark/);
        void issues;
    });

    test('the contact form shows validation errors instead of sending', async ({
        page,
        issues,
    }) => {
        issues.allow(/http 422/);
        await page.goto('/');
        await ready(page);

        const form = page.locator('#contact form');
        await scrollTo(form);
        await form.locator('input[name="name"]').fill('QA');
        await form.locator('input[name="email"]').fill('qa@example.test');
        await form.locator('textarea[name="message"]').fill('too short');
        await form.locator('button[type="submit"]').click();

        await expect(
            form.getByText(/at least 20|must be at least/i),
        ).toBeVisible();
    });

    test('a valid enquiry is accepted', async ({ page, issues }) => {
        await page.goto('/');
        await ready(page);

        const form = page.locator('#contact form');
        await scrollTo(form);
        await form.locator('input[name="name"]').fill('Playwright QA');
        await form.locator('input[name="email"]').fill('qa@example.test');
        await form
            .locator('textarea[name="message"]')
            .fill('This is an end-to-end test enquiry from the QA suite.');
        await form.locator('button[type="submit"]').click();

        await expect(
            page.getByText(/thanks|message sent/i).first(),
        ).toBeVisible();
        void issues;
    });

    test('the footer hides the résumé link when no résumé is uploaded', async ({
        page,
        request,
        issues,
    }) => {
        const resume = await request.get('/resume');
        await page.goto('/founder');
        await ready(page);

        const links = page.locator('footer a[href="/resume"]');

        if (resume.status() === 404) {
            await expect(links).toHaveCount(0);
        }

        void issues;
    });
});
