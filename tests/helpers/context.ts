import { Browser, BrowserContext, BrowserContextOptions, Page, TestInfo } from '@playwright/test';

/**
 * Playwright's `video: 'on'` config only auto-records the fixture-provided
 * page/context (the ones injected as test args). Contexts created manually via
 * browser.newContext() — which is most tests in this suite, since they need
 * multiple concurrent logins — are invisible to that setting and need video
 * recording wired up explicitly. Use this instead of a bare browser.newContext().
 */
export async function newRecordedContext(
    browser: Browser,
    testInfo: TestInfo,
    options: BrowserContextOptions = {},
): Promise<{ ctx: BrowserContext; page: Page }> {
    const ctx  = await browser.newContext({ ...options, recordVideo: { dir: testInfo.outputDir } });
    const page = await ctx.newPage();
    return { ctx, page };
}

/**
 * Closes a manually-created context and attaches its video to the test report.
 * The video only finalizes once the context is closed, so path() must be
 * awaited after close(), not before.
 */
export async function closeRecordedContext(ctx: BrowserContext, page: Page, testInfo: TestInfo): Promise<void> {
    const video = page.video();
    await ctx.close();
    if (! video) {
        return;
    }
    try {
        const videoPath = await video.path();
        await testInfo.attach('video', { path: videoPath, contentType: 'video/webm' });
    } catch {
        // Video may not be available in all environments — non-fatal.
    }
}
