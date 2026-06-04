/**
 * PERM-2.x — Publisher role permissions.
 *
 * Runs authenticated as gca_publisher.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { isAccessDenied } from '../helpers/login';
import { createPost, deletePost } from '../helpers/wp-cli';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const PUBLISHER_USER = process.env.WP_PUBLISHER_USER || '';

test.use({
    storageState: path.join(__dirname, '../.auth/publisher.json'),
});

test.describe('Publisher permissions (PERM-2.x)', () => {

    test('PERM-2.1 — Publisher can access the Pages list', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const response = await page.goto('/wp-admin/edit.php?post_type=page');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1', { hasText: 'Pages' })).toBeVisible();
    });

    test('PERM-2.2 — Publisher can create a new Page', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=page');
        // Visibility of the title field is sufficient proof of access.
        await expect(page.locator('#title')).toBeVisible();
    });

    test('PERM-2.3 — Publisher sees the Publish button', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await expect(page.locator('#publish')).toBeVisible();
    });

    test('PERM-2.4 — Publisher can delete any page', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const pageId = createPost('PERM-2.4 Delete Test', 'draft', 'page');

        await page.goto('/wp-admin/edit.php?post_type=page');
        const row = page.locator(`tr#post-${pageId}`);
        await expect(row).toBeVisible();
        // Row actions are CSS hover-only; hover first to make Trash link visible.
        await row.hover();
        await row.locator('a.submitdelete').click();
        await expect(row).toHaveCount(0);
    });

    test('PERM-2.5 — Publisher can access news CPT and publish', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=news');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        // CPT may not exist in all envs; only assert if the CPT is registered.
        if (bodyText.includes('Invalid post type')) {
            test.skip(true, 'news CPT not registered in this environment');
        }
        // Title field visible proves access — don't use isAccessDenied which can
        // match unrelated "not allowed" text from workflow notices.
        await expect(page.locator('#title')).toBeVisible();
    });

    test('PERM-2.6 — Publisher cannot access Settings → Feature Flags', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/options-general.php?page=gca-feature-flags');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-2.7 — Publisher cannot manage Users', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/users.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-2.8 — Publisher can see Pending pages from contributors', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const pageId = createPost('PERM-2.8 Pending Page', 'pending', 'page');

        try {
            await page.goto('/wp-admin/edit.php?post_type=page&post_status=pending');
            await expect(page.locator(`tr#post-${pageId}`)).toBeVisible();
        } finally {
            deletePost(pageId);
        }
    });

    test('PERM-2.9 — Publisher sees Rejection Comments meta box on edit screen', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const pageId = createPost('PERM-2.9 Meta Box Test', 'pending', 'page');

        try {
            await page.goto(`/wp-admin/post.php?post=${pageId}&action=edit`);
            await expect(page.locator('div#gca_rejection_comments')).toBeVisible();
        } finally {
            deletePost(pageId);
        }
    });

});
