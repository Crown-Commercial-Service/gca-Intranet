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

$is_community_host = function_exists('gca_cw_is_community_host') && gca_cw_is_community_host();

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

            <?php /* ── Sidebar ───────────────────────────────────────────── */ ?>
            <div class="govuk-grid-column-one-quarter gca-cw-sidebar-col">
                <aside class="gca-cw-sidebar" aria-label="Community actions">

                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--question"
                        aria-label="Ask a question"
                    >
                        Ask a question
                    </button>

                    <button
                        type="button"
                        class="gca-cw__sidebar-btn gca-cw__sidebar-btn--shoutout"
                        aria-label="Shout-out a colleague"
                    >
                        Shout-out a colleague
                    </button>

                    <?php /* Pending Q&A submissions – shown by JS when user has questions under review */ ?>
                    <div id="gca-qa-sidebar-pending" hidden>
                        <p class="gca-cw-sidebar__section-title">My questions</p>
                        <ul id="gca-qa-sidebar-pending-list" class="gca-qa-sidebar-pending__list"></ul>
                        <button type="button" class="gca-cw-sidebar__link-btn" id="gca-qa-view-all-btn">
                            View all my submissions
                        </button>
                    </div>

                </aside>
            </div>

            <?php /* ── Main content ─────────────────────────────────────── */ ?>
            <main class="govuk-grid-column-three-quarters gca-cw-main-col" id="main-content" tabindex="-1">

                <?php /* ── Tab bar ──────────────────────────────────────── */ ?>
                <nav class="gca-cw-tabs" aria-label="Community Hub sections">
                    <ul class="gca-cw-tabs__list" role="list">
                        <li>
                            <button type="button" class="gca-cw-tabs__btn gca-cw-tabs__btn--active" data-tab="feed" aria-pressed="true">
                                Your feed
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="shoutouts" aria-pressed="false">
                                Shout-outs
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="qa" aria-pressed="false">
                                Your questions answered
                            </button>
                        </li>
                        <li>
                            <button type="button" class="gca-cw-tabs__btn" data-tab="polls" aria-pressed="false">
                                Polls
                            </button>
                        </li>
                    </ul>
                </nav>

                <?php /* ═══════════════════════════
                    PANEL: Your feed
                   ═══════════════════════════ */ ?>
                <div id="gca-panel-feed">

                    <?php if ($is_community_host) : ?>
                    <div class="gca-cw-compose" id="gca-cw-compose">

                        <div class="gca-cw-compose__trigger-row" id="gca-cw-trigger-row">
                            <?php if ($current_user_avatar) : ?>
                                <img src="<?php echo esc_url($current_user_avatar); ?>" alt="" width="40" height="40" class="gca-cw-compose__avatar">
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
                                        placeholder="What would you like to share with the agency? Use @ to mention someone"
                                        maxlength="500"
                                        aria-describedby="gca-cw-char-hint gca-cw-error"
                                        aria-label="Share an update. Use @ to mention someone"
                                    ></textarea>
                                    <ul class="gca-lc__mention-list" id="gca-cw-mention-list" role="listbox" aria-label="Mention suggestions" hidden></ul>
                                </div>

                                <p class="gca-cw-compose__char-hint" id="gca-cw-char-hint" aria-live="polite">
                                    <span id="gca-cw-chars-left">500</span> characters remaining
                                </p>

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

                                        <button type="button" id="gca-cw-poll-btn" class="gca-cw-compose__attach-btn" title="Create a poll" aria-label="Switch to create poll">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                                <line x1="18" y1="20" x2="18" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="12" y1="20" x2="12" y2="4"  stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="6"  y1="20" x2="6"  y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            <span>Create poll</span>
                                        </button>
                                    </div>

                                    <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                        <button type="submit" class="gca-lc-delete-modal__confirm gca-cw-compose__submit-btn" id="gca-cw-submit">Post update</button>
                                        <button type="button" class="gca-lc-delete-modal__cancel gca-cw-compose__cancel-btn" id="gca-cw-cancel">Cancel</button>
                                    </div>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-cw-error" role="alert" tabindex="-1" hidden></div>
                        </div>

                    </div>
                    <?php endif; ?>

                    <div class="gca-cw-feed" id="gca-cw-feed" aria-label="Community feed" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-cw-loading">Loading posts&hellip;</p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-cw-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-cw-load-more">
                            Load more posts
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-cw-end" hidden>You&rsquo;ve reached the end of the feed.</p>

                </div>
                <?php /* END panel: feed */ ?>

                <?php /* ═══════════════════════════
                    PANEL: Shout-outs
                   ═══════════════════════════ */ ?>
                <div id="gca-panel-shoutouts" hidden>

                    <div class="gca-cw-compose" id="gca-shoutout-compose">
                        <div class="gca-cw-compose__trigger-row">
                            <?php if ($current_user_avatar) : ?>
                                <img src="<?php echo esc_url($current_user_avatar); ?>" alt="" width="40" height="40" class="gca-cw-compose__avatar">
                            <?php endif; ?>
                            <button type="button" class="gca-cw-compose__trigger" id="gca-shoutout-trigger" aria-expanded="false" aria-controls="gca-shoutout-form-area">
                                Shout-out a colleague&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-shoutout-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-shoutout-form" novalidate>

                                <div class="govuk-form-group">
                                    <label class="govuk-label" for="gca-shoutout-recipient-search">Who are you shouting out?</label>
                                    <div class="gca-shoutout-compose__search-wrap">
                                        <input
                                            type="text"
                                            id="gca-shoutout-recipient-search"
                                            class="govuk-input"
                                            placeholder="Search for a colleague&hellip;"
                                            autocomplete="off"
                                            role="combobox"
                                            aria-autocomplete="list"
                                            aria-expanded="false"
                                            aria-controls="gca-shoutout-suggestions"
                                            aria-describedby="gca-shoutout-error"
                                        >
                                        <input type="hidden" id="gca-shoutout-recipient-id" name="recipient_id">
                                        <ul id="gca-shoutout-suggestions" class="gca-shoutout-compose__suggestions" role="listbox" aria-label="Colleague suggestions" hidden></ul>
                                    </div>
                                </div>

                                <div class="govuk-form-group" id="gca-shoutout-category-group">
                                    <label class="govuk-label" for="gca-shoutout-category">Category</label>
                                    <select id="gca-shoutout-category" class="govuk-select" name="category_id" disabled>
                                        <option value="">Loading categories&hellip;</option>
                                    </select>
                                </div>

                                <div class="govuk-form-group gca-cw-compose__textarea-group">
                                    <label class="govuk-label" for="gca-shoutout-content">Your message</label>
                                    <textarea
                                        id="gca-shoutout-content"
                                        class="govuk-textarea gca-cw-compose__textarea"
                                        name="content"
                                        rows="3"
                                        placeholder="What would you like to celebrate?"
                                        maxlength="500"
                                        aria-describedby="gca-shoutout-char-hint gca-shoutout-error"
                                    ></textarea>
                                </div>

                                <p class="gca-cw-compose__char-hint" id="gca-shoutout-char-hint" aria-live="polite">
                                    <span id="gca-shoutout-chars-left">500</span> characters remaining
                                </p>

                                <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                    <button type="submit" class="gca-lc-delete-modal__confirm" id="gca-shoutout-submit">Post shout-out</button>
                                    <button type="button" class="gca-lc-delete-modal__cancel" id="gca-shoutout-cancel">Cancel</button>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-shoutout-error" role="alert" tabindex="-1" hidden></div>
                        </div>
                    </div>

                    <div class="gca-cw-feed" id="gca-shoutout-feed" aria-label="Shout-out feed" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-shoutout-loading">Loading shout-outs&hellip;</p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-shoutout-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-shoutout-load-more">
                            Load more shout-outs
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-shoutout-end" hidden>You&rsquo;ve reached the end.</p>

                </div>
                <?php /* END panel: shoutouts */ ?>

                <?php /* ═══════════════════════════
                    PANEL: Questions & Answers
                   ═══════════════════════════ */ ?>
                <div id="gca-panel-qa" hidden>

                    <div id="gca-qa-pending-banner" class="gca-qa-pending-banner" hidden>
                        <div class="gca-qa-pending-banner__header">
                            <p class="gca-qa-pending-banner__intro govuk-body-s">Your submitted questions — we&rsquo;ll answer them as soon as we can.</p>
                            <button type="button" class="gca-qa-pending-banner__close" id="gca-qa-pending-close" aria-label="Close my submissions">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M2 2l12 12M14 2L2 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div id="gca-qa-pending-list"></div>
                    </div>

                    <div class="gca-cw-compose" id="gca-qa-compose">
                        <div class="gca-cw-compose__trigger-row">
                            <?php if ($current_user_avatar) : ?>
                                <img src="<?php echo esc_url($current_user_avatar); ?>" alt="" width="40" height="40" class="gca-cw-compose__avatar">
                            <?php endif; ?>
                            <button type="button" class="gca-cw-compose__trigger" id="gca-qa-trigger" aria-expanded="false" aria-controls="gca-qa-form-area">
                                Ask a question&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-qa-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-qa-form" novalidate>

                                <div class="govuk-form-group gca-cw-compose__textarea-group">
                                    <label class="govuk-label govuk-visually-hidden" for="gca-qa-content">Your question</label>
                                    <textarea
                                        id="gca-qa-content"
                                        class="govuk-textarea gca-cw-compose__textarea"
                                        name="content"
                                        rows="3"
                                        placeholder="What would you like to ask?"
                                        maxlength="500"
                                        aria-describedby="gca-qa-char-hint gca-qa-error"
                                    ></textarea>
                                </div>

                                <p class="gca-cw-compose__char-hint" id="gca-qa-char-hint" aria-live="polite">
                                    <span id="gca-qa-chars-left">500</span> characters remaining
                                </p>

                                <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                    <button type="submit" class="gca-lc-delete-modal__confirm" id="gca-qa-submit">Submit question</button>
                                    <button type="button" class="gca-lc-delete-modal__cancel" id="gca-qa-cancel">Cancel</button>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-qa-error" role="alert" tabindex="-1" hidden></div>
                        </div>
                    </div>

                    <div class="gca-cw-feed" id="gca-qa-feed" aria-label="Questions and answers feed" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-qa-loading">Loading questions&hellip;</p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-qa-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-qa-load-more">
                            Load more questions
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-qa-end" hidden>You&rsquo;ve reached the end.</p>

                </div>
                <?php /* END panel: qa */ ?>

                <?php /* ═══════════════════════════
                    PANEL: Polls
                   ═══════════════════════════ */ ?>
                <div id="gca-panel-polls" hidden>

                    <?php if ($is_community_host) : ?>
                    <div class="gca-cw-compose" id="gca-poll-compose">
                        <div class="gca-cw-compose__trigger-row">
                            <?php if ($current_user_avatar) : ?>
                                <img src="<?php echo esc_url($current_user_avatar); ?>" alt="" width="40" height="40" class="gca-cw-compose__avatar">
                            <?php endif; ?>
                            <button type="button" class="gca-cw-compose__trigger" id="gca-poll-trigger" aria-expanded="false" aria-controls="gca-poll-form-area">
                                Create a poll&hellip;
                            </button>
                        </div>

                        <div class="gca-cw-compose__form-area" id="gca-poll-form-area" hidden>
                            <form class="gca-cw-compose__form" id="gca-poll-form" novalidate>

                                <div class="govuk-form-group">
                                    <label class="govuk-label" for="gca-poll-title">Poll title <span class="govuk-hint govuk-!-display-inline">(optional)</span></label>
                                    <input type="text" id="gca-poll-title" class="govuk-input" maxlength="100">
                                </div>

                                <div class="govuk-form-group">
                                    <label class="govuk-label" for="gca-poll-question">Question</label>
                                    <input type="text" id="gca-poll-question" class="govuk-input" placeholder="What would you like to ask?" maxlength="200" required aria-describedby="gca-poll-error">
                                </div>

                                <fieldset class="govuk-fieldset" aria-describedby="gca-poll-error">
                                    <legend class="govuk-fieldset__legend govuk-fieldset__legend--s">Answer options</legend>
                                    <ul id="gca-poll-options-list" class="gca-poll-compose__options-list">
                                        <li class="gca-poll-compose__option-row">
                                            <input type="text" class="govuk-input gca-poll-compose__option-input" placeholder="Option 1" maxlength="100" aria-label="Option 1">
                                        </li>
                                        <li class="gca-poll-compose__option-row">
                                            <input type="text" class="govuk-input gca-poll-compose__option-input" placeholder="Option 2" maxlength="100" aria-label="Option 2">
                                        </li>
                                    </ul>
                                    <button type="button" class="gca-poll-compose__add-option-btn" id="gca-poll-add-option">+ Add option</button>
                                </fieldset>

                                <div class="govuk-form-group govuk-!-margin-top-4">
                                    <label class="govuk-label" for="gca-poll-deadline">Close poll on <span class="govuk-hint govuk-!-display-inline">(optional)</span></label>
                                    <input type="datetime-local" id="gca-poll-deadline" class="govuk-input govuk-input--width-20">
                                </div>

                                <div class="gca-cw-compose__actions gca-lc-delete-modal__actions">
                                    <button type="submit" class="gca-lc-delete-modal__confirm" id="gca-poll-submit">Create poll</button>
                                    <button type="button" class="gca-lc-delete-modal__cancel" id="gca-poll-cancel">Cancel</button>
                                </div>

                            </form>
                            <div class="gca-cw-compose__error" id="gca-poll-error" role="alert" tabindex="-1" hidden></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="gca-cw-feed" id="gca-poll-feed" aria-label="Polls feed" aria-live="polite">
                        <p class="gca-cw-feed__status" id="gca-poll-loading">Loading polls&hellip;</p>
                    </div>

                    <div class="gca-cw-feed__footer" id="gca-poll-footer" hidden>
                        <button type="button" class="gca-cw-feed__load-more" id="gca-poll-load-more">
                            Load more polls
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <p class="gca-cw-feed__end" id="gca-poll-end" hidden>You&rsquo;ve reached the end.</p>

                </div>
                <?php /* END panel: polls */ ?>

            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>
