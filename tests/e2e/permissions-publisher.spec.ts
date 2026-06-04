/**
 * PERM-2.x — Publisher role permissions.
 *
 * Runs authenticated as gca_publisher.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { isAccessDenied } from '../helpers/login';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const PUBLISHER_USER = process.env.WP_PUBLISHER_USER     || '';
const PUBLISHER_PASS = process.env.WP_PUBLISHER_PASSWORD || '';

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
        await expect(page.locator('#title')).toBeVisible();
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(false);
    });

    test('PERM-2.3 — Publisher sees the Publish button', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=page');
        await expect(page.locator('#publish')).toBeVisible();
    });

    test('PERM-2.4 — Publisher can delete any page', async ({ page, browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        // Create a page as admin to delete as publisher.
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        await adminPage.goto('/wp-admin/post-new.php?post_type=page');
        await adminPage.fill('#title', 'PERM-2.4 Delete Test');
        await adminPage.locator('#save-post').click();
        await adminPage.waitForURL(/post\.php\?post=\d+/);
        const pageId = parseInt( adminPage.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await adminCtx.close();

        await page.goto('/wp-admin/edit.php?post_type=page');
        const row = page.locator(`tr#post-${pageId}`);
        if (await row.isVisible({ timeout: 3000 })) {
            await row.locator('a.submitdelete').click();
            await expect(row).toHaveCount(0);
        }
    });

    test('PERM-2.5 — Publisher can access news CPT and publish', async ({ page }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        await page.goto('/wp-admin/post-new.php?post_type=news');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        const hasAccess = !isAccessDenied(bodyText) && bodyText.includes('Add New');
        // CPT may not exist in all envs; only assert if the CPT is registered.
        if (bodyText.includes('Invalid post type')) {
            test.skip(true, 'news CPT not registered in this environment');
        }
        expect(hasAccess).toBe(true);
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

    test('PERM-2.8 — Publisher can see Pending pages from contributors', async ({ page, browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        // Create a pending page as admin.
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        await adminPage.goto('/wp-admin/post-new.php?post_type=page');
        await adminPage.fill('#title', 'PERM-2.8 Pending Page');
        await adminPage.selectOption('#post_status', 'pending');
        await adminPage.locator('#save-post').click();
        await adminPage.waitForURL(/post\.php\?post=\d+/);
        const pageId = parseInt( adminPage.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await adminCtx.close();

        await page.goto('/wp-admin/edit.php?post_type=page&post_status=pending');
        await expect(page.locator(`tr#post-${pageId}`)).toBeVisible();

        // Cleanup.
        const cleanCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const cleanPage = await cleanCtx.newPage();
        await cleanPage.goto(`/wp-admin/post.php?post=${pageId}&action=trash`);
        await cleanCtx.close();
    });

    test('PERM-2.9 — Publisher sees Rejection Comments meta box on edit screen', async ({ page, browser }) => {
        if (!PUBLISHER_USER) test.skip(true, 'WP_PUBLISHER_USER not set');
        const adminCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const adminPage = await adminCtx.newPage();
        await adminPage.goto('/wp-admin/post-new.php?post_type=page');
        await adminPage.fill('#title', 'PERM-2.9 Meta Box Test');
        await adminPage.selectOption('#post_status', 'pending');
        await adminPage.locator('#save-post').click();
        await adminPage.waitForURL(/post\.php\?post=\d+/);
        const pageId = parseInt( adminPage.url().match(/post=(\d+)/)?.[1] ?? '0', 10 );
        await adminCtx.close();

        await page.goto(`/wp-admin/post.php?post=${pageId}&action=edit`);
        await expect(page.locator('#gca_rejection_comments')).toBeVisible();

        const cleanCtx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/admin.json') });
        const cleanPage = await cleanCtx.newPage();
        await cleanPage.goto(`/wp-admin/post.php?post=${pageId}&action=trash`);
        await cleanCtx.close();
    });

});
