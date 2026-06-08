import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';

dotenv.config({ path: path.resolve(__dirname, '../.env') });

export const BASE_URL   = process.env.WP_HOME          || 'http://localhost:8090';
export const ADMIN_USER = process.env.WP_ADMIN_USER     || 'admin';
export const ADMIN_PASS = process.env.WP_ADMIN_PASSWORD || '';

export const CRON_PAGE = `${BASE_URL}/wp-admin/tools.php?page=gca-cron-manager`;

export default defineConfig({
    testDir: './e2e',

    // Keep serial: each test mutates shared WP DB state.
    fullyParallel: false,
    workers: 1,

    timeout: 60_000,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,

    reporter: [['html', { open: 'never' }], ['list']],

    use: {
        baseURL: BASE_URL,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },

    projects: [
        // ── Auth setup ──────────────────────────────────────────────────────
        {
            name: 'setup',
            testMatch: /auth\.setup\.ts/,
        },
        {
            name: 'setup-contributor',
            testMatch: /auth\.contributor\.ts/,
        },
        {
            name: 'setup-publisher',
            testMatch: /auth\.publisher\.ts/,
        },
        {
            name: 'setup-publisher-admin',
            testMatch: /auth\.publisher-admin\.ts/,
        },

        // ── Test runs ────────────────────────────────────────────────────────
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                storageState: '.auth/admin.json',
            },
            dependencies: ['setup', 'setup-publisher'],
            testIgnore: /auth\.(setup|contributor|publisher|publisher-admin)\.ts|permissions-category\.spec\.ts/,
        },
        {
            name: 'chromium-contributor',
            use: {
                ...devices['Desktop Chrome'],
                storageState: '.auth/contributor.json',
            },
            dependencies: ['setup-contributor'],
            testMatch: /permissions-contributor\.spec\.ts/,
        },
        {
            name: 'chromium-publisher',
            use: {
                ...devices['Desktop Chrome'],
                storageState: '.auth/publisher.json',
            },
            dependencies: ['setup-publisher'],
            testMatch: /permissions-publisher\.spec\.ts/,
        },
        {
            name: 'chromium-publisher-admin',
            use: {
                ...devices['Desktop Chrome'],
                storageState: '.auth/publisher-admin.json',
            },
            dependencies: ['setup-publisher-admin'],
            testMatch: /permissions-publisher-admin\.spec\.ts/,
        },
        {
            name: 'chromium-category',
            use: {
                ...devices['Desktop Chrome'],
                storageState: '.auth/contributor.json',
            },
            dependencies: ['setup-contributor', 'setup-publisher-admin', 'setup-publisher'],
            testMatch: /permissions-category\.spec\.ts/,
        },
    ],
});
