/**
 * CAT-1.x — Directorate / Category Permissions.
 *
 * Tests the Content Permissions feature end-to-end:
 *   - Assignment UI on the user edit screen (publisher-admin POV)
 *   - Scoped page list and direct-URL blocking (contributor POV)
 *
 * Requires WP_CONTRIBUTOR_USER, WP_PUBLISHER_USER, WP_PUBLISHER_ADMIN_USER in .env.
 * Requires at least 2 terms in the responsible_team taxonomy.
 */
import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as dotenv from 'dotenv';
import { isAccessDenied } from '../helpers/login';
import {
    createPost,
    deletePost,
    getUserId,
    setPostTerms,
    setContributorTeams,
    getResponsibleTeamTermIds,
    wpCli,
} from '../helpers/wp-cli';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });

const CONTRIBUTOR_USER     = process.env.WP_CONTRIBUTOR_USER         || '';
const PUBLISHER_USER       = process.env.WP_PUBLISHER_USER           || '';
const PUBLISHER_ADMIN_USER = process.env.WP_PUBLISHER_ADMIN_USER     || '';

// Contributor session is the default for this spec.
test.use({
    storageState: path.join(__dirname, '../.auth/contributor.json'),
});

// ─── CAT-1.1 – CAT-1.3, CAT-1.9 : Assignment UI (publisher-admin / publisher POV) ─

test.describe('CAT-1 Content Permissions — Assignment UI', () => {

    test('CAT-1.1 — Publisher admin sees Content Permissions accordion on a contributor profile', async ({ browser }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        if (!CONTRIBUTOR_USER)     test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        const contributorId = getUserId(CONTRIBUTOR_USER);
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/publisher-admin.json') });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/user-edit.php?user_id=${contributorId}`);
        await expect(page.locator('button#gca-teams-toggle')).toBeVisible();

        await ctx.close();
    });

    test('CAT-1.2 — Content Permissions accordion NOT shown on a publisher profile', async ({ browser }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        if (!PUBLISHER_USER)       test.skip(true, 'WP_PUBLISHER_USER not set');

        const publisherId = getUserId(PUBLISHER_USER);
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/publisher-admin.json') });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/user-edit.php?user_id=${publisherId}`);
        await expect(page.locator('button#gca-teams-toggle')).toHaveCount(0);

        await ctx.close();
    });

    test('CAT-1.3 — Publisher admin can assign a directorate and it persists on reload', async ({ browser }) => {
        if (!PUBLISHER_ADMIN_USER) test.skip(true, 'WP_PUBLISHER_ADMIN_USER not set');
        if (!CONTRIBUTOR_USER)     test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        let termIds: number[];
        try {
            termIds = getResponsibleTeamTermIds(1);
        } catch {
            test.skip(true, 'No responsible_team terms found');
            return;
        }
        const termId        = termIds[0];
        const contributorId = getUserId(CONTRIBUTOR_USER);
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/publisher-admin.json') });
        const page = await ctx.newPage();

        try {
            await page.goto(`/wp-admin/user-edit.php?user_id=${contributorId}`);

            // Open accordion.
            await page.click('button#gca-teams-toggle');
            await expect(page.locator('#gca-teams-body')).toBeVisible();

            // Ensure "All" is unchecked so individual boxes are enabled.
            const allBox = page.locator('input#gca_team_all');
            if (await allBox.isChecked()) {
                await allBox.uncheck();
            }

            // Check the target term.
            const termBox = page.locator(`input.gca-team-checkbox[value="${termId}"]`);
            await termBox.check();

            await page.click('#submit');
            await page.waitForLoadState('networkidle');

            // Reload and confirm checkbox is still checked.
            await page.goto(`/wp-admin/user-edit.php?user_id=${contributorId}`);
            await page.click('button#gca-teams-toggle');
            await expect(page.locator(`input.gca-team-checkbox[value="${termId}"]`)).toBeChecked();
            await expect(page.locator('#gca-teams-summary')).toContainText('1 selected');
        } finally {
            setContributorTeams(CONTRIBUTOR_USER, 'all');
            await ctx.close();
        }
    });

    test('CAT-1.9 — Publisher (not admin) also sees Content Permissions accordion', async ({ browser }) => {
        if (!PUBLISHER_USER)   test.skip(true, 'WP_PUBLISHER_USER not set');
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        const contributorId = getUserId(CONTRIBUTOR_USER);
        const ctx  = await browser.newContext({ storageState: path.join(__dirname, '../.auth/publisher.json') });
        const page = await ctx.newPage();

        await page.goto(`/wp-admin/user-edit.php?user_id=${contributorId}`);
        await expect(page.locator('button#gca-teams-toggle')).toBeVisible();

        await ctx.close();
    });

});

