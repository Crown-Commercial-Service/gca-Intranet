import { Browser, BrowserContext, Page } from '@playwright/test';

/**
 * Create a new browser context authenticated as the given user.
 * Uses the backdoor login form (/?gcawebadmin) that bypasses Google SSO.
 */
export async function loginAs(
    browser: Browser,
    username: string,
    password: string,
): Promise<BrowserContext> {
    const ctx  = await browser.newContext({ storageState: undefined });
    const page = await ctx.newPage();

    await page.goto('/?gcawebadmin');
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    if (!page.url().includes('/wp-admin')) {
        throw new Error(`loginAs(${username}): login failed — landed on ${page.url()}`);
    }
    await page.close();

    return ctx;
}

/**
 * Navigate to a URL and return the body text — useful for checking
 * permission-denied responses without needing a full page parse.
 */
export async function getBodyText(page: Page, url: string): Promise<string> {
    await page.goto(url);
    return (await page.locator('body').textContent()) ?? '';
}

export function isAccessDenied(text: string): boolean {
    return (
        text.includes('do not have sufficient permissions') ||
        text.includes('not allowed') ||
        text.includes('Sorry, you are not allowed')
    );
}
