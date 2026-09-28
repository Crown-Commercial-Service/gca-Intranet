/**
 * WF-2.x — Rejection & revision flow.
 *
 * One recorded context per role is kept open for the whole file (via
 * beforeAll/afterAll) rather than a fresh one per test, so each role's video
 * is one continuous recording of every step it performs, not a separate
 * clip per test.
 */
import { test, expect, Browser, BrowserContext, Page } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { newRecordedContext, closeRecordedContext } from '../helpers/context';
import { createPost, deletePost, deletePostLock, getPostStatus, setPostStatus } from '../helpers/wp-cli';

const CONTRIBUTOR_AUTH = path.join(__dirname, '../.auth/contributor.json');
const PUBLISHER_AUTH   = path.join(__dirname, '../.auth/publisher.json');

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER   || '';

let pendingPageId: number;

let contributorCtx: BrowserContext;
let contributorPage: Page;
let publisherCtx: BrowserContext;
let publisherPage: Page;

test.describe('Rejection flow (WF-2.x)', () => {

    test.beforeAll(async ({ browser }, testInfo) => {
        // Page must be contributor-owned — contributors can only edit their own pages.
        pendingPageId = createPost('WF-2 Rejection Test Page', 'pending', 'page', CONTRIBUTOR_USER);

        if (CONTRIBUTOR_USER) {
            ({ ctx: contributorCtx, page: contributorPage } = await newRecordedContext(browser, testInfo, { storageState: CONTRIBUTOR_AUTH }));
        }
        if (PUBLISHER_USER) {
            ({ ctx: publisherCtx, page: publisherPage } = await newRecordedContext(browser, testInfo, { storageState: PUBLISHER_AUTH }));
        }
    });

    test.afterAll(async ({}, testInfo) => {
        if (contributorCtx) await closeRecordedContext(contributorCtx, contributorPage, testInfo);
        if (publisherCtx) await closeRecordedContext(publisherCtx, publisherPage, testInfo);
        if (pendingPageId) deletePost(pendingPageId);
    });

    test('WF-2.1 — Publisher sees Rejection Comments meta box on a Pending page', async () => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        const page = publisherPage;
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        // Use the div selector — both the wrapper div and the textarea share this ID.
        await expect(page.locator('div#gca_rejection_comments')).toBeVisible();
        await expect(page.locator('div#gca_rejection_comments textarea')).toBeVisible();
    });

    test('WF-2.2 — Publisher submits rejection and page reverts to Draft', async () => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        const page = publisherPage;

        // Clear lock left by WF-2.1 publisher session.
        deletePostLock(pendingPageId);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await page.fill('div#gca_rejection_comments textarea', 'Please improve the introduction.');
        // Register dialog handler BEFORE clicking — the dialog fires synchronously on click.
        page.on('dialog', d => d.accept());
        await page.locator('button:has-text("Submit Rejection")').click();
        await page.waitForLoadState('networkidle');
        expect(getPostStatus(pendingPageId)).toBe('draft');
    });

    test('WF-2.3 — Rejected page has rejection comments stored', async ({ page }) => {
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        await expect(page.locator('div#gca_rejection_comments textarea')).toHaveValue(/Please improve the introduction\./);
    });

    test('WF-2.4 — Contributor sees Reviewer Feedback meta box on rejected Draft', async () => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        const page = contributorPage;

        // Clear any lock left by WF-2.3 (admin context).
        deletePostLock(pendingPageId);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        // Contributors see the "Rejection Feedback" meta box (render_contributor_meta_box),
        // which shares the same wrapper id as the reviewer's "Rejection Comments" box.
        await expect(page.locator('div#gca_rejection_comments')).toBeVisible();
        await expect(page.locator('div#gca_rejection_comments')).toContainText('Please improve the introduction.');
    });

    test('WF-2.5 — Contributor re-submits and page returns to Pending', async () => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        if (getPostStatus(pendingPageId) !== 'draft') setPostStatus(pendingPageId, 'draft');

        const page = contributorPage;

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
    });

    test('WF-2.6 — Re-submitted page shows no stale Reviewer Feedback', async () => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        if (getPostStatus(pendingPageId) !== 'pending') setPostStatus(pendingPageId, 'pending');

        const page = contributorPage;
        // WF-2.5's own redirect can still be settling when this test starts — the
        // page is reused across tests now, so a fresh goto() here can collide with
        // it ("interrupted by another navigation"). A brief pause lets it finish;
        // retrying the goto() itself isn't reliable since the retry can just as
        // easily collide with the tail of the same still-resolving redirect.
        await page.waitForTimeout(1000);
        await page.goto(`/wp-admin/post.php?post=${pendingPageId}&action=edit`);
        const noticeBox = page.locator('#gca_rejection_notice .notice-warning');
        await expect(noticeBox).toHaveCount(0);
    });

});
