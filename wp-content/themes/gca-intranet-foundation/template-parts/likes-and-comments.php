<?php
/**
 * Likes & Comments component.
 * Rendered on single blog, news, and work_update posts.
 * All interaction is handled client-side via the GCA REST API.
 */

if (!is_user_logged_in() || !in_the_loop()) {
    return;
}

$post_id = get_the_ID();
?>

<section
    class="gca-lc"
    data-post-id="<?php echo esc_attr((string) $post_id); ?>"
    aria-label="Likes and comments"
>
    <?php /* Accessible live region – JS writes status messages here */ ?>
    <div
        class="govuk-visually-hidden"
        aria-live="polite"
        aria-atomic="true"
        id="gca-lc-status-<?php echo esc_attr((string) $post_id); ?>"
    ></div>

    <?php /* ── Like bar ─────────────────────────────────────────────────── */ ?>
    <div class="gca-lc__like-bar">
        <button
            type="button"
            class="gca-lc__like-btn"
            aria-pressed="false"
            data-action="toggle-post-like"
        >
            <span class="gca-lc__like-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path class="gca-lc__like-path" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                         2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3
                         19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                          stroke="currentColor" stroke-width="2"/>
                </svg>
            </span>
            <span class="gca-lc__like-label">Like</span>
            <span class="gca-lc__like-count" aria-label="likes">0</span>
        </button>

        <button
            type="button"
            class="gca-lc__comment-toggle-btn"
            aria-expanded="false"
            aria-controls="gca-lc-comments-<?php echo esc_attr((string) $post_id); ?>"
            data-action="toggle-comments"
        >
            <span class="gca-lc__comment-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="gca-lc__comment-count-label"><span class="gca-lc__comment-count">0</span> comments</span>
        </button>
    </div>

    <?php /* ── Comments panel ───────────────────────────────────────────── */ ?>
    <div
        class="gca-lc__comments-panel"
        id="gca-lc-comments-<?php echo esc_attr((string) $post_id); ?>"
        hidden
    >
        <?php /* Comment form */ ?>
        <div class="gca-lc__form-wrap">
            <div class="gca-lc__form-avatar">
                <?php echo get_avatar(get_current_user_id(), 40, '', '', ['class' => 'gca-lc__avatar']); ?>
            </div>
            <form class="gca-lc__form" data-action="submit-comment" novalidate>
                <input type="hidden" name="parent_id" value="0">
                <div class="govuk-form-group gca-lc__textarea-group">
                    <label class="govuk-label govuk-visually-hidden" for="gca-lc-textarea-<?php echo esc_attr((string) $post_id); ?>">
                        Add a comment
                    </label>
                    <textarea
                        id="gca-lc-textarea-<?php echo esc_attr((string) $post_id); ?>"
                        class="govuk-textarea gca-lc__textarea"
                        name="content"
                        rows="3"
                        placeholder="Add a comment… Use @ to mention someone"
                        aria-label="Add a comment. Use @ to mention someone"
                        maxlength="2000"
                    ></textarea>
                    <?php /* @mention dropdown */ ?>
                    <ul class="gca-lc__mention-list" role="listbox" aria-label="Mention suggestions" hidden></ul>
                </div>
                <div class="gca-lc__form-actions">
                    <button type="submit" class="govuk-button govuk-button--secondary gca-lc__submit-btn">
                        Post comment
                    </button>
                </div>
            </form>
        </div>

        <?php /* Comments list – populated by JS */ ?>
        <div class="gca-lc__list" aria-live="polite" aria-relevant="additions" role="log">
            <p class="gca-lc__loading govuk-body-s">Loading comments…</p>
        </div>
    </div>
</section>
