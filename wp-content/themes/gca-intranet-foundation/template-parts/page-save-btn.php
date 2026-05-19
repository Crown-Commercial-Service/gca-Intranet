<?php
/**
 * Standalone save button for pages.
 * Rendered as a minimal .gca-lc wrapper so the shared interactions JS can init it.
 * Only shown to logged-in users.
 */
if (!is_user_logged_in()) {
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
                <path class="gca-lc__save-path" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="gca-lc__save-label">Save</span>
    </button>
    <span aria-live="polite" class="govuk-visually-hidden"></span>
</div>
