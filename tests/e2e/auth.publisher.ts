import { test as setup } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import * as dotenv from 'dotenv';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const authFile = path.join(__dirname, '../.auth/publisher.json');

setup('authenticate as publisher', async ({ page }) => {
    const user = process.env.WP_PUBLISHER_USER     || '';
    const pass = process.env.WP_PUBLISHER_PASSWORD || '';

    if (!user || !pass) {
        console.warn('WP_PUBLISHER_USER / WP_PUBLISHER_PASSWORD not set — skipping publisher auth.');
        fs.mkdirSync(path.dirname(authFile), { recursive: true });
        fs.writeFileSync(authFile, JSON.stringify({ cookies: [], origins: [] }));
        return;
    }

    const base = (process.env.WP_HOME || 'http://localhost:8090').replace(/\/$/, '');
    fs.mkdirSync(path.dirname(authFile), { recursive: true });

    // Re-use an existing valid session rather than re-authenticating each run.
    if (fs.existsSync(authFile)) {
        const state = JSON.parse(fs.readFileSync(authFile, 'utf8'));
        if (state.cookies?.length > 0) {
            console.log('Re-using existing publisher session.');
            return;
        }
    }

    await page.goto(`${base}/?gcawebadmin`);
    await page.fill('#user_login', user);
    await page.fill('#user_pass', pass);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    if (!page.url().includes('/wp-admin')) {
        throw new Error(`Login failed — landed on ${page.url()}`);
    }
    await page.context().storageState({ path: authFile });
});
