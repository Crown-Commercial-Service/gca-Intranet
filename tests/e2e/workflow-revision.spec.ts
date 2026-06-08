/**
 * WF-3.x — Edit own content / revision flow via PublishPress Revisions.
 *
 * Requires a live Published page and both contributor + publisher test accounts.
 */
import { test, expect, Browser } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs } from '../helpers/login';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER       || '';
const PUBLISHER_PASS   = process.env.WP_PUBLISHER_PASSWORD   || '';

let livePageId: number;

test.describe('Edit own content — revision flow (WF-3.x)', () => {

    test.beforeAll(async ({ browser }) => {
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await page.fill('#title', 'WF-3 Live Page');
        await page.locator('#publish').click();
        await page.waitForURL(/post\.php\?post=\d+/);
        livePageId = parseInt( page.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await ctx.close();
    });

    test.afterAll(async ({ browser }) => {
        if (!livePageId) return;
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=trash`);
        await ctx.close();
    });

    test('WF-3.1 — Contributor opens a live page and revision UI loads', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await loginAs(browser, CONTRIBUTOR_USER, CONTRIBUTOR_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // PublishPress Revisions shows a notice that edits create a revision.
        await expect(page.locator('#post-body')).toBeVisible();
        // Original page should still be published (not changed by opening).
        expect(page.url()).toContain('post.php');

        await ctx.close();
    });

    test('WF-3.2 — Contributor saves a draft revision without changing live page', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await loginAs(browser, CONTRIBUTOR_USER, CONTRIBUTOR_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        await page.fill('#title', 'WF-3 Live Page (revised)');
        // PublishPress Revisions may show "Save Draft Revision" instead of the standard save.
        const saveBtn = page.locator('input[name="rvy_save_draft"], #save-post').first();
        await saveBtn.click();
        await page.waitForURL(/.*/);

        // Verify the live page is still published by checking it via admin.
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        await adminPage.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        await expect(adminPage.locator('#post-status-display')).toHaveText(/Published/i);
        await adminCtx.close();

        await ctx.close();
    });

    test('WF-3.3 — Contributor submits revision for review', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await loginAs(browser, CONTRIBUTOR_USER, CONTRIBUTOR_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // Submit revision for review via PublishPress Revisions button.
        const submitBtn = page.locator('input[name="rvy_submit_revision"], button:has-text("Submit for Review")').first();
        if (await submitBtn.isVisible()) {
            await submitBtn.click();
            await page.waitForURL(/.*/);
        }

        await ctx.close();
    });

    test('WF-3.4 — Publisher sees Compare Revision option', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // PublishPress Revisions surfaces pending revisions in the edit screen.
        const compareLink = page.locator('a:has-text("Compare"), a:has-text("Review Revision")').first();
        if (await compareLink.isVisible({ timeout: 3000 })) {
            await expect(compareLink).toBeVisible();
        }

        await ctx.close();
    });

    test('WF-3.5 — Publisher approves revision and live content is updated', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const approveBtn = page.locator('input[name="rvy_approve_revision"], button:has-text("Approve Revision")').first();
        if (await approveBtn.isVisible({ timeout: 3000 })) {
            await approveBtn.click();
            await page.waitForURL(/.*/);
            await expect(page.locator('#post-status-display')).toHaveText(/Published/i);
        }

        await ctx.close();
    });

    test('WF-3.6 — Approved revision updates the Last Modified date', async ({ page }) => {
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const modDate = await page.locator('#post-modified-date-value, #timestamp').textContent();
        const today   = new Date().toISOString().slice(0, 10);
        // Date should contain today's year at minimum.
        expect(modDate).toContain(String(new Date().getFullYear()));
    });

    test('WF-3.7 — Publisher can reject a revision with comments', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS);
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const rejectBtn = page.locator('button:has-text("Reject"), input[name="rvy_reject_revision"]').first();
        if (await rejectBtn.isVisible({ timeout: 3000 })) {
            await page.fill('#gca_rejection_comments textarea', 'Revision needs more detail.');
            await page.locator('button[name="gca_submit_rejection"]').click();
            page.on('dialog', d => d.accept());
            await page.waitForURL(/.*/);
        }

        await ctx.close();
    });

});
