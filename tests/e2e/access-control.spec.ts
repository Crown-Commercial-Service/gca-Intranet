/**
 * AC-1.2 — Access restriction for non-admin users
 *
 * Requires a non-admin user to exist in the environment.
 * Set WP_SUBSCRIBER_USER and WP_SUBSCRIBER_PASSWORD in your .env file to enable.
 * If these are not set, the test is skipped.
 */
import { test, expect, Browser, BrowserContext } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';
import { CRON_PAGE } from '../playwright.config';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const SUBSCRIBER_USER = process.env.WP_SUBSCRIBER_USER     || '';
const SUBSCRIBER_PASS = process.env.WP_SUBSCRIBER_PASSWORD || '';

async function loginAs(browser: Browser, user: string, pass: string): Promise<BrowserContext> {
    const ctx  = await browser.newContext({ storageState: undefined });
    const page = await ctx.newPage();
    await page.goto('/wp-login.php');
    await page.fill('#user_login', user);
    await page.fill('#user_pass', pass);
    await page.click('#wp-submit');
    await page.waitForURL('**/wp-admin/**');
    await page.close();
    return ctx;
}

test.describe('Access control (AC-1.2)', () => {

    test('AC-1.2 — non-admin user cannot access the Cron Jobs page', async ({ browser }) => {
        if (!SUBSCRIBER_USER || !SUBSCRIBER_PASS) {
            test.skip(true, 'Set WP_SUBSCRIBER_USER and WP_SUBSCRIBER_PASSWORD in .env to run this test.');
            return;
        }

        const ctx  = await loginAs(browser, SUBSCRIBER_USER, SUBSCRIBER_PASS);
        const page = await ctx.newPage();

        await page.goto(CRON_PAGE);
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(
            bodyText.includes('do not have sufficient permissions') || bodyText.includes('not allowed')
        ).toBe(true);

        await ctx.close();
    });

});
