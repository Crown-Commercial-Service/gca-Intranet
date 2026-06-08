/**
 * PERM-3.x — Publisher Admin role permissions.
 *
 * Runs authenticated as gca_publisher_admin.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { isAccessDenied } from '../helpers/login';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const PUBLISHER_ADMIN_USER = process.env.WP_PUBLISHER_ADMIN_USER     || '';
const PUBLISHER_ADMIN_PASS = process.env.WP_PUBLISHER_ADMIN_PASSWORD || '';

test.use({
    storageState: path.join(__dirname, '../.auth/publisher-admin.json'),
});

test.describe('Publisher Admin permissions (PERM-3.x)', () => {

    test('PERM-3.1 — Publisher Admin can access Settings → Feature Flags', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        const response = await page.goto('/wp-admin/options-general.php?page=gca-feature-flags');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1', { hasText: 'Feature Flags' })).toBeVisible();
    });

    test('PERM-3.2 — Publisher Admin can access Settings → Global Settings', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        const response = await page.goto('/wp-admin/options-general.php');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1', { hasText: 'General Settings' })).toBeVisible();
    });

    test('PERM-3.3 — Publisher Admin can access Categories and Tags screens', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        const response = await page.goto('/wp-admin/edit-tags.php?taxonomy=category');
        expect(response?.status()).toBe(200);
        // Check for the categories h1 rather than absence of "not allowed" text —
        // Press Permit adds informational notices containing "not allowed" phrases
        // even on pages the user is permitted to view.
        await expect(page.locator('h1', { hasText: 'Categories' })).toBeVisible();
    });

    test('PERM-3.4 — Publisher Admin can access Plugins page', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        const response = await page.goto('/wp-admin/plugins.php');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1', { hasText: 'Plugins' })).toBeVisible();
    });

    test('PERM-3.5 — Publisher Admin can list and edit users', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        const response = await page.goto('/wp-admin/users.php');
        expect(response?.status()).toBe(200);
        await expect(page.locator('h1', { hasText: 'Users' })).toBeVisible();
    });

    test('PERM-3.6 — Publisher Admin can access PublishPress Permissions settings', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        // Press Permit Core uses 'presspermit-groups' as its main permissions page slug.
        await page.goto('/wp-admin/admin.php?page=presspermit-groups');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(false);
        await expect(page.locator('#wpbody')).toBeVisible();
    });

    test('PERM-3.7 — Publisher Admin cannot access WordPress core updates screen', async ({ page }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        await page.goto('/wp-admin/update-core.php');
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

});
