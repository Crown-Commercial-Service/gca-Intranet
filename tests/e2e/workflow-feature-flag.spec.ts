/**
 * WF-0.x — Publishing Workflow feature flag gating
 *
 * Runs as admin (storageState: admin.json).
 * Flag is restored after each test.
 */
import { test, expect } from '@playwright/test';

const FLAGS_PAGE    = '/wp-admin/options-general.php?page=gca-feature-flags';
const WORKFLOW_PAGE = '/wp-admin/options-general.php?page=gca-publishing-workflow';
const FLAG_ID       = 'publishing-workflow';

async function setFlag(page: import('@playwright/test').Page, enable: boolean) {
    await page.goto(FLAGS_PAGE);
    const checkbox = page.locator(`input[name="gca_flags[${FLAG_ID}]"]`);
    const isChecked = await checkbox.isChecked();
    if (enable && !isChecked) {
        await checkbox.check({ force: true });
    } else if (!enable && isChecked) {
        await checkbox.uncheck({ force: true });
    }
    await page.locator('input[name="submit"]').click();
    await expect(page.locator('.updated, #setting-error-settings_updated')).toBeVisible();
}

test.describe('Publishing Workflow feature flag (WF-0.x)', () => {

    test.afterEach(async ({ page }) => {
        await setFlag(page, false);
    });

    test('WF-0.1 — feature flag appears on flags page', async ({ page }) => {
        await page.goto(FLAGS_PAGE);
        await expect(page.locator(`input[name="gca_flags[${FLAG_ID}]"]`)).toBeVisible();
    });

    test('WF-0.2 — enabling flag makes Settings → Publishing Workflow accessible', async ({ page }) => {
        await setFlag(page, true);

        const response = await page.goto(WORKFLOW_PAGE);
        await expect(page.locator('h1', { hasText: 'Publishing Workflow' })).toBeVisible();
        expect(response?.status()).toBe(200);
    });

    test('WF-0.3 — Settings → Publishing Workflow is always in menu (settings page always active)', async ({ page }) => {
        // The settings page registers regardless of flag state so admins can
        // configure the reviewer email before enabling the flag.
        await setFlag(page, false);

        await page.goto('/wp-admin/options-general.php');
        const menuItem = page.locator('#adminmenu a', { hasText: 'Publishing Workflow' });
        await expect(menuItem).toBeVisible();
    });

});
