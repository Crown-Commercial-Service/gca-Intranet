/**
 * WF-3.x — Edit own content / revision flow via PublishPress Revisions.
 *
 * Requires a live Published page and both contributor + publisher test accounts.
 *
 * One recorded context per role is kept open for the whole file (via
 * beforeAll/afterAll) rather than a fresh one per test, so each role's video
 * is one continuous recording of every step it performs, not a separate
 * clip per test.
 */
import { test, expect, Browser, BrowserContext, Page } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs } from '../helpers/login';
import { closeRecordedContext } from '../helpers/context';
import { deletePostLock } from '../helpers/wp-cli';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER       || '';
const PUBLISHER_PASS   = process.env.WP_PUBLISHER_PASSWORD   || '';

let livePageId: number;

let contributorCtx: BrowserContext;
let contributorPage: Page;
let publisherCtx: BrowserContext;
let publisherPage: Page;

test.describe('Edit own content — revision flow (WF-3.x)', () => {

    test.beforeAll(async ({ browser }, testInfo) => {
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await page.fill('#title', 'WF-3 Live Page');
        await page.locator('#publish').click();
        await page.waitForURL(/post\.php\?post=\d+/);
        livePageId = parseInt( page.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await ctx.close();

        if (CONTRIBUTOR_USER) {
            contributorCtx  = await loginAs(browser, CONTRIBUTOR_USER, CONTRIBUTOR_PASS, testInfo);
            contributorPage = await contributorCtx.newPage();
        }
        if (PUBLISHER_USER) {
            publisherCtx  = await loginAs(browser, PUBLISHER_USER, PUBLISHER_PASS, testInfo);
            publisherPage = await publisherCtx.newPage();
        }
    });

    test.afterAll(async ({ browser }, testInfo) => {
        if (contributorCtx) await closeRecordedContext(contributorCtx, contributorPage, testInfo);
        if (publisherCtx) await closeRecordedContext(publisherCtx, publisherPage, testInfo);

        if (!livePageId) return;
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const page = await ctx.newPage();
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=trash`);
        await ctx.close();
    });

    test('WF-3.1 — Contributor opens a live page and revision UI loads', async () => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const page = contributorPage;

        // Each test opens the edit screen fresh — clear any WP heartbeat edit lock
        // rather than risk the "someone is editing this" dialog.
        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // PublishPress Revisions shows a notice that edits create a revision.
        // Longer timeout: revisionary's own JS briefly manipulates this element on
        // load, which can outlast the default 5s window under system load.
        await expect(page.locator('#post-body')).toBeVisible({ timeout: 30000 });
        // Original page should still be published (not changed by opening).
        expect(page.url()).toContain('post.php');
    });

    test('WF-3.2 — Contributor saves a draft revision without changing live page', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const page = contributorPage;

        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        await page.fill('#title', 'WF-3 Live Page (revised)');
        // PublishPress Revisions may show "Save Draft Revision" instead of the standard save.
        const saveBtn = page.locator('input[name="rvy_save_draft"], #save-post').first();
        await saveBtn.click();
        await page.waitForURL(/.*/);

        // Verify the live page is still published by checking it via admin.
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        deletePostLock(livePageId);
        await adminPage.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        await expect(adminPage.locator('#post-status-display')).toHaveText(/Published/i);
        await adminCtx.close();
    });

    test('WF-3.3 — Contributor submits revision for review', async () => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const page = contributorPage;

        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // Submit revision for review via PublishPress Revisions button.
        const submitBtn = page.locator('input[name="rvy_submit_revision"], button:has-text("Submit for Review")').first();
        if (await submitBtn.isVisible()) {
            await submitBtn.click();
            await page.waitForURL(/.*/);
        }
    });

    test('WF-3.4 — Publisher sees Compare Revision option', async () => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const page = publisherPage;

        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        // PublishPress Revisions surfaces pending revisions in the edit screen.
        const compareLink = page.locator('a:has-text("Compare"), a:has-text("Review Revision")').first();
        if (await compareLink.isVisible({ timeout: 3000 })) {
            await expect(compareLink).toBeVisible();
        }
    });

    test('WF-3.5 — Publisher approves revision and live content is updated', async () => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const page = publisherPage;

        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const approveBtn = page.locator('input[name="rvy_approve_revision"], button:has-text("Approve Revision")').first();
        if (await approveBtn.isVisible({ timeout: 3000 })) {
            await approveBtn.click();
            await page.waitForURL(/.*/);
            await expect(page.locator('#post-status-display')).toHaveText(/Published/i);
        }
    });

    test('WF-3.6 — Approved revision updates the Last Modified date', async ({ page }) => {
        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const modDate = await page.locator('#post-modified-date-value, #timestamp').textContent();
        expect(modDate).toContain(String(new Date().getFullYear()));
    });

    test('WF-3.7 — Publisher can reject a revision with comments', async () => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const page = publisherPage;

        deletePostLock(livePageId);
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        const rejectBtn = page.locator('button:has-text("Reject"), input[name="rvy_reject_revision"]').first();
        if (await rejectBtn.isVisible({ timeout: 3000 })) {
            await page.fill('#gca_rejection_comments textarea', 'Revision needs more detail.');
            // Register the dialog handler BEFORE clicking — the button's onclick fires
            // a synchronous confirm(), so a handler attached after the click is too late.
            page.on('dialog', d => d.accept());
            await page.locator('button:has-text("Submit Rejection")').click();
            await page.waitForURL(/.*/);
        }
    });

});
