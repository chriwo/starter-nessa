import { expect, test } from '@playwright/test';

/**
 * Desktop main navigation (>= lg breakpoint).
 * Covers header position, dropdown ARIA state, hover/keyboard opening and Escape.
 */
test.describe('Desktop navigation', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/');
    });

    test('header carries a position state class', async ({ page }) => {
        const header = page.locator('#site-header');
        await expect(header).toHaveClass(/is-header-(sticky|fixed)/);
    });

    test('desktop navigation is visible, hamburger is hidden', async ({ page }) => {
        await expect(page.locator('.c-mainnav')).toBeVisible();
        await expect(page.locator('.o-site-header__toggler')).toBeHidden();
    });

    test('dropdown opens on hover and reflects aria-expanded', async ({ page }) => {
        const parent = page.locator('.c-mainnav__item--has-children').first();
        const toggle = parent.locator('> .c-mainnav__toggle');
        const dropdown = parent.locator('> .c-mainnav__dropdown--l2');

        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(dropdown).toBeHidden();

        await parent.hover();

        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(dropdown).toBeVisible();
    });

    test('dropdown opens on keyboard focus and closes on Escape', async ({ page }) => {
        const parent = page.locator('.c-mainnav__item--has-children').first();
        const toggle = parent.locator('> .c-mainnav__toggle');
        const dropdown = parent.locator('> .c-mainnav__dropdown--l2');

        await toggle.focus();
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(dropdown).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(dropdown).toBeHidden();
    });

    test('dropdown parent exposes aria-haspopup', async ({ page }) => {
        const parent = page.locator('.c-mainnav__item--has-children').first();
        await expect(parent).toHaveAttribute('aria-haspopup', 'true');
    });
});
