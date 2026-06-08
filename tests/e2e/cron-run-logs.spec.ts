/**
 * AC-5.x — Run Now (manual trigger)
 * AC-6.x — Execution logs
 *
 * These tests create a dedicated e2e cron job, run it manually, then inspect the logs.
 * The hook has no registered listener so no real work happens — but the log entry
 * is still created by handle_run_now() directly.
 */
import { test, expect, Page } from '@playwright/test';
import * as path from 'path';
import { CRON_PAGE } from '../playwright.config';

const AUTH_STATE = path.join(__dirname, '../.auth/admin.json');

const RUN_TEST_HOOK = `gca_e2e_run_${Date.now()}`;

function futureDateTime(offsetMinutes = 60): string {
    const d = new Date(Date.now() + offsetMinutes * 60 * 1000);
    return d.toISOString().slice(0, 16);
}

async function createTestJob(page: Page, hook: string) {
    await page.goto(`${CRON_PAGE}&action=add`);
    await page.fill('#cron_hook', hook);
    await page.fill('#cron_next_run', futureDateTime(60));
    await page.click('button[type="submit"]');
    await expect(page.locator('.notice-success')).toContainText('Cron job created.');
}

test.describe('Run Now — manual trigger (AC-5.x)', () => {

    test.beforeAll(async ({ browser }) => {
        const ctx  = await browser.newContext({ storageState: AUTH_STATE });
        const page = await ctx.newPage();
        await createTestJob(page, RUN_TEST_HOOK);
        await ctx.close();
    });

    test.afterAll(async ({ browser }) => {
        const ctx  = await browser.newContext({ storageState: AUTH_STATE });
        const page = await ctx.newPage();
        await page.goto(CRON_PAGE);
        const row = page.locator('.gca-cron-table tr', { hasText: RUN_TEST_HOOK });
        if (await row.count() > 0) {
            page.once('dialog', d => d.accept());
            await row.locator('button', { hasText: 'Delete' }).click();
        }
        await ctx.close();
    });

    test('AC-5.1 — Run Now button is present in each row', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const runBtn = page.locator('.gca-cron-table tr', { hasText: RUN_TEST_HOOK })
            .locator('button', { hasText: 'Run Now' });
        await expect(runBtn).toBeVisible();
    });

    test('AC-5.2 — clicking Run Now shows success notice', async ({ page }) => {
        await page.goto(CRON_PAGE);
        page.once('dialog', d => d.accept());
        await page.locator('.gca-cron-table tr', { hasText: RUN_TEST_HOOK })
            .locator('button', { hasText: 'Run Now' })
            .click();

        await expect(page.locator('.notice-success')).toContainText('Cron job triggered manually.');
    });

    test('AC-5.3 — a log entry with triggered_by=manual is created after Run Now', async ({ page }) => {
        await page.goto(CRON_PAGE);

        // Run it once more to ensure a fresh log entry exists
        page.once('dialog', d => d.accept());
        await page.locator('.gca-cron-table tr', { hasText: RUN_TEST_HOOK })
            .locator('button', { hasText: 'Run Now' })
            .click();

        // Follow the "View log" link in the success notice
        await page.locator('.notice-success a', { hasText: 'View log' }).click();

        await expect(page.locator('h1')).toContainText('Cron Logs');
        await expect(page.locator('.gca-log-table')).toBeVisible();

        // The most recent entry should be a manual trigger
        const firstRow = page.locator('.gca-log-table tbody tr').first();
        await expect(firstRow.locator('.gca-trigger-manual')).toBeVisible();

        // Duration cell should be present (non-empty)
        const durationText = await firstRow.locator('td').nth(2).textContent();
        expect(durationText).toMatch(/\d+/);
    });

});

test.describe('Execution logs page (AC-6.x)', () => {

    test('AC-6.1 — Logs link navigates to the logs page for that hook', async ({ page }) => {
        await page.goto(CRON_PAGE);

        // Find any job with a log entry (Last Run is not "Never") or use Last Run = Never link
        const lastRunCell = page.locator('.gca-cron-table .column-last-run a').first();
        await expect(lastRunCell).toBeVisible();
        await lastRunCell.click();

        await expect(page).toHaveURL(/action=logs/);
        await expect(page.locator('h1')).toContainText('Cron Logs');
    });

    test('AC-6.2 — log table has required columns', async ({ page }) => {
        // Navigate to logs for the run test hook (has entries from AC-5 tests above)
        const logsUrl = `${CRON_PAGE}&action=logs&hook=${encodeURIComponent(RUN_TEST_HOOK)}`;
        await page.goto(logsUrl);

        const thead = page.locator('.gca-log-table thead tr');
        await expect(thead).toContainText('Date / Time');
        await expect(thead).toContainText('Triggered By');
        await expect(thead).toContainText('Duration');
        await expect(thead).toContainText('Status');
        await expect(thead).toContainText('Stats');
        await expect(thead).toContainText('Output');
    });

    test('AC-6.3 — stats are displayed for jobs that fire gca_cron_run_completed', async ({ page }) => {
        // This test requires gca_sync_workday_users to have run at least once.
        const logsUrl = `${CRON_PAGE}&action=logs&hook=${encodeURIComponent('gca_sync_workday_users')}`;
        await page.goto(logsUrl);

        const noRuns = await page.locator('p', { hasText: 'No runs recorded yet' }).isVisible();
        if (noRuns) {
            test.skip(); // Sync hasn't run in this environment — manual test required.
            return;
        }

        const statsCell = page.locator('.gca-log-table tbody tr').first().locator('.gca-log-stats');
        // Expect at least one stat item (created / updated / skipped / errors)
        await expect(statsCell.locator('.gca-stat-item').first()).toBeVisible();
        const statText = await statsCell.textContent();
        expect(statText).toMatch(/Created|Updated|Skipped|Errors/i);
    });

});
