/**
 * WF-4.x — Deletion & retirement workflow.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { loginAs, isAccessDenied } from '../helpers/login';
import { createPost, deletePost, getPostStatus, setPostStatus } from '../helpers/wp-cli';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';
const PUBLISHER_USER   = process.env.WP_PUBLISHER_USER       || '';
const PUBLISHER_PASS   = process.env.WP_PUBLISHER_PASSWORD   || '';

const CONTRIBUTOR_AUTH = path.join(__dirname, '../.auth/contributor.json');
const PUBLISHER_AUTH   = path.join(__dirname, '../.auth/publisher.json');

let draftPageId: number;
let livePageId: number;
let archivePageId: number;

test.describe('Deletion & retirement (WF-4.x)', () => {

    test.beforeAll(async () => {
        // Draft must be contributor-owned so contributor can see the Trash link on it.
        draftPageId   = createPost('WF-4 Draft Page',   'draft',   'page', CONTRIBUTOR_USER);
        livePageId    = createPost('WF-4 Live Page',    'publish');
        archivePageId = createPost('WF-4 Archive Page', 'publish');
    });

    test.afterAll(async () => {
        for (const id of [draftPageId, livePageId, archivePageId]) {
            if (id) {
                try { deletePost(id); } catch { /* already deleted */ }
            }
        }
    });

    test('WF-4.1 — Contributor cannot delete a Draft page; sees retirement request notice', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        // Use stored session — loginAs(contributor) fails headless because the
        // backdoor login redirects non-admins to the front page, not /wp-admin/.
        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${draftPageId}&action=edit`);
        const trashLink = page.locator(`a.submitdelete`).first();

        if (await trashLink.isVisible({ timeout: 3000 })) {
            // If the GCA plugin shows a trash link, clicking it should trigger a
            // retirement request rather than an actual deletion.
            const href = await trashLink.getAttribute('href') ?? '';
            await page.goto(href);
            await page.waitForLoadState('networkidle');
        } else {
            // Contributor has no delete_pages capability — no trash link is shown.
            // Direct trash attempt (without nonce) redirects silently to the page list.
            await page.goto(`/wp-admin/post.php?post=${draftPageId}&action=trash`);
            await page.waitForLoadState('networkidle');
        }

        // Either way the page must not have been deleted.
        const status = getPostStatus(draftPageId);
        expect(['draft', 'pending', 'gca_retirement_requested']).toContain(status);

        await ctx.close();
    });

    test('WF-4.2 — Contributor cannot trash a Published page', async ({ browser }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const ctx  = await browser.newContext({ storageState: CONTRIBUTOR_AUTH });
        const page = await ctx.newPage();

        const response = await page.goto(`/wp-admin/post.php?post=${livePageId}&action=trash`);
        const bodyText = (await page.locator('body').textContent()) ?? '';
        const blocked  = isAccessDenied(bodyText) || response?.status() === 403;

        expect(blocked || page.url().includes('gca_delete_blocked')).toBe(true);

        await ctx.close();
    });

    test('WF-4.3 — Publisher can trash a Published page', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        // Get the nonce-bearing trash URL from the edit screen rather than
        // constructing it manually — WordPress requires a nonce for the trash action.
        await page.goto(`/wp-admin/post.php?post=${livePageId}&action=edit`);
        await page.waitForLoadState('networkidle');
        const trashHref = await page.locator('a.submitdelete').first().getAttribute('href');
        if (trashHref) {
            await page.goto(trashHref);
            await page.waitForLoadState('networkidle');
        }

        await page.goto('/wp-admin/edit.php?post_type=page&post_status=trash');
        await expect(page.locator(`tr#post-${livePageId}`)).toBeVisible();

        await ctx.close();
    });

    test('WF-4.4 — Publisher can permanently delete a trashed page', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        await page.goto('/wp-admin/edit.php?post_type=page&post_status=trash');
        const row = page.locator(`tr#post-${livePageId}`);
        if (await row.isVisible({ timeout: 3000 })) {
            // WordPress row-actions are hidden until hover; the Delete Permanently
            // link is inside <span class="delete"><a>…</a></span>, not <a class="delete">.
            await row.hover();
            await row.locator('span.delete a').click({ force: true });
            await page.waitForLoadState('networkidle');
            await expect(page.locator(`tr#post-${livePageId}`)).toHaveCount(0);
        }

        await ctx.close();
    });

    test('WF-4.5 — Publisher can archive a Published page', async ({ browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const ctx  = await browser.newContext({ storageState: PUBLISHER_AUTH });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/post.php?post=${archivePageId}&action=edit`);
        await page.waitForLoadState('networkidle');

        // Classic editor: the status select is hidden until the "Edit" link is clicked.
        await page.locator('.edit-post-status').click();
        await page.selectOption('#post_status', 'gca_archived');
        await page.locator('.save-post-status').click();

        // Click Update and wait for the page to reload back to the editor.
        await page.locator('#publish').click();
        await page.waitForLoadState('networkidle');

        // Assert via the UI label, not WP-CLI — this is what was missing before.
        await expect(page.locator('#post-status-display')).toHaveText('Archived');
        // Cross-check with DB to confirm the value is persisted correctly.
        expect(getPostStatus(archivePageId)).toBe('gca_archived');
        await ctx.close();
    });

    test('WF-4.6 — Archived page returns 404 on the frontend', async ({ page }) => {
        if (getPostStatus(archivePageId) !== 'gca_archived') setPostStatus(archivePageId, 'gca_archived');
        const response = await page.goto('/wf-4-archive-page/');
        expect(response?.status()).toBe(404);
    });

});
