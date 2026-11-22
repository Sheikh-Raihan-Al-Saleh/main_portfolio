import type { BrowserContext, Page } from '@playwright/test';
import { expect, test } from './support/fixtures';

/**
 * Admin CRUD flows in a real browser, one per module. The tests share one
 * signed-in page (signing in for every test would hit the login rate limit)
 * and run in order. Every page must load without console errors or failed
 * requests.
 */
test.describe.configure({ mode: 'serial' });
test.skip(({ isMobile }) => isMobile, 'Admin CRUD runs on desktop.');

let context: BrowserContext;
let page: Page;
const problems: string[] = [];

test.beforeAll(async ({ browser }) => {
    context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
    });
    page = await context.newPage();

    page.on('pageerror', (error) =>
        problems.push(`pageerror: ${error.message}`),
    );
    page.on('console', (message) => {
        if (message.type() === 'error') {
            problems.push(`console: ${message.text()}`);
        }
    });

    await page.goto('/login');
    await page.getByLabel('Email address').fill('e2e-admin@example.test');
    await page.getByLabel('Password', { exact: true }).fill('e2e-password-123');
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/admin$/);
});

test.afterEach(() => {
    expect(problems, 'console or page errors').toEqual([]);
});

test.afterAll(async () => {
    await context.close();
});

const stamp = () => Date.now().toString(36);

test('projects: create and edit', async () => {
    const title = `E2E Project ${stamp()}`;

    await page.goto('/admin/projects/create');
    await page.getByLabel('Title').fill(title);
    await page.getByRole('button', { name: 'Create project' }).click();
    await expect(page).toHaveURL(/\/admin\/projects$/);
    await expect(page.getByText(title)).toBeVisible();

    // The success toast sits over the row's buttons, so follow the link directly.
    const editUrl = await page
        .getByRole('link', { name: `Edit ${title}` })
        .getAttribute('href');
    await page.goto(editUrl!);
    await expect(page.getByLabel('Title')).toHaveValue(title);
    await page.getByLabel('Title').fill(`${title} edited`);
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByText(`${title} edited`)).toBeVisible();
});

test('education: create an entry', async () => {
    const institution = `E2E University ${stamp()}`;

    await page.goto('/admin/educations/create');
    await page.getByLabel('Institution').fill(institution);
    await page.getByLabel('Degree').fill('BSc');
    await page.getByRole('button', { name: 'Create entry' }).click();

    await expect(page).toHaveURL(/\/admin\/educations$/);
    await expect(page.getByText(institution)).toBeVisible();
});

test('experience: create a role', async () => {
    const company = `E2E Company ${stamp()}`;

    await page.goto('/admin/experiences/create');
    await page.getByLabel('Role').fill('Engineer');
    await page.getByLabel('Company', { exact: true }).fill(company);
    await page.getByLabel('Start date').fill('2022-01-01');
    await page.getByRole('button', { name: 'Create role' }).click();

    await expect(page).toHaveURL(/\/admin\/experiences$/);
    await expect(page.getByText(company)).toBeVisible();
});

test('clients: add a client in the dialog', async () => {
    const name = `E2E Client ${stamp()}`;

    await page.goto('/admin/clients');
    await page.getByRole('button', { name: 'New client' }).first().click();
    await page.locator('#new-client-name').fill(name);
    await page
        .getByRole('dialog')
        .getByRole('button', { name: /add|create|save/i })
        .last()
        .click();

    await expect(page.getByText(name).first()).toBeVisible();
});

test('technologies: add one in the dialog', async () => {
    const name = `E2E Tech ${stamp()}`;

    await page.goto('/admin/technologies');
    await page.getByRole('button', { name: 'New technology' }).click();
    await page.getByLabel('Name').fill(name);
    await page.getByRole('button', { name: 'Add technology' }).click();

    await expect(page.getByRole('cell', { name, exact: true })).toBeVisible();
});

test('skills: add a category', async () => {
    const category = `E2E Cat ${stamp()}`;

    await page.goto('/admin/skills');
    await page.getByLabel('New category name').fill(category);
    await page.getByRole('button', { name: 'Add category' }).click();

    await expect(page.getByLabel(`Rename ${category}`)).toBeVisible();
});

test('messages: open a message and mark it unread', async () => {
    await page.goto('/admin/messages');
    await page.locator('a[href*="/admin/messages/"]').first().click();
    await expect(page).toHaveURL(/\/admin\/messages\/\d+$/);

    await page.getByRole('button', { name: /unread/i }).click();
    await expect(page).toHaveURL(/\/admin\/messages$/);
});

test('studio: the brand name updates across the site', async () => {
    const name = `E2E Studio ${stamp()}`;

    await page.goto('/admin/studio');
    await page.getByLabel('Name', { exact: true }).fill(name);
    await page.getByRole('button', { name: 'Save studio' }).click();
    await expect(page.getByText('Studio updated.')).toBeVisible();

    await page.goto('/');
    await expect(page).toHaveTitle(new RegExp(name));
});
