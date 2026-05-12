<?php
/**
 * Template Name: Community Hub
 *
 * @package gca-intranet-foundation
 */

declare(strict_types=1);

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user        = wp_get_current_user();
$current_user_avatar = '';
if ($current_user->exists()) {
    $local               = trim((string) get_user_meta($current_user->ID, 'google_profile_picture_local_url', true));
    $current_user_avatar = $local ?: (string) get_avatar_url($current_user->ID, ['size' => 40]);
}

get_header();

if (!gca_flag_enabled('community-hub')) :
?>
<section class="gca-hero-banner" aria-label="Page banner">
    <div class="govuk-width-container">
        <div class="gca-hero-banner__inner">
            <h1 class="govuk-heading-xl gca-hero-banner__title">Community Hub</h1>
        </div>
    </div>
</section>
<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-9">
    <main id="main-content" tabindex="-1">
        <div class="govuk-notification-banner govuk-notification-banner--important" role="region" aria-labelledby="govuk-notification-banner-title" data-module="govuk-notification-banner">
            <div class="govuk-notification-banner__header">
                <h2 class="govuk-notification-banner__title" id="govuk-notification-banner-title">Feature not enabled</h2>
            </div>
            <div class="govuk-notification-banner__content">
                <p class="govuk-body">The Community Hub is not currently available.<?php if (current_user_can('manage_options')) : ?> To enable it, please contact your administrator.<?php endif; ?></p>
            </div>
        </div>
    </main>
</div>
<?php
get_footer();
exit;
endif;
?>

<section class="gca-hero-banner" aria-label="Page banner">
    <div class="govuk-width-container">
        <div class="gca-hero-banner__inner">
            <h1 class="govuk-heading-xl gca-hero-banner__title">Community Hub</h1>
        </div>
    </div>
</section>

<div class="gca-cw-page-wrap">
    <div class="govuk-width-container">
        <div class="govuk-grid-row gca-cw-layout">

            <?php /* ── Sidebar ─────────────────────────────────────────── */ ?>
            <div class="govuk-grid-column-one-quarter gca-cw-sidebar-col">
                <aside class="gca-cw-sidebar" aria-label="Community actions">
                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--question"
                        disabled
                        aria-label="Ask a question (coming soon)"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                        </svg>
                        Ask a question
                    </button>

                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--shoutout"
                        disabled
                        aria-label="Shout-out a colleague (coming soon)"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                        Shout-out a colleague
                    </button>
                </aside>
            </div>

            <?php /* ── Main feed ──────────────────────────────────────── */ ?>
            <main class="govuk-grid-column-three-quarters gca-cw-main-col" id="main-content" tabindex="-1">

                <?php /* Tabs */ ?>
                <nav class="gca-cw-tabs" aria-label="Community Hub sections">
                    <ul class="gca-cw-tabs__list" role="list">
                        <li>
                            <button type="button" class="gca-cw-tabs__btn gca-cw-tabs__btn--active" data-tab="feed" aria-pressed="true">
                                Your feed
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="shoutouts" disabled aria-pressed="false">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                                Shout-outs
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="qa" disabled aria-pressed="false">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                                </svg>
                                Questions &amp; answers
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="polls" disabled aria-pressed="false">
                                Polls
                            </button>
                        </li>
                    </ul>
                </nav>

                <?php /* Create post box */ ?>
                <div class="gca-cw-compose" id="gca-cw-compose">

                    <?php /* Collapsed trigger row */ ?>
                    <div class="gca-cw-compose__trigger-row" id="gca-cw-trigger-row">
                        <?php if ($current_user_avatar) : ?>
                            <img
                                src="<?php echo esc_url($current_user_avatar); ?>"
                                alt=""
                                width="40"
                                height="40"
                                class="gca-cw-compose__avatar"
                            >
                        <?php endif; ?>
                        <button
                            type="button"
                            class="gca-cw-compose__trigger"
                            id="gca-cw-trigger"
                            aria-expanded="false"
                            aria-controls="gca-cw-form-area"
                        >
                            Share an update with the agency&hellip;
                        </button>
                    </div>

                    <?php /* Expanded form */ ?>
                    <div class="gca-cw-compose__form-area" id="gca-cw-form-area" hidden>
                        <form class="gca-cw-compose__form" id="gca-cw-form" novalidate>

                            <div class="govuk-form-group gca-cw-compose__textarea-group">
                                <label class="govuk-label govuk-visually-hidden" for="gca-cw-content">
                                    Share an update with the agency
                                </label>
                                <textarea
                                    id="gca-cw-content"
                                    class="govuk-textarea gca-cw-compose__textarea"
                                    name="content"
                                    rows="4"
                                    placeholder="What would you like to share with the agency?"
                                    maxlength="2000"
                                    aria-describedby="gca-cw-char-hint"
                                ></textarea>
                            </div>

                            <p class="gca-cw-compose__char-hint" id="gca-cw-char-hint" aria-live="polite">
                                <span id="gca-cw-chars-left">2000</span> characters remaining
                            </p>

                            <?php /* Media previews (populated by JS) */ ?>
                            <div class="gca-cw-compose__media-preview" id="gca-cw-media-preview" hidden aria-label="Attached media"></div>

                            <div class="gca-cw-compose__footer">
                                <div class="gca-cw-compose__attach-row">
                                    <label class="gca-cw-compose__attach-btn" title="Attach photo or video">
                                        <input
                                            type="file"
                                            id="gca-cw-file-input"
                                            accept="image/*,video/*"
                                            multiple
                                            class="govuk-visually-hidden"
                                            aria-label="Attach photo or video"
                                        >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                            <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Photo / Video</span>
                                    </label>

                                    <button
                                        type="button"
                                        class="gca-cw-compose__attach-btn gca-cw-compose__attach-btn--disabled"
                                        disabled
                                        title="Polls — coming soon"
                                        aria-label="Add poll (coming soon)"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                            <line x1="18" y1="20" x2="18" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="12" y1="20" x2="12" y2="4"  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="6"  y1="20" x2="6"  y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <span>Polls</span>
                                    </button>
                                </div>

                                <div class="gca-cw-compose__actions">
                                    <button
                                        type="button"
                                        class="govuk-button govuk-button--secondary gca-cw-compose__cancel-btn"
                                        id="gca-cw-cancel"
                                    >Cancel</button>
                                    <button
                                        type="submit"
                                        class="govuk-button gca-cw-compose__submit-btn"
                                        id="gca-cw-submit"
                                    >Post update</button>
                                </div>
                            </div>

                        </form>

                        <div class="gca-cw-compose__error" id="gca-cw-error" role="alert" hidden></div>
                    </div>

                </div>
                <?php /* End create post box */ ?>

                <?php /* Feed */ ?>
                <div class="gca-cw-feed" id="gca-cw-feed" aria-label="Community feed" aria-live="polite">
                    <p class="gca-cw-feed__status" id="gca-cw-loading">
                        Loading posts&hellip;
                    </p>
                </div>

                <div class="gca-cw-feed__footer" id="gca-cw-footer" hidden>
                    <button type="button" class="gca-cw-feed__load-more" id="gca-cw-load-more">
                        Load more posts
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <p class="gca-cw-feed__end" id="gca-cw-end" hidden>
                    You&rsquo;ve reached the end of the feed.
                </p>

            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>
