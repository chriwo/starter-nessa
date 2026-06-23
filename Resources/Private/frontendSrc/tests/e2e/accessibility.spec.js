import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

/**
 * Automated WCAG 2.1 AA checks (axe-core) for the navigation in its key states.
 */
test.describe('Navigation accessibility (axe WCAG 2.1 AA)', () => {
    const wcagTags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];

    test('header and desktop navigation have no violations', async ({ page }) => {
        await page.goto('/');

        const results = await new AxeBuilder({ page })
            .withTags(wcagTags)
            .include('#site-header')
            .analyze();

        expect(results.violations).toEqual([]);
    });

    test('opened offcanvas has no violations', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/');

        await page.locator('.o-site-header__toggler').click();
        await expect(page.locator('#offcanvasNav')).toBeVisible();

        const results = await new AxeBuilder({ page })
            .withTags(wcagTags)
            .include('#offcanvasNav')
            .analyze();

        expect(results.violations).toEqual([]);
    });
});
