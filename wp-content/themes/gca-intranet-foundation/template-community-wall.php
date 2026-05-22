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
                        aria-label="Ask a question"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                        </svg>
                        Ask a question
                    </button>

                    <?php if (gca_flag_enabled('community-shoutouts')) : ?>
                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--shoutout"
                        aria-label="Shout-out a colleague"
                    >
                    <?php else : ?>
                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--shoutout"
                        disabled
                        aria-label="Shout-out a colleague (coming soon)"
                    >
                    <?php endif; ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                        Shout-out a colleague
                    </button>

                    <?php if (gca_flag_enabled('community-qa')) : ?>
                    <div class="gca-qa-sidebar-pending" id="gca-qa-sidebar-pending" hidden>
                        <p class="gca-qa-sidebar-pending__title">My Pending Questions</p>
                        <ul class="gca-qa-sidebar-pending__list" id="gca-qa-sidebar-pending-list"></ul>
                        <button type="button" class="gca-qa-sidebar-pending__view-all" id="gca-qa-view-all-btn">
                            View all my submissions &rarr;
                        </button>
                    </div>
                    <?php endif; ?>

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
                            <?php if (gca_flag_enabled('community-shoutouts')) : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="shoutouts" aria-pressed="false">
                        <?php else : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="shoutouts" disabled aria-pressed="false">
                        <?php endif; ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                                Shout-outs
                            </button>
                        </li>
                        <li>
                            <?php if (gca_flag_enabled('community-qa')) : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="qa" aria-pressed="false">
                            <?php else : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="qa" disabled aria-pressed="false">
                            <?php endif; ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                                </svg>
                                Questions &amp; answers
                            </button>
                        </li>
                        <li>
                            <?php if (gca_flag_enabled('community-polls')) : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="polls" aria-pressed="false">
                            <?php else : ?>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="polls" disabled aria-pressed="false">
                            <?php endif; ?>
                                Polls
                            </button>
                        </li>
                    </ul>
                </nav>

                <?php /* ── Feed panel ──────────────────────────────────── */ ?>
                <div id="gca-panel-feed" data-panel="feed">

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

                                    <?php if (gca_flag_enabled('community-polls')) : ?>
                                    <button
                                        type="button"
                                        id="gca-cw-poll-btn"
                                        class="gca-cw-compose__attach-btn"
                                        title="Create a poll"
                                        aria-label="Create a poll"
                                    >
                                    <?php else : ?>
                                    <button
                                        type="button"
                                        class="gca-cw-compose__attach-btn gca-cw-compose__attach-btn--disabled"
                                        disabled
                                        title="Polls — coming soon"
                                        aria-label="Add poll (coming soon)"
                                    >
                                    <?php endif; ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                            <line x1="18" y1="20" x2="18" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="12" y1="20" x2="12" y2="4"  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <line x1="6"  y1="20" x2="6"  y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <span>Polls</span>
                                    </button>
                                </div>

                                <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                    <button
                                        type="submit"
                                        class="gca-lc-delete-modal__confirm gca-cw-compose__submit-btn"
                                        id="gca-cw-submit"
                                    >Post update</button>
                                    <button
                                        type="button"
                                        class="gca-lc-delete-modal__cancel gca-cw-compose__cancel-btn"
                                        id="gca-cw-cancel"
                                    >Cancel</button>
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

                </div><?php /* end #gca-panel-feed */ ?>

                <?php if (gca_flag_enabled('community-qa')) : ?>
                <?php /* ── Q&A panel ────────────────────────────────────── */ ?>
                <div id="gca-panel-qa" data-panel="qa" hidden>

                    <?php /* Ask question compose */ ?>
                    <div class="gca-cw-compose" id="gca-qa-compose">

                        <div class="gca-cw-compose__trigger-row" id="gca-qa-trigger-row">
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
                                id="gca-qa-trigger"
                                aria-expanded="false"
                                aria-controls="gca-qa-form-area"
                            >
                                Ask a question&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-qa-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-qa-form" novalidate>

                                <div class="govuk-form-group gca-cw-compose__textarea-group">
                                    <label class="govuk-label govuk-visually-hidden" for="gca-qa-content">
                                        Ask a question
                                    </label>
                                    <textarea
                                        id="gca-qa-content"
                                        class="govuk-textarea gca-cw-compose__textarea"
                                        name="content"
                                        rows="4"
                                        placeholder="What would you like to ask?"
                                        maxlength="1000"
                                        aria-describedby="gca-qa-char-hint"
                                    ></textarea>
                                </div>

                                <p class="gca-cw-compose__char-hint" id="gca-qa-char-hint" aria-live="polite">
                                    <span id="gca-qa-chars-left">1000</span> characters remaining
                                </p>

                                <div class="gca-cw-compose__footer">
                                    <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                        <button
                                            type="submit"
                                            class="gca-lc-delete-modal__confirm gca-cw-compose__submit-btn"
                                            id="gca-qa-submit"
                                        >Submit question</button>
                                        <button
                                            type="button"
                                            class="gca-lc-delete-modal__cancel gca-cw-compose__cancel-btn"
                                            id="gca-qa-cancel"
                                        >Cancel</button>
                                    </div>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-qa-error" role="alert" hidden></div>
                        </div>

                    </div><?php /* end gca-qa-compose */ ?>

                    <?php /* User's pending questions banner */ ?>
                    <div class="gca-qa-pending-banner" id="gca-qa-pending-banner" hidden>
                        <p class="gca-qa-pending-banner__title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            My pending questions
                        </p>
                        <div class="gca-qa-pending-list" id="gca-qa-pending-list"></div>
                    </div>

                    <?php /* Answered Q&A feed */ ?>
                    <div class="gca-qa-feed" id="gca-qa-feed" aria-label="Questions and answers" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-qa-loading">
                            Loading questions&hellip;
                        </p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-qa-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-qa-load-more">
                            Load more questions
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-qa-end" hidden>
                        You&rsquo;ve seen all answered questions.
                    </p>

                </div><?php /* end #gca-panel-qa */ ?>
                <?php endif; ?>

                <?php if (gca_flag_enabled('community-polls')) : ?>
                <?php /* ── Polls panel ──────────────────────────────────── */ ?>
                <div id="gca-panel-polls" data-panel="polls" hidden>

                    <?php /* Poll compose */ ?>
                    <div class="gca-cw-compose" id="gca-poll-compose">

                        <div class="gca-cw-compose__trigger-row" id="gca-poll-trigger-row">
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
                                id="gca-poll-trigger"
                                aria-expanded="false"
                                aria-controls="gca-poll-form-area"
                            >
                                Create a poll&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-poll-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-poll-form" novalidate>

                                <div class="govuk-form-group">
                                    <label class="govuk-label" for="gca-poll-title">
                                        Title <span class="govuk-hint" style="display:inline;font-weight:400">(optional)</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="gca-poll-title"
                                        class="govuk-input"
                                        name="poll_title"
                                        placeholder="e.g. Q3 All-Staff Survey"
                                        maxlength="150"
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="govuk-form-group">
                                    <label class="govuk-label" for="gca-poll-team">
                                        Team <span class="govuk-hint" style="display:inline;font-weight:400">(optional)</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="gca-poll-team"
                                        class="govuk-input"
                                        name="poll_team"
                                        placeholder="e.g. Corporate Communications"
                                        maxlength="100"
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="govuk-form-group gca-cw-compose__textarea-group">
                                    <label class="govuk-label" for="gca-poll-question">
                                        Question
                                    </label>
                                    <input
                                        type="text"
                                        id="gca-poll-question"
                                        class="govuk-input gca-poll-compose__question"
                                        name="question"
                                        placeholder="Ask a question…"
                                        maxlength="500"
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="govuk-form-group">
                                    <label class="govuk-label gca-poll-compose__options-label">
                                        Options <span class="govuk-hint" style="display:inline;font-weight:400">(minimum 2)</span>
                                    </label>
                                    <div class="gca-poll-compose__options-list" id="gca-poll-options-list">
                                        <div class="gca-poll-compose__option-row">
                                            <input type="text" class="govuk-input gca-poll-compose__option-input" placeholder="Option 1" maxlength="100" aria-label="Option 1">
                                        </div>
                                        <div class="gca-poll-compose__option-row">
                                            <input type="text" class="govuk-input gca-poll-compose__option-input" placeholder="Option 2" maxlength="100" aria-label="Option 2">
                                        </div>
                                    </div>
                                    <button type="button" class="gca-poll-compose__add-option" id="gca-poll-add-option">
                                        + Add another option
                                    </button>
                                </div>

                                <div class="gca-poll-compose__deadline govuk-form-group">
                                    <label class="govuk-label gca-poll-compose__deadline-label" for="gca-poll-deadline">
                                        Deadline
                                    </label>
                                    <span class="govuk-hint gca-poll-compose__deadline-hint">
                                        Optional. Voting closes at this date and time.
                                    </span>
                                    <input
                                        type="datetime-local"
                                        id="gca-poll-deadline"
                                        class="govuk-input"
                                        name="deadline"
                                        style="max-width:260px"
                                    >
                                </div>

                                <div class="gca-cw-compose__footer">
                                    <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                        <button
                                            type="submit"
                                            class="gca-lc-delete-modal__confirm gca-cw-compose__submit-btn"
                                            id="gca-poll-submit"
                                        >Create poll</button>
                                        <button
                                            type="button"
                                            class="gca-lc-delete-modal__cancel gca-cw-compose__cancel-btn"
                                            id="gca-poll-cancel"
                                        >Cancel</button>
                                    </div>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-poll-error" role="alert" hidden></div>
                        </div>

                    </div><?php /* end gca-poll-compose */ ?>

                    <?php /* Polls feed */ ?>
                    <div class="gca-cw-feed" id="gca-poll-feed" aria-label="Community polls" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-poll-loading">
                            Loading polls&hellip;
                        </p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-poll-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-poll-load-more">
                            Load more polls
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-poll-end" hidden>
                        You&rsquo;ve seen all polls.
                    </p>

                </div><?php /* end #gca-panel-polls */ ?>
                <?php endif; ?>

                <?php if (gca_flag_enabled('community-shoutouts')) : ?>
                <?php /* ── Shout-outs panel ──────────────────────────────── */ ?>
                <div id="gca-panel-shoutouts" data-panel="shoutouts" hidden>

                    <?php /* Shout-out compose */ ?>
                    <div class="gca-cw-compose" id="gca-shoutout-compose">

                        <div class="gca-cw-compose__trigger-row" id="gca-shoutout-trigger-row">
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
                                id="gca-shoutout-trigger"
                                aria-expanded="false"
                                aria-controls="gca-shoutout-form-area"
                            >
                                Shout out a colleague&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-shoutout-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-shoutout-form" novalidate>

                                <div class="govuk-form-group gca-shoutout-compose__recipient-wrap">
                                    <label class="govuk-label" for="gca-shoutout-recipient-search">
                                        Who are you shouting out?
                                    </label>
                                    <input
                                        type="text"
                                        id="gca-shoutout-recipient-search"
                                        class="govuk-input"
                                        placeholder="Search by name&hellip;"
                                        autocomplete="off"
                                        aria-autocomplete="list"
                                        aria-expanded="false"
                                        aria-controls="gca-shoutout-suggestions"
                                    >
                                    <input type="hidden" id="gca-shoutout-recipient-id" name="recipient_id">
                                    <ul
                                        id="gca-shoutout-suggestions"
                                        role="listbox"
                                        aria-label="Colleague suggestions"
                                        hidden
                                    ></ul>
                                </div>

                                <div class="govuk-form-group gca-cw-compose__textarea-group">
                                    <label class="govuk-label" for="gca-shoutout-content">
                                        Your message
                                    </label>
                                    <textarea
                                        id="gca-shoutout-content"
                                        class="govuk-textarea gca-cw-compose__textarea"
                                        name="content"
                                        rows="4"
                                        placeholder="Tell everyone what they did that deserves a shout-out&hellip;"
                                        maxlength="1000"
                                        aria-describedby="gca-shoutout-char-hint"
                                    ></textarea>
                                </div>

                                <p class="gca-cw-compose__char-hint" id="gca-shoutout-char-hint" aria-live="polite">
                                    <span id="gca-shoutout-chars-left">1000</span> characters remaining
                                </p>

                                <div class="gca-cw-compose__footer">
                                    <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                        <button
                                            type="submit"
                                            class="gca-lc-delete-modal__confirm gca-cw-compose__submit-btn"
                                            id="gca-shoutout-submit"
                                        >Post shout-out</button>
                                        <button
                                            type="button"
                                            class="gca-lc-delete-modal__cancel gca-cw-compose__cancel-btn"
                                            id="gca-shoutout-cancel"
                                        >Cancel</button>
                                    </div>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-shoutout-error" role="alert" hidden></div>
                        </div>

                    </div><?php /* end gca-shoutout-compose */ ?>

                    <?php /* Shout-outs feed */ ?>
                    <div class="gca-cw-feed" id="gca-shoutout-feed" aria-label="Community shout-outs" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-shoutout-loading">
                            Loading shout-outs&hellip;
                        </p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-shoutout-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-shoutout-load-more">
                            Load more shout-outs
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-shoutout-end" hidden>
                        You&rsquo;ve seen all shout-outs.
                    </p>

                </div><?php /* end #gca-panel-shoutouts */ ?>
                <?php endif; ?>

            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>
