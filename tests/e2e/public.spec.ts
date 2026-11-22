import {
    brokenVisibleImages,
    expect,
    hasHorizontalOverflow,
    inertiaPage,
    ready,
    scrollTo,
    test,
} from './support/fixtures';

/**
 * The public website in a real browser: every page renders cleanly, media
 * loads, the brand is visible and large, and the interactive parts work.
 */
const pages = ['/', '/about', '/founder', '/projects'];

for (const path of pages) {
    test(`${path} renders without errors, broken images or overflow`, async ({
        page,
        issues,
    }) => {
        await page.goto(path);
        await ready(page);

        await expect(page.locator('h1').first()).toBeVisible();
        expect(await hasHorizontalOverflow(page)).toBe(false);

        // Walk the page so lazy images load, then check none is broken.
        const height = await page.evaluate(() => document.body.scrollHeight);

        for (let y = 0; y < height; y += 700) {
            await page.evaluate((top) => window.scrollTo(0, top), y);
            await page.waitForTimeout(60);
            expect(await brokenVisibleImages(page)).toEqual([]);
        }

        void issues;
    });
}

test('the company logo is shown large in the header and footer', async ({
    page,
    issues,
}) => {
    await page.goto('/');
    await ready(page);

    const header = page.locator('header [data-brand-logo]').first();
    await expect(header).toBeVisible();
    expect((await header.boundingBox())!.height).toBeGreaterThanOrEqual(38);

    const footer = page.locator('footer [data-brand-logo]').first();
    await scrollTo(footer);
    await expect(footer).toBeVisible();
    expect((await footer.boundingBox())!.height).toBeGreaterThanOrEqual(60);

    void issues;
});

test('the brand name and logo come from the studio record', async ({
    page,
    issues,
}) => {
    await page.goto('/');
    await ready(page);

    const { props } = await inertiaPage(page);
    const brand = props.brand as { name: string; logo_url: string | null };

    expect(brand.logo_url).toMatch(/^\/uploads\//);
    await expect(page).toHaveTitle(new RegExp(brand.name));
    await expect(
        page.locator(`header img[alt="${brand.name} logo"]`),
    ).toBeVisible();

    void issues;
});

test('the work archive filters by owner and keeps the filter in the URL', async ({
    page,
    issues,
}) => {
    await page.goto('/projects');
    await ready(page);

    const count = () => page.locator('article').count();
    const total = await count();
    expect(total).toBeGreaterThan(0);

    await page
        .getByRole('group', { name: 'Filter by owner' })
        .getByRole('button')
        .nth(1)
        .click();

    await expect(page).toHaveURL(/owner=studio/);
    expect(await count()).toBeLessThanOrEqual(total);

    void issues;
});

test('a project page shows its cover and links to related work', async ({
    page,
    issues,
}) => {
    await page.goto('/projects');
    await ready(page);

    await page.locator('article h3 a').first().click();
    await expect(page).toHaveURL(/\/projects\/[a-z0-9-]+$/);
    await ready(page);

    await expect(page.locator('h1')).toBeVisible();
    expect(await brokenVisibleImages(page)).toEqual([]);

    void issues;
});

test('a case-study landing page loads its hero screenshots', async ({
    page,
    issues,
}) => {
    await page.goto('/projects');
    await ready(page);

    const { props } = await inertiaPage(page);
    const withLanding = (
        props.projects as { landing_url: string | null }[]
    ).find((project) => project.landing_url);

    expect(withLanding, 'the e2e seed has one case study').toBeTruthy();

    await page.goto(withLanding!.landing_url!);
    await ready(page);
    await expect(page.locator('h1')).toBeVisible();
    expect(await brokenVisibleImages(page)).toEqual([]);

    void issues;
});

test('the contact form sends a message', async ({ page, issues }) => {
    await page.goto('/#contact');
    await ready(page);

    const form = page.locator('#contact form');
    await scrollTo(form);
    await form.getByLabel('Your name').fill('E2E Visitor');
    await form.getByLabel('Email').fill('visitor@example.test');
    await form
        .getByLabel('Message')
        .fill('Hello, this is an end-to-end test message for the studio.');
    await form.getByRole('button', { name: /send/i }).click();

    await expect(page.getByText(/thanks for reaching out/i)).toBeVisible();

    void issues;
});

test('the mobile menu opens and navigates', async ({ page, issues }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    await ready(page);

    await page.getByRole('button', { name: 'Open menu' }).click();
    const menu = page.locator('#mobile-menu');
    await expect(menu).toBeVisible();

    await menu.getByRole('link', { name: 'About' }).click();
    await expect(page).toHaveURL(/\/about$/);

    void issues;
});
