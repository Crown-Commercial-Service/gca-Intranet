/**
 * AC-1.x — Cron Manager list page
 */
import { test, expect } from '@playwright/test';
import { CRON_PAGE } from '../playwright.config';

test.describe('Cron Manager list view', () => {

    test('AC-1.1 — Cron Jobs appears in Tools menu', async ({ page }) => {
        await page.goto('/wp-admin/tools.php');
        await expect(page.locator('#menu-tools .wp-submenu a', { hasText: 'Cron Jobs' })).toBeVisible();
    });

    test('AC-1.3 — list table shows required columns', async ({ page }) => {
        await page.goto(CRON_PAGE);

        const thead = page.locator('.gca-cron-table thead');
        await expect(thead.locator('th.column-hook')).toContainText('Hook');
        await expect(thead.locator('th.column-next-run')).toContainText('Next Run');
        await expect(thead.locator('th.column-recurrence')).toContainText('Recurrence');
        await expect(thead.locator('th.column-args')).toContainText('Arguments');
        await expect(thead.locator('th.column-last-run')).toContainText('Last Run');
    });

    test('AC-1.4 — clicking Hook column header re-sorts the list', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const hookHeader = page.locator('.gca-cron-table th.column-hook a');
        await hookHeader.click();
        await expect(page).toHaveURL(/orderby=hook/);
    });

    test('AC-1.4 — clicking Next Run column header re-sorts the list', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const nextRunHeader = page.locator('.gca-cron-table th.column-next-run a');
        await nextRunHeader.click();
        await expect(page).toHaveURL(/orderby=next_run/);
    });

    test('AC-1.4 — clicking Recurrence column header re-sorts the list', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const recurrenceHeader = page.locator('.gca-cron-table th.column-recurrence a');
        await recurrenceHeader.click();
        await expect(page).toHaveURL(/orderby=recurrence/);
    });

    test('AC-1.5 — Recurring filter shows only recurring jobs', async ({ page }) => {
        await page.goto(CRON_PAGE);
        await page.locator('.subsubsub a', { hasText: 'Recurring' }).click();
        await expect(page).toHaveURL(/filter_type=recurring/);

        // Every row in the recurrence column must NOT say "One-time"
        const recurrenceCells = page.locator('.gca-cron-table .column-recurrence');
        const count = await recurrenceCells.count();
        for (let i = 0; i < count; i++) {
            await expect(recurrenceCells.nth(i)).not.toHaveText('One-time');
        }
    });

    test('AC-1.5 — One-time filter shows only one-time jobs', async ({ page }) => {
        // Navigate directly rather than clicking the tab (avoids brittle text matching on count spans)
        await page.goto(`${CRON_PAGE}&filter_type=one-time`);
        await expect(page).toHaveURL(/filter_type=one-time/);
        await expect(page.locator('.subsubsub a.current')).toContainText('One-time');

        // Every visible recurrence cell (tbody only, not the header th) must contain "One-time"
        const recurrenceCells = page.locator('.gca-cron-table tbody .column-recurrence');
        const count = await recurrenceCells.count();
        for (let i = 0; i < count; i++) {
            await expect(recurrenceCells.nth(i)).toContainText('One-time');
        }
    });

    test('AC-1.6 — Overdue filter tab only appears when overdue jobs exist', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const overdueLink = page.locator('.subsubsub a', { hasText: /^Overdue/ });

        // If there are overdue jobs the tab is visible; if not the tab isn't rendered.
        const visible = await overdueLink.isVisible();
        if (visible) {
            await overdueLink.click();
            await expect(page).toHaveURL(/filter_status=overdue/);
            // Every matching row should show an Overdue badge
            await expect(page.locator('.gca-overdue-badge').first()).toBeVisible();
        } else {
            // No overdue jobs — that's a valid state; just verify the tab is absent.
            await expect(overdueLink).toHaveCount(0);
        }
    });

    test('AC-1.7 — search by hook name filters results', async ({ page }) => {
        await page.goto(CRON_PAGE);

        // Use a known WP core hook that will always be present
        const searchTerm = 'wp_';
        await page.fill('input[name="s"]', searchTerm);
        await page.click('input[type="submit"]');

        await expect(page).toHaveURL(new RegExp(`s=${encodeURIComponent(searchTerm)}`));

        // All visible hook names should contain the search term
        const hooks = page.locator('.gca-cron-table .column-hook strong');
        const count = await hooks.count();
        for (let i = 0; i < count; i++) {
            const text = (await hooks.nth(i).textContent()) ?? '';
            expect(text.toLowerCase()).toContain(searchTerm.toLowerCase());
        }
    });

    test('AC-1.7 — search with no match shows empty state', async ({ page }) => {
        await page.goto(CRON_PAGE);
        await page.fill('input[name="s"]', 'zzz_this_hook_does_not_exist_zzz');
        await page.locator('input[name="s"]').press('Enter');
        await expect(page.locator('.notice-info, .notice.notice-info')).toContainText('No cron jobs match');
    });

});
