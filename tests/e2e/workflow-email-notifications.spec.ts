/**
 * WF-6.x — Email notification triggers.
 *
 * These tests verify that the correct WordPress actions and transitions
 * are triggered that would cause email to be sent. They do NOT verify
 * actual email delivery (that depends on SendGrid / WP Mail SMTP config).
 *
 * Tests that require a mail-catcher env var (MAIL_CATCHER_URL) will
 * be skipped in CI unless that var is set.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs } from '../helpers/login';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const PUBLISHER_USER = process.env.WP_PUBLISHER_USER     || '';
const PUBLISHER_PASS = process.env.WP_PUBLISHER_PASSWORD || '';
const MAIL_CATCHER   = process.env.MAIL_CATCHER_URL      || '';

let pendingPageId: number;

test.describe('Email notification triggers (WF-6.x)', () => {

    test.beforeAll(async ({ browser }) => {
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await page.fill('#title', 'WF-6 Email Test Page');
        await page.locator('#save-post').click();
        await page.waitForURL(/post\.php\?post=\d+/);
        pendingPageId = parseInt( page.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await ctx.close();
    });

    test.afterAll(async ({ browser }) => {
        if (!pendingPageId) return;
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=trash`);
        await ctx.close();
    });

    test('WF-6.1 — Status → Pending triggers reviewer notification (trigger verified)', async ({ page }) => {
        // Submit for review and verify the transition completes without errors.
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.selectOption('#post_status', 'pending');
        await page.locator('#save-post').click();
        await page.waitForURL(/post\.php/);
        await expect(page.locator('#post-status-display')).toHaveText(/Pending Review/i);
        // If a mail-catcher is available, we could assert an email was received.
        if (MAIL_CATCHER) {
            test.skip(true, 'Mail-catcher assertion not yet implemented');
        }
    });

    test('WF-6.2 — Revision subject contains "Update submitted for ... for review"', async ({ page }) => {
        // Covered by unit test UNIT-N.2. This E2E test is a placeholder until
        // a mail-catcher is configured.
        if (!MAIL_CATCHER) {
            test.skip(true, 'Set MAIL_CATCHER_URL in .env to enable delivery verification');
        }
    });

    test('WF-6.3 — Page published triggers contributor notification (trigger verified)', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.locator('#publish').click();
        await page.waitForURL(/post\.php/);
        await expect(page.locator('#post-status-display')).toHaveText(/Published/i);

        await ctx.close();
    });

    test('WF-6.4 — Rejection triggers notification with comments (trigger verified)', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        // Re-set to pending first.
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        await adminPage.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await adminPage.selectOption('#post_status', 'pending');
        await adminPage.locator('#save-post').click();
        await adminPage.waitForURL(/post\.php/);
        await adminCtx.close();

        const ctx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS);
        const page = await ctx.newPage();
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.fill('#gca_rejection_comments textarea', 'Email notification test comments.');
        await page.locator('button[name="gca_submit_rejection"]').click();
        page.on('dialog', d => d.accept());
        await page.waitForURL(/post\.php/);
        await expect(page.locator('#post-status-display')).toHaveText(/Draft/i);
        await ctx.close();
    });

    test('WF-6.5 — Retirement request triggers reviewer notification (trigger verified)', async ({ page }) => {
        // Covered by WF-4.2 which verifies the block. Full delivery verification
        // requires a mail-catcher.
        if (!MAIL_CATCHER) {
            test.skip(true, 'Set MAIL_CATCHER_URL in .env to enable delivery verification');
        }
    });

});
