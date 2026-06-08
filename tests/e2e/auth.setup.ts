import { test as setup } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import * as dotenv from 'dotenv';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const authFile = path.join(__dirname, '../.auth/admin.json');

setup('authenticate as admin', async ({ page }) => {
    const user = process.env.WP_ADMIN_USER     || 'admin';
    const pass = process.env.WP_ADMIN_PASSWORD || '';
    const base = (process.env.WP_HOME || 'http://localhost:8090').replace(/\/$/, '');

    fs.mkdirSync(path.dirname(authFile), { recursive: true });

    // Re-use an existing valid session rather than re-authenticating each run.
    if (fs.existsSync(authFile)) {
        const state = JSON.parse(fs.readFileSync(authFile, 'utf8'));
        if (state.cookies?.length > 0) {
            console.log('Re-using existing admin session.');
            return;
        }
    }

    // The site uses Google SSO. The backdoor login form is triggered by
    // any URL containing 'gcawebadmin', which fires wp-login.php via the
    // theme's init hook on a regular front-end request.
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
