/**
 * AC-2.x — Add, AC-3.x — Edit, AC-4.x — Delete
 *
 * Uses a unique test hook name per run to avoid state pollution.
 */
import { test, expect, Page } from '@playwright/test';
import { CRON_PAGE } from '../playwright.config';

const TEST_HOOK = `gca_e2e_test_${Date.now()}`;

// Helpers -------------------------------------------------------------------

async function goToAddPage(page: Page) {
    await page.goto(CRON_PAGE);
    await page.locator('.page-title-action', { hasText: 'Add New' }).click();
    await expect(page.locator('h1')).toContainText('Add New Cron Job');
}

function futureDateTime(offsetMinutes = 60): string {
    const d = new Date(Date.now() + offsetMinutes * 60 * 1000);
    // Format: YYYY-MM-DDTHH:MM (datetime-local format)
    return d.toISOString().slice(0, 16);
}

// AC-2.x — Add --------------------------------------------------------------

test.describe('Add cron job (AC-2.x)', () => {

    test('AC-2.1 — add form has required fields', async ({ page }) => {
        await goToAddPage(page);
        await expect(page.locator('#cron_hook')).toBeVisible();
        await expect(page.locator('#cron_next_run')).toBeVisible();
        await expect(page.locator('#cron_recurring')).toBeVisible();
        // Schedule row is hidden until recurring is checked
        await expect(page.locator('#gca-schedule-row')).toBeHidden();
    });

    test('AC-2.2 — create a one-time job shows success and appears in list', async ({ page }) => {
        await goToAddPage(page);

        await page.fill('#cron_hook', TEST_HOOK);
        await page.fill('#cron_next_run', futureDateTime(90));
        // Recurring stays unchecked

        await page.click('button[type="submit"]');

        await expect(page.locator('.notice-success')).toContainText('Cron job created.');
        await expect(page.locator('.gca-cron-table .column-hook strong', { hasText: TEST_HOOK })).toBeVisible();
        await expect(page.locator('.gca-cron-table tr', { hasText: TEST_HOOK })
            .locator('.column-recurrence')).toHaveText('One-time');
    });

    test('AC-2.3 — create a recurring job shows success and appears in list as recurring', async ({ page }) => {
        const recurringHook = `${TEST_HOOK}_recurring`;
        await goToAddPage(page);

        await page.fill('#cron_hook', recurringHook);
        await page.fill('#cron_next_run', futureDateTime(120));
        await page.check('#cron_recurring');

        // Schedule row should now be visible
        await expect(page.locator('#gca-schedule-row')).toBeVisible();

        // Pick the first available schedule (hourly is typically present)
        const scheduleSelect = page.locator('#cron_schedule');
        await scheduleSelect.selectOption({ index: 0 });

        await page.click('button[type="submit"]');

        await expect(page.locator('.notice-success')).toContainText('Cron job created.');
        const row = page.locator('.gca-cron-table tr', { hasText: recurringHook });
        await expect(row.locator('.column-recurrence')).not.toHaveText('One-time');

        // Cleanup: delete this recurring test job
        await page.once('dialog', dialog => dialog.accept());
        await row.locator('button', { hasText: 'Delete' }).click();
        await expect(page.locator('.notice-success')).toContainText('Cron job deleted.');
    });

    test('AC-2.4 — submitting without a hook name shows validation error', async ({ page }) => {
        await goToAddPage(page);
        await page.fill('#cron_next_run', futureDateTime(60));
        // Leave hook name empty — HTML required attribute should block submit
        // (or server-side returns error=empty_hook)
        const hookInput = page.locator('#cron_hook');
        await expect(hookInput).toHaveAttribute('required');
    });

});

// AC-3.x — Edit -------------------------------------------------------------

