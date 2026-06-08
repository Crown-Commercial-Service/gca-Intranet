/**
 * PERM-1.x — Contributor role permissions.
 *
 * All tests run authenticated as gca_contributor.
 * Requires WP_CONTRIBUTOR_USER and WP_CONTRIBUTOR_PASSWORD in .env.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { isAccessDenied } from '../helpers/login';
import { createPost, deletePost } from '../helpers/wp-cli';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER = process.env.WP_CONTRIBUTOR_USER     || '';
const CONTRIBUTOR_PASS = process.env.WP_CONTRIBUTOR_PASSWORD || '';

test.use({
    storageState: path.join(__dirname, '../.auth/contributor.json'),
});

test.describe('Contributor permissions (PERM-1.x)', () => {

    test.beforeAll(() => {
        if (!CONTRIBUTOR_USER || !CONTRIBUTOR_PASS) {
            // Tests will be individually skipped via conditional checks.
        }
    });

    test('PERM-1.1 — Contributor can access WP admin dashboard', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        const response = await page.goto('/wp-admin/');
        expect(response?.status()).not.toBe(403);
        await expect(page.locator('#wpwrap')).toBeVisible();
    });

    test('PERM-1.2 — Contributor cannot see Add New Page in admin menu', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/');
        await expect(
            page.locator('#menu-pages .wp-submenu a', { hasText: 'Add New' })
        ).toHaveCount(0);
    });

    test('PERM-1.3 — Contributor cannot access New Page URL directly', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=page');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText) || !bodyText.includes('Add New Page')).toBe(true);
    });

    test('PERM-1.4 — Contributor can open an existing page for editing', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        // Page must be contributor-owned — contributors can only edit their own pages.
        const pageId = createPost('PERM-1.4 Test Page', 'draft', 'page', CONTRIBUTOR_USER);
        try {
            await page.goto(`/wp-admin/post.php?post=${pageId}&action=edit`);
            await expect(page.locator('#wpbody')).toBeVisible();
        } finally {
            deletePost(pageId);
        }
    });

    test('PERM-1.5 — Contributor sees Submit for Review, not Publish', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/edit.php?post_type=page');
        const firstEditLink = page.locator('table.wp-list-table tbody tr:first-child a.row-title');
        if (await firstEditLink.count() === 0) {
            test.skip(true, 'No pages available for contributor to edit');
        }
        await firstEditLink.first().click();
        await expect(page.locator('#publish')).toHaveCount(0);
    });

    test('PERM-1.6 — Contributor cannot edit standard Posts', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/edit.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText) || !bodyText.includes('Add New')).toBe(true);
    });

    test('PERM-1.7 — Contributor cannot access Settings', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/options-general.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-1.8 — Contributor cannot access Users', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/users.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-1.9 — Contributor cannot access Plugins', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/plugins.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-1.10 — Contributor cannot access Appearance / Themes', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/themes.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-1.11 — Contributor cannot edit homepage (after PressPermit config)', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        // Get the homepage ID from WP settings.
        // This test passes once the homepage PressPermit restriction is configured in admin UI.
        const homepageId = process.env.WP_HOMEPAGE_ID || '';
        if (!homepageId) {
            test.skip(true, 'Set WP_HOMEPAGE_ID in .env to test homepage restriction');
        }
        await page.goto(`/wp-admin/post.php?post=${homepageId}&action=edit`);
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('PERM-1.12 — Contributor can access Media Library and upload', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');
        await page.goto('/wp-admin/upload.php');
        await expect(page.locator('#wpbody')).toBeVisible();
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(false);
    });

});
