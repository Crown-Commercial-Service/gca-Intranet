<?php
/**
 * Likes & Comments component.
 * Rendered on single blog, news, work_update, and event posts.
 * All interaction is handled client-side via the GCA REST API.
 */

$post_id = isset($args['post_id']) ? absint($args['post_id']) : get_the_ID();

if (!is_user_logged_in() || $post_id <= 0) {
    return;
}
?>

<section
    class="gca-lc"
    data-post-id="<?php echo esc_attr((string) $post_id); ?>"
    aria-label="Likes and comments for: <?php echo esc_attr(get_the_title($post_id)); ?>"
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
        <?php if (get_post_meta($post_id, '_gca_hide_likes_and_comments', true) ? '0' : '1') : ?>
        <button
            type="button"
            class="gca-lc__like-btn"
            aria-pressed="false"
            data-action="toggle-post-like"
        >
            <span class="gca-lc__like-icon" aria-hidden="true">
                <svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path class="gca-lc__like-path" d="M22 9C22 7.89 21.1 7 20 7H13.68L14.64 2.43C14.66 2.33 14.67 2.22 14.67 2.11C14.67 1.7 14.5 1.32 14.23 1.05L13.17 0L6.59 6.58C6.22 6.95 6 7.45 6 8V18C6 18.5304 6.21071 19.0391 6.58579 19.4142C6.96086 19.7893 7.46957 20 8 20H17C17.83 20 18.54 19.5 18.84 18.78L21.86 11.73C21.95 11.5 22 11.26 22 11V9ZM0 20H4V8H0V20Z" fill="currentColor"/>
                </svg>
            </span>
            <span class="gca-lc__like-label">Like</span>
            <span class="gca-lc__like-count" aria-label="likes">0</span>
        </button>
        <?php endif; ?>

        <?php if (get_post_meta($post_id, '_gca_hide_comments', true)||get_post_meta($post_id, '_gca_hide_likes_and_comments', true) ? '0' : '1') : ?>
        <button
            type="button"
            class="gca-lc__comment-toggle-btn"
            aria-expanded="true"
            aria-controls="gca-lc-comments-<?php echo esc_attr((string) $post_id); ?>"
            data-action="toggle-comments"
        >
            <span class="gca-lc__comment-icon" aria-hidden="true">
                <svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path d="M11 0C4.92422 0 0 4.15625 0 9.28571C0 11.5 0.919531 13.5268 2.44922 15.1205C1.91211 17.3705 0.116016 19.375 0.0945313 19.3973C0 19.5 -0.0257812 19.6518 0.0300781 19.7857C0.0859375 19.9196 0.20625 20 0.34375 20C3.19258 20 5.32813 18.5804 6.38516 17.7054C7.79023 18.2545 9.35 18.5714 11 18.5714C17.0758 18.5714 22 14.4152 22 9.28571C22 4.15625 17.0758 0 11 0Z" fill="currentColor"/>
                </svg>
            </span>
            <span class="gca-lc__comment-count-label"><span class="gca-lc__comment-count">0</span> comments</span>
        </button>
        <?php endif; ?>

        <?php if (gca_flag_enabled('post-saves')) : ?>
        <button
            type="button"
            class="gca-lc__save-btn"
            aria-pressed="false"
            aria-label="Save post"
            data-action="toggle-post-save"
        >
            <span class="gca-lc__save-icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                    <path class="gca-lc__save-path" d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </button>
        <?php endif; ?>
    </div>

    <?php /* ── Comments panel ───────────────────────────────────────────── */ ?>
    <?php if (get_post_meta($post_id, '_gca_hide_comments', true)||get_post_meta($post_id, '_gca_hide_likes_and_comments', true) ? '0' : '1') : ?>
    <div
        class="gca-lc__comments-panel"
        id="gca-lc-comments-<?php echo esc_attr((string) $post_id); ?>"
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
    <?php endif; ?>
</section>
