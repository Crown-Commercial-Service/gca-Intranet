/**
 * WF-2.x — Rejection & revision flow.
 */
import { test, expect, Browser } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs } from '../helpers/login';
import { createPost, deletePost, deletePostLock, getPostStatus, setPostStatus } from '../helpers/wp-cli';

const CONTRIBUTOR_AUTH = path.join(__dirname, '../.auth/contributor.json');
const PUBLISHER_AUTH   = path.join(__dirname, '../.auth/publisher.json');

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER       || '';
const PUBLISHER_PASS   = process.env.WP_PUBLISHER_PASSWORD   || '';

let pendingPageId: number;

test.describe('Rejection flow (WF-2.x)', () => {

    test.beforeAll(async () => {
        // Page must be contributor-owned — contributors can only edit their own pages.
        pendingPageId = createPost('WF-2 Rejection Test Page', 'pending', 'page', CONTRIBUTOR_USER);
    });

    test.afterAll(async () => {
        if (pendingPageId) deletePost(pendingPageId);
    });

    test('WF-2.1 — Publisher sees Rejection Comments meta box on a Pending page', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        // Use stored publisher session — loginAs(publisher) intermittently fails
        // because ?gcawebadmin redirects non-admin users to the front page.
        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        // Use the div selector — both the wrapper div and the textarea share this ID.
        await expect(page.locator('div#gca_rejection_comments')).toBeVisible();
        await expect(page.locator('div#gca_rejection_comments textarea')).toBeVisible();

        await ctx.close();
    });

    test('WF-2.2 — Publisher submits rejection and page reverts to Draft', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        // Clear lock left by WF-2.1 publisher session.
        deletePostLock(pendingPageId);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.fill('div#gca_rejection_comments textarea', 'Please improve the introduction.');
        // Register dialog handler BEFORE clicking — the dialog fires synchronously on click.
        page.on('dialog', d => d.accept());
        await page.locator('button[name="gca_submit_rejection"]').click();
        await page.waitForLoadState('networkidle');
        expect(getPostStatus(pendingPageId)).toBe('draft');

        await ctx.close();
    });

    test('WF-2.3 — Rejected page has rejection comments stored', async ({ page }) => {
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await expect(page.locator('div#gca_rejection_comments textarea')).toHaveValue(/Please improve the introduction\./);
    });

    test('WF-2.4 — Contributor sees Reviewer Feedback meta box on rejected Draft', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        // Clear any lock left by WF-2.3 (admin context).
        deletePostLock(pendingPageId);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await expect(page.locator('#gca_rejection_notice .notice-warning')).toBeVisible();
        await expect(page.locator('#gca_rejection_notice')).toContainText('Please improve the introduction.');

        await ctx.close();
    });

    test('WF-2.5 — Contributor re-submits and page returns to Pending', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        if (getPostStatus(pendingPageId) !== 'draft') setPostStatus(pendingPageId, 'draft');

        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        // Clear any editor lock left by previous sessions (WF-2.2 publisher, WF-2.4 contributor).
        deletePostLock(pendingPageId);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.waitForLoadState('networkidle');

        // Classic editor (Classic Editor plugin active): #publish says "Submit for Review"
        // for contributors — clicking it re-submits the draft back to pending.
        const classicPublish = page.locator('#publish');
        if (await classicPublish.count() > 0) {
            await classicPublish.click();
            await page.waitForLoadState('networkidle');
        } else {
            // Gutenberg path.
            await page.locator('.editor-post-publish-button__button').click();
            await page.waitForLoadState('networkidle');
            const panelBtn = page.locator('.editor-post-publish-panel__header-publish-button button, .editor-post-publish-button__button');
            if (await panelBtn.first().isVisible({ timeout: 1500 }).catch(() => false)) {
                await panelBtn.first().click();
                await page.waitForLoadState('networkidle');
            }
        }

        expect(getPostStatus(pendingPageId)).toBe('pending');
        await ctx.close();
    });

    test('WF-2.6 — Re-submitted page shows no stale Reviewer Feedback', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        const noticeBox = page.locator('#gca_rejection_notice .notice-warning');
        await expect(noticeBox).toHaveCount(0);

        await ctx.close();
    });

});
