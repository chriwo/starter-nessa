import { expect, test } from '@playwright/test';

/**
 * Mobile navigation (< lg breakpoint) — Bootstrap 5 offcanvas.
 * Covers toggler visibility, opening/closing (Escape + backdrop) and collapse sub-menus.
 */
test.describe('Mobile navigation (offcanvas)', () => {
    test.use({ viewport: { width: 390, height: 844 } });

    test.beforeEach(async ({ page }) => {
        await page.goto('/');
    });

    test('hamburger is visible, desktop navigation is hidden', async ({ page }) => {
        await expect(page.locator('.o-site-header__toggler')).toBeVisible();
        await expect(page.locator('.c-mainnav')).toBeHidden();
    });

    test('toggler opens the offcanvas', async ({ page }) => {
        const offcanvas = page.locator('#offcanvasNav');
        await expect(offcanvas).toBeHidden();

        await page.locator('.o-site-header__toggler').click();

        await expect(offcanvas).toBeVisible();
        await expect(page.locator('.o-site-header__toggler')).toHaveAttribute(
            'aria-expanded',
            'true',
        );
    });

    test('offcanvas closes on Escape', async ({ page }) => {
        const offcanvas = page.locator('#offcanvasNav');
        await page.locator('.o-site-header__toggler').click();

        // Wait for the open animation to finish (the focus trap that handles
        // Escape is only active once the offcanvas is fully shown).
        await expect(offcanvas).toBeVisible();
        await expect(offcanvas).not.toHaveClass(/showing/);

        await page.keyboard.press('Escape');
        await expect(offcanvas).toBeHidden();
    });

    test('offcanvas closes via the close button', async ({ page }) => {
        await page.locator('.o-site-header__toggler').click();
        const offcanvas = page.locator('#offcanvasNav');
        await expect(offcanvas).toBeVisible();

        await offcanvas.locator('.btn-close').click();
        await expect(offcanvas).toBeHidden();
    });

    test('sub-menu collapse toggles aria-expanded and reveals children', async ({ page }) => {
        await page.locator('.o-site-header__toggler').click();
        await expect(page.locator('#offcanvasNav')).toBeVisible();

        const toggle = page.locator('#offcanvasNav .c-offcanvas-nav__toggle').first();
        const panelId = await toggle.getAttribute('aria-controls');
        const panel = page.locator(`#${panelId}`);

        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(panel).toBeHidden();

        await toggle.click();

        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(panel).toBeVisible();
    });
});
