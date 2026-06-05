/**
 * AC-8.x — Feature flag gating
 *
 * These tests toggle the cron-manager feature flag and verify the UI responds.
 * The flag is restored after each test to avoid breaking subsequent specs.
 */
import { test, expect } from '@playwright/test';
import { CRON_PAGE } from '../playwright.config';

const FLAGS_PAGE = '/wp-admin/options-general.php?page=gca-feature-flags';

async function setFlag(page: import('@playwright/test').Page, flagId: string, enable: boolean) {
    await page.goto(FLAGS_PAGE);
    const checkbox = page.locator(`input[name="gca_flags[${flagId}]"]`);
    const isChecked = await checkbox.isChecked();
    // The toggle uses a <span> overlay that intercepts pointer events — use force: true
    // to dispatch the click directly to the hidden checkbox input.
    if (enable && !isChecked) {
        await checkbox.check({ force: true });
    } else if (!enable && isChecked) {
        await checkbox.uncheck({ force: true });
    }
    await page.locator('input[name="submit"]').click();
    await expect(page.locator('.updated, #setting-error-settings_updated')).toBeVisible();
}

test.describe('Feature flag: cron-manager (AC-8.x)', () => {

    test.afterEach(async ({ page }) => {
        // Always restore the cron-manager flag to enabled after each test.
        await setFlag(page, 'cron-manager', true);
    });

    test('AC-8.1 — disabling cron-manager hides the Cron Jobs menu item', async ({ page }) => {
        await setFlag(page, 'cron-manager', false);

        await page.goto('/wp-admin/tools.php');
        await expect(
            page.locator('#menu-tools .wp-submenu a', { hasText: 'Cron Jobs' })
        ).toHaveCount(0);
    });

    test('AC-8.2 — re-enabling cron-manager restores the Cron Jobs menu item', async ({ page }) => {
        // Disable first
        await setFlag(page, 'cron-manager', false);

        // Re-enable
        await setFlag(page, 'cron-manager', true);

        await page.goto('/wp-admin/tools.php');
        await expect(
            page.locator('#menu-tools .wp-submenu a', { hasText: 'Cron Jobs' })
        ).toBeVisible();
    });

    test('AC-8.1 — accessing cron page directly while disabled shows no-access or 404', async ({ page }) => {
        await setFlag(page, 'cron-manager', false);

        const response = await page.goto(CRON_PAGE);
        // WordPress redirects non-existent menu pages or shows an error
        const bodyText = await page.locator('body').textContent();
        const isBlocked =
            response?.status() === 403 ||
            (bodyText?.includes('not have sufficient permissions') ?? false) ||
            !(bodyText?.includes('Cron Jobs') ?? true);
        expect(isBlocked).toBe(true);
    });

});

test.describe('Feature flag: workday-user-sync (AC-8.3)', () => {

    test.afterEach(async ({ page }) => {
        await setFlag(page, 'workday-user-sync', true);
    });

    test('AC-8.3 — disabling workday-user-sync is reflected in the feature flags UI', async ({ page }) => {
        await setFlag(page, 'workday-user-sync', false);

        await page.goto(FLAGS_PAGE);
        const checkbox = page.locator('input[name="gca_flags[workday-user-sync]"]');
        await expect(checkbox).not.toBeChecked();
    });

});
