/**
 * WF-1.x — Happy path: Contributor submits a page, Publisher approves.
 *
 * Runs as admin. Uses the contributor and publisher auth contexts for
 * role-specific steps.
 */
import { test, expect, Browser } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs } from '../helpers/login';
import { createPost, deletePost, deletePostLock, getPostStatus, setPostStatus } from '../helpers/wp-cli';

// Non-admin users (contributor) cannot be re-authenticated headless via the
// ?gcawebadmin backdoor — the redirect lands on the front page, not /wp-admin/.
// Use the stored session files created by the auth setup projects instead.
const CONTRIBUTOR_AUTH = path.join(__dirname, '../.auth/contributor.json');
const PUBLISHER_AUTH   = path.join(__dirname, '../.auth/publisher.json');

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER       || '';
const PUBLISHER_PASS   = process.env.WP_PUBLISHER_PASSWORD   || '';

let testPageId: number;

test.describe('Happy path — new content (WF-1.x)', () => {

    test.beforeAll(async () => {
        // Page must be contributor-owned — contributors can only edit their own pages.
        testPageId = createPost('WF-1 Test Page', 'draft', 'page', CONTRIBUTOR_USER);
    });

    test.afterAll(async () => {
        if (testPageId) deletePost(testPageId);
    });

    test('WF-1.1 — Contributor can open an existing draft page for editing', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${testPageId}&action=edit`);
        await expect(page.locator('#wpbody')).toBeVisible();
        expect(page.url()).toContain('post.php');

        await ctx.close();
    });

    test('WF-1.2 — Contributor sees Submit for Review, not Publish', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${testPageId}&action=edit`);
        await page.waitForLoadState('networkidle');

        // Classic editor (Classic Editor plugin active): #publish exists but
        // PublishPress Statuses renames its value to "Submit for Review" for contributors.
        const classicPublish = page.locator('#publish');
        if (await classicPublish.count() > 0) {
            const btnValue = (await classicPublish.getAttribute('value')) ?? '';
            expect(btnValue.toLowerCase()).not.toBe('publish');
        } else {
            // Gutenberg path.
            const gutenbergPublish = page.locator('.editor-post-publish-button__button:visible');
            if (await gutenbergPublish.count() > 0) {
                const btnText = (await gutenbergPublish.first().textContent()) ?? '';
                expect(btnText.toLowerCase()).not.toContain('publish');
            }
        }

        await ctx.close();
    });

    test('WF-1.3 — Submitting for review sets status to Pending', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${testPageId}&action=edit`);
        await page.waitForLoadState('networkidle');

        // Classic editor (Classic Editor plugin active): #publish says "Submit for Review"
        // for contributors — clicking it sets the status to pending.
        // #post_status exists in the DOM but is hidden (collapsed Publish meta box).
        const classicPublish = page.locator('#publish');
        if (await classicPublish.count() > 0) {
            await classicPublish.click();
            await page.waitForLoadState('networkidle');
        } else {
            // Gutenberg path.
            const topBtn = page.locator('.editor-post-publish-button__button');
            await topBtn.click();
            await page.waitForLoadState('networkidle');
            const panelBtn = page.locator('.editor-post-publish-panel__header-publish-button button, .editor-post-publish-button__button');
            if (await panelBtn.first().isVisible({ timeout: 1500 }).catch(() => false)) {
                await panelBtn.first().click();
                await page.waitForLoadState('networkidle');
            }
        }

        expect(getPostStatus(testPageId)).toBe('pending');
        await ctx.close();
    });

    test('WF-1.4 — Pending page appears in publisher dashboard', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        // Ensure page is in pending state (WF-1.3 may have run before this).
        if (getPostStatus(testPageId) !== 'pending') setPostStatus(testPageId, 'pending');

        // Use stored publisher session — loginAs(publisher) intermittently fails
        // because ?gcawebadmin redirects non-admin users to the front page.
        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        await page.goto('/wp-admin/edit.php?post_type=page&post_status=pending');
        await expect(page.locator(`a[href*="post=${testPageId}"]`).first()).toBeVisible();

        await ctx.close();
    });

    test('WF-1.5 — Publisher can approve and publish the pending page', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        if (getPostStatus(testPageId) !== 'pending') setPostStatus(testPageId, 'pending');

        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        // Clear any editor lock left by the contributor session in WF-1.3.
        deletePostLock(testPageId);
        await page.goto(`/wp-admin/post.php?post=${testPageId}&action=edit`);
        await page.waitForLoadState('networkidle');

        // Classic editor uses #publish; Gutenberg uses the top-bar button.
        const classicPublish = page.locator('#publish');
        if (await classicPublish.count() > 0) {
            await classicPublish.click();
            await page.waitForURL(/post\.php/);
        } else {
            await page.locator('.editor-post-publish-button__button').click();
            await page.waitForLoadState('networkidle');
            // Handle pre-publish panel.
            const panelBtn = page.locator('.editor-post-publish-panel__header-publish-button button, .editor-post-publish-button__button');
            if (await panelBtn.first().isVisible({ timeout: 1500 }).catch(() => false)) {
                await panelBtn.first().click();
                await page.waitForLoadState('networkidle');
            }
        }

        expect(getPostStatus(testPageId)).toBe('publish');
        await ctx.close();
    });

    test('WF-1.6 — Published page is visible on the frontend', async ({ page }) => {
        if (getPostStatus(testPageId) !== 'publish') setPostStatus(testPageId, 'publish');
        const response = await page.goto('/wf-1-test-page/');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1')).toContainText('WF-1 Test Page');
    });

});
