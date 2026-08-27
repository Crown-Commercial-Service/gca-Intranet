<?php
/**
 * Standalone save button for pages.
 * Rendered as a minimal .gca-lc wrapper so the shared interactions JS can init it.
 * Only shown to logged-in users.
 */
if (!is_user_logged_in() || !gca_flag_enabled('post-saves')) {
    return;
}
?>
<div class="gca-lc gca-lc--page-save" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
    <button
        type="button"
        class="gca-lc__save-btn"
        aria-pressed="false"
        aria-label="Save page"
        data-action="toggle-post-save"
    >
        <span class="gca-lc__save-icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                <path class="gca-lc__save-path" d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="gca-lc__save-label">Save this item</span>
    </button>
    <span aria-live="polite" class="govuk-visually-hidden"></span>
</div>
