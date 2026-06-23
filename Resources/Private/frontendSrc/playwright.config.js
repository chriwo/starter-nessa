import { defineConfig, devices } from '@playwright/test';

/**
 * End-to-end test configuration for the navigation (PROJ-3).
 *
 * Tests run against a running TYPO3 instance. By default the local DDEV URL is
 * used; override with E2E_BASE_URL (e.g. in CI). Self-signed DDEV certificates
 * are accepted via ignoreHTTPSErrors.
 */
const baseURL = process.env.E2E_BASE_URL || 'https://theme-nessa.ddev.site';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',

    use: {
        baseURL,
        ignoreHTTPSErrors: true,
        trace: 'on-first-retry',
        // Desktop default viewport (>= lg breakpoint). Mobile specs override this.
        viewport: { width: 1280, height: 800 },
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 800 } },
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'], viewport: { width: 1280, height: 800 } },
        },
        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'], viewport: { width: 1280, height: 800 } },
        },
    ],
});
