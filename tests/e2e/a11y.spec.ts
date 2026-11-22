import AxeBuilder from '@axe-core/playwright';
import { expect, ready, settle, test } from './support/fixtures';

/**
 * Automated accessibility audit (axe-core, WCAG 2.1 A/AA rules) on every
 * public page. Serious and critical violations fail the test.
 */
for (const path of ['/', '/about', '/projects', '/founder']) {
    test(`${path} has no serious accessibility violations`, async ({
        page,
        issues,
    }) => {
        await page.goto(path);
        await ready(page);
        // Reveal every section, then let the entrance animations finish.
        await page.evaluate(() =>
            window.scrollTo(0, document.body.scrollHeight),
        );
        await page.evaluate(() => window.scrollTo(0, 0));
        await settle(page);

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            // Decorative WebGL canvases carry no content.
            .exclude('canvas')
            .analyze();

        const serious = results.violations
            .filter((v) => v.impact === 'serious' || v.impact === 'critical')
            .map(
                (v) =>
                    `${v.id} (${v.impact}): ${v.help} — ${v.nodes
                        .slice(0, 3)
                        .map((n) => n.target.join(' '))
                        .join(' | ')}`,
            );

        expect(serious).toEqual([]);
        void issues;
    });
}