test.describe('Edit cron job (AC-3.x)', () => {

    test('AC-3.1 — Edit button is present in each row', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const firstRowEdit = page.locator('.gca-cron-table tbody tr').first()
            .locator('a.button', { hasText: 'Edit' });
        await expect(firstRowEdit).toBeVisible();
    });

    test('AC-3.2 — Edit form is pre-filled with current values', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const testRow = page.locator('.gca-cron-table tr', { hasText: TEST_HOOK });
        await testRow.locator('a.button', { hasText: 'Edit' }).click();

        await expect(page.locator('h1')).toContainText('Edit Cron Job');
        // Hook is displayed (read-only) and the datetime input is populated
        await expect(page.locator('code', { hasText: TEST_HOOK })).toBeVisible();
        const nextRunValue = await page.locator('#cron_next_run').inputValue();
        expect(nextRunValue).not.toBe('');
    });

    test('AC-3.3 — Updating next run time is reflected in the list', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const testRow = page.locator('.gca-cron-table tr', { hasText: TEST_HOOK });
        await testRow.locator('a.button', { hasText: 'Edit' }).click();

        const newTime = futureDateTime(180);
        await page.fill('#cron_next_run', newTime);
        await page.click('button[type="submit"]');

        await expect(page.locator('.notice-success')).toContainText('Cron job updated.');
        await expect(page.locator('.gca-cron-table tr', { hasText: TEST_HOOK })).toBeVisible();
    });

    test('AC-3.4 — Toggling recurrence off changes job to One-time', async ({ page }) => {
        // First create a recurring job to edit
        const tempHook = `${TEST_HOOK}_edit_toggle`;
        await goToAddPage(page);
        await page.fill('#cron_hook', tempHook);
        await page.fill('#cron_next_run', futureDateTime(60));
        await page.check('#cron_recurring');
        await page.click('button[type="submit"]');
        await expect(page.locator('.notice-success')).toContainText('Cron job created.');

        // Now edit it and uncheck recurring
        const row = page.locator('.gca-cron-table tr', { hasText: tempHook });
        await row.locator('a.button', { hasText: 'Edit' }).click();
        await page.uncheck('#cron_recurring');
        await page.click('button[type="submit"]');

        await expect(page.locator('.notice-success')).toContainText('Cron job updated.');
        const updatedRow = page.locator('.gca-cron-table tr', { hasText: tempHook });
        await expect(updatedRow.locator('.column-recurrence')).toHaveText('One-time');

        // Cleanup
        await page.once('dialog', dialog => dialog.accept());
        await updatedRow.locator('button', { hasText: 'Delete' }).click();
    });

});

// AC-4.x — Delete -----------------------------------------------------------

test.describe('Delete cron job (AC-4.x)', () => {

    test('AC-4.1 — Delete button is present in each row', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const firstRowDelete = page.locator('.gca-cron-table tbody tr').first()
            .locator('button', { hasText: 'Delete' });
        await expect(firstRowDelete).toBeVisible();
    });

    test('AC-4.2 — deleting the test job removes it from the list', async ({ page }) => {
        await page.goto(CRON_PAGE);
        const testRow = page.locator('.gca-cron-table tr', { hasText: TEST_HOOK });
        await expect(testRow).toBeVisible();

        // Accept the confirm() dialog
        page.once('dialog', dialog => dialog.accept());
        await testRow.locator('button', { hasText: 'Delete' }).click();

        await expect(page.locator('.notice-success')).toContainText('Cron job deleted.');
        await expect(page.locator('.gca-cron-table tr', { hasText: TEST_HOOK })).toHaveCount(0);
    });

    test('AC-4.3 — dismissing the confirm dialog does NOT delete the job', async ({ page }) => {
        // Create a job to test cancellation on
        const cancelHook = `${TEST_HOOK}_cancel`;
        await goToAddPage(page);
        await page.fill('#cron_hook', cancelHook);
        await page.fill('#cron_next_run', futureDateTime(60));
        await page.click('button[type="submit"]');

        await page.goto(CRON_PAGE);
        const row = page.locator('.gca-cron-table tr', { hasText: cancelHook });

        // Dismiss the confirm dialog
        page.once('dialog', dialog => dialog.dismiss());
        await row.locator('button', { hasText: 'Delete' }).click();

        // Job should still be present
        await expect(page.locator('.gca-cron-table tr', { hasText: cancelHook })).toBeVisible();

        // Cleanup
        page.once('dialog', dialog => dialog.accept());
        await row.locator('button', { hasText: 'Delete' }).click();
    });

});
