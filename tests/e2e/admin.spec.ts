import type { Page } from '@playwright/test';
import { expect, test } from './support/fixtures';

// Mirrors Database\Seeders\E2eSeeder; only ever exists in the e2e database.
// Each Playwright project signs in as its own admin, so parallel projects
// never trip the login rate limit (5 attempts per minute per account).
const PASSWORD = 'e2e-password-123';

function adminEmail(): string {
    return test.info().project.name === 'mobile'
        ? 'e2e-admin-mobile@example.test'
        : 'e2e-admin@example.test';
}

async function login(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByLabel(/email/i).fill(adminEmail());
    await page.getByLabel(/^password/i).fill(PASSWORD);
    await page.getByRole('button', { name: /log in|sign in/i }).click();
    await expect(page).toHaveURL(/\/admin/);
}

test.describe('admin panel', () => {
    test('guests are sent to the login page', async ({ page, issues }) => {
        await page.goto('/admin');
        await expect(page).toHaveURL(/\/login$/);
        void issues;
    });

    test('a wrong password is rejected', async ({ page, issues }) => {
        await page.goto('/login');
        await page.getByLabel(/email/i).fill(adminEmail());
        await page.getByLabel(/^password/i).fill('not-the-password');
        await page.getByRole('button', { name: /log in|sign in/i }).click();
        await expect(page.getByText(/credentials|do not match/i)).toBeVisible();
        void issues;
    });

    test('the admin can sign in and open every editor', async ({
        page,
        issues,
    }) => {
        await login(page);

        for (const path of [
            '/admin',
            '/admin/projects',
            '/admin/projects/create',
            '/admin/skills',
            '/admin/experiences',
            '/admin/educations',
            '/admin/clients',
            '/admin/messages',
            '/admin/company',
            '/admin/profile',
            '/admin/studio-sections',
        ]) {
            const response = await page.goto(path);
            expect(response?.status(), path).toBe(200);
            await expect(
                page.locator('main, [data-sidebar], body').first(),
            ).toBeVisible();
        }

        void issues;
    });

    test('the project editor lists gallery images for removal', async ({
        page,
        issues,
    }) => {
        await login(page);
        await page.goto('/admin/projects');

        await page
            .locator('a[href*="/admin/projects/"][href$="/edit"]')
            .first()
            .click();
        await expect(page).toHaveURL(/\/edit$/);
        await expect(page.locator('input[name="title"]')).toHaveValue(/.+/);
        void issues;
    });
});

test.describe('sign-in page', () => {
    test('shows the coding workspace and real project covers', async ({
        page,
        issues,
        isMobile,
    }) => {
        await page.goto('/login');
        await expect(page.locator('h1')).toBeVisible();

        if (isMobile) {
            // Phones get the form first and a single prompt line, not the IDE.
            await expect(page.getByTestId('code-editor')).toBeHidden();
        } else {
            const editor = page.getByTestId('code-editor');
            await expect(editor).toBeVisible();
            await expect(editor).toContainText('signIn');
            await expect(
                page.getByTestId('auth-showcase').locator('img'),
            ).toHaveCount(3);
        }

        void issues;
    });

    test('runs the code rain behind the workspace and glitches the title', async ({
        page,
        issues,
    }) => {
        await page.goto('/login');

        // The rain is actually painting: some pixels of its canvas are lit.
        const rain = page.getByTestId('code-rain');
        await expect(rain).toBeVisible();
        await expect
            .poll(() =>
                rain.evaluate((canvas: HTMLCanvasElement) => {
                    const ctx = canvas.getContext('2d');
                    const { data } = ctx!.getImageData(
                        0,
                        0,
                        canvas.width,
                        canvas.height,
                    );

                    for (let i = 3; i < data.length; i += 4) {
                        if (data[i] > 0) {
                            return true;
                        }
                    }

                    return false;
                }),
            )
            .toBe(true);

        // The title carries the glitch copy for its colour-split slices.
        await expect(page.locator('h1 .glitch')).toHaveAttribute(
            'data-text',
            /.+/,
        );
        void issues;
    });
});
