import type { Page } from '@playwright/test';
import { expect, test } from './support/fixtures';

/**
 * The sign-in page and the admin panel in a real browser.
 */
const ADMIN_PASSWORD = 'e2e-password-123';

async function signIn(page: Page, email: string): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Email address').fill(email);
    await page.getByLabel('Password', { exact: true }).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/admin$/);
}

/** Each worker signs in with its own account, so sessions never collide. */
function adminEmail(projectName: string): string {
    return projectName === 'mobile'
        ? 'e2e-admin-mobile@example.test'
        : 'e2e-admin@example.test';
}

test('the login page shows the brand and the 3D scene', async ({
    page,
    issues,
}) => {
    await page.goto('/login');

    await expect(page.locator('main [data-brand-logo]').first()).toBeVisible();
    await expect(page.getByText('Content Studio')).toBeVisible();

    // The scene renders a WebGL canvas, or its still collage without WebGL.
    await expect(
        page.locator('aside canvas, aside [aria-hidden="true"] img').first(),
    ).toBeAttached({ timeout: 15_000 });

    void issues;
});

test('a wrong password is rejected with an error', async ({ page, issues }) => {
    issues.allow(/http 422/);

    await page.goto('/login');
    await page.getByLabel('Email address').fill('nobody@example.test');
    await page.getByLabel('Password', { exact: true }).fill('wrong-password');
    await page.getByRole('button', { name: 'Log in' }).click();

    await expect(page.getByText(/credentials do not match/i)).toBeVisible();
});

test('an admin can sign in and reach every admin page', async ({
    page,
    issues,
}, info) => {
    await signIn(page, adminEmail(info.project.name));

    await expect(page.locator('h1').first()).toBeVisible();

    for (const path of [
        '/admin/studio',
        '/admin/studio/content',
        '/admin/founder',
        '/admin/projects',
        '/admin/projects/create',
        '/admin/technologies',
        '/admin/clients',
        '/admin/skills',
        '/admin/experiences',
        '/admin/educations',
        '/admin/messages',
    ]) {
        await page.goto(path);
        await expect(page.locator('h1').first()).toBeVisible();
    }

    void issues;
});