// ─── CAT-1.4 – CAT-1.8 : Scoped access (contributor POV) ────────────────────

test.describe('CAT-1 Content Permissions — Scoped access', () => {

    let inScopeTermId:    number;
    let outOfScopeTermId: number;
    let inScopePageId:    number;   // contributor-authored, in-scope term
    let sharedPageId:     number;   // admin-authored, in-scope term
    let outOfScopePageId: number;   // admin-authored, out-of-scope term

    test.beforeAll(async () => {
        if (!CONTRIBUTOR_USER) return;

        const termIds = getResponsibleTeamTermIds(2);
        inScopeTermId    = termIds[0];
        outOfScopeTermId = termIds[1];

        setContributorTeams(CONTRIBUTOR_USER, [inScopeTermId]);

        inScopePageId    = createPost('CAT-1 In-Scope Page',        'draft', 'page', CONTRIBUTOR_USER);
        sharedPageId     = createPost('CAT-1 Shared In-Scope Page', 'draft', 'page');
        outOfScopePageId = createPost('CAT-1 Out-of-Scope Page',    'draft', 'page');

        setPostTerms(inScopePageId,    [inScopeTermId],    'responsible_team');
        setPostTerms(sharedPageId,     [inScopeTermId],    'responsible_team');
        setPostTerms(outOfScopePageId, [outOfScopeTermId], 'responsible_team');
    });

    test.afterAll(async () => {
        if (inScopePageId)    deletePost(inScopePageId);
        if (sharedPageId)     deletePost(sharedPageId);
        if (outOfScopePageId) deletePost(outOfScopePageId);
        if (CONTRIBUTOR_USER) setContributorTeams(CONTRIBUTOR_USER, 'all');
    });

    test('CAT-1.4 — Contributor only sees pages from their assigned directorate', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        await page.goto('/wp-admin/edit.php?post_type=page');
        // Use td.column-title so we capture both editable (a.row-title) and
        // non-editable (plain <strong>) titles — contributors can't edit
        // pages they didn't author, so those render without a link.
        const titles = await page.locator('table.wp-list-table tbody tr td.column-title').allTextContents();

        expect(titles.some(t => t.includes('CAT-1 In-Scope Page'))).toBe(true);
        expect(titles.some(t => t.includes('CAT-1 Out-of-Scope Page'))).toBe(false);
    });

    test('CAT-1.5 — In-scope page not authored by contributor still appears in All view', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        await page.goto('/wp-admin/edit.php?post_type=page');
        const titles = await page.locator('table.wp-list-table tbody tr td.column-title').allTextContents();

        expect(titles.some(t => t.includes('CAT-1 Shared In-Scope Page'))).toBe(true);
    });

    test('CAT-1.6 — View filter counts reflect directorate scope, not site totals', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        await page.goto('/wp-admin/edit.php?post_type=page');

        // Grab the "All" view count displayed to the contributor.
        const allLink     = page.locator('.subsubsub li a').filter({ hasText: /^All/ }).first();
        const allText     = (await allLink.textContent()) ?? '';
        const match       = allText.match(/\((\d[\d,]*)\)/);
        expect(match).not.toBeNull();
        const shownCount  = parseInt((match![1]).replace(',', ''), 10);

        // Get the unfiltered total via WP-CLI (what a publisher sees).
        const totalCount  = parseInt(
            wpCli('post list --post_type=page --post_status=publish,draft,pending,future,private --format=count'),
            10,
        );

        // Contributor's count must be less than the site total, confirming filtering is active.
        expect(shownCount).toBeGreaterThanOrEqual(2); // we created 2 in-scope pages
        expect(shownCount).toBeLessThan(totalCount);
    });

    test('CAT-1.7 — Contributor cannot access edit URL for an out-of-scope page', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        await page.goto(`/wp-admin/post.php?post=${outOfScopePageId}&action=edit`);
        const bodyText = (await page.locator('body').textContent()) ?? '';
        expect(isAccessDenied(bodyText)).toBe(true);
    });

    test('CAT-1.8 — Contributor with "All directorates/teams" sees all pages', async ({ page }) => {
        if (!CONTRIBUTOR_USER) test.skip(true, 'WP_CONTRIBUTOR_USER not set');

        setContributorTeams(CONTRIBUTOR_USER, 'all');

        await page.goto('/wp-admin/edit.php?post_type=page');
        const titles = await page.locator('table.wp-list-table tbody tr td.column-title').allTextContents();

        expect(titles.some(t => t.includes('CAT-1 In-Scope Page'))).toBe(true);
        expect(titles.some(t => t.includes('CAT-1 Out-of-Scope Page'))).toBe(true);

        // Restore scoped state for any remaining tests.
        setContributorTeams(CONTRIBUTOR_USER, [inScopeTermId]);
    });

});
