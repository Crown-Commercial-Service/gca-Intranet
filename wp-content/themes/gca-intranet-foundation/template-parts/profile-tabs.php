<?php
/**
 * Profile tabs — My Saves, Shout-outs, Mentions, My Posts.
 * My Saves / Mentions / My Posts only rendered when the logged-in user is viewing their own profile.
 * Shout-outs tab is visible to all logged-in users on any profile.
 *
 * Args:
 *   rest_url        (string) – base REST URL, e.g. /wp-json/gca/v1
 *   nonce           (string) – WP REST nonce
 *   is_own_profile  (bool)   – true when logged-in user is viewing their own profile
 *   profile_user_id (int)    – WP user ID of the profile being viewed
 */

$rest_url        = esc_js($args['rest_url']         ?? '');
$nonce           = esc_js($args['nonce']            ?? '');
$is_own_profile  = (bool) ($args['is_own_profile']  ?? false);
$profile_user_id = (int)  ($args['profile_user_id'] ?? 0);
$show_saves_tabs     = $is_own_profile && function_exists('gca_flag_enabled') && gca_flag_enabled('post-saves');
$show_shoutouts      = function_exists('gca_flag_enabled') && gca_flag_enabled('community-shoutouts');
$show_notifications  = $is_own_profile && function_exists('gca_flag_enabled') && gca_flag_enabled('social-wall-notifications');
?>

<div class="gca-profile-tabs govuk-!-margin-top-6" id="gca-profile-tabs">

  <nav class="gca-profile-tabs__nav" role="tablist" aria-label="<?php echo $is_own_profile ? 'Your activity' : 'Profile activity'; ?>">

    <?php if ($show_saves_tabs) : ?>
    <button
      class="gca-profile-tabs__btn gca-profile-tabs__btn--active"
      role="tab"
      aria-selected="true"
      aria-controls="gca-tab-saves"
      id="gca-tab-btn-saves"
      data-tab="saves"
    >
      My saves
    </button>
    <?php endif; ?>

    <?php if ($show_shoutouts) : ?>
    <button
      class="gca-profile-tabs__btn<?php echo !$show_saves_tabs ? ' gca-profile-tabs__btn--active' : ''; ?>"
      role="tab"
      aria-selected="<?php echo !$show_saves_tabs ? 'true' : 'false'; ?>"
      aria-controls="gca-tab-shoutouts"
      id="gca-tab-btn-shoutouts"
      data-tab="shoutouts"
    >
      Shout-outs
    </button>
    <?php endif; ?>

    <?php if ($show_saves_tabs) : ?>
    <button
      class="gca-profile-tabs__btn"
      role="tab"
      aria-selected="false"
      aria-controls="gca-tab-mentions"
      id="gca-tab-btn-mentions"
      data-tab="mentions"
    >
      Mentions
      <span class="gca-profile-tabs__badge" id="gca-mentions-badge" hidden></span>
    </button>

    <button
      class="gca-profile-tabs__btn"
      role="tab"
      aria-selected="false"
      aria-controls="gca-tab-posts"
      id="gca-tab-btn-posts"
      data-tab="posts"
    >
      My posts
    </button>
    <?php endif; ?>

    <?php if ($show_notifications) : ?>
    <button
      class="gca-profile-tabs__btn"
      role="tab"
      aria-selected="false"
      aria-controls="gca-tab-notifications"
      id="gca-tab-btn-notifications"
      data-tab="notifications"
    >
      Notification settings
    </button>
    <?php endif; ?>

  </nav>

  <?php if ($show_saves_tabs) : ?>
  <!-- My Saves -->
  <div class="gca-profile-tabs__panel" role="tabpanel" id="gca-tab-saves" aria-labelledby="gca-tab-btn-saves">
    <p class="gca-profile-tabs__loading" id="gca-saves-loading">Loading&hellip;</p>
    <ul class="gca-profile-item-list" id="gca-saves-list" hidden></ul>
    <p class="gca-profile-tabs__empty" id="gca-saves-empty" hidden>You haven&rsquo;t saved anything yet.</p>
  </div>
  <?php endif; ?>

  <?php if ($show_shoutouts) : ?>
  <!-- Shout-outs -->
  <div class="gca-profile-tabs__panel" role="tabpanel" id="gca-tab-shoutouts" aria-labelledby="gca-tab-btn-shoutouts"<?php echo $show_saves_tabs ? ' hidden' : ''; ?>>
    <p class="gca-profile-tabs__loading" id="gca-shoutouts-loading"<?php echo $show_saves_tabs ? ' hidden' : ''; ?>>Loading&hellip;</p>
    <ul class="gca-profile-item-list gca-profile-item-list--mentions" id="gca-shoutouts-list" hidden></ul>
    <p class="gca-profile-tabs__empty" id="gca-shoutouts-empty" hidden>No shout-outs yet.</p>
  </div>
  <?php endif; ?>

  <?php if ($show_saves_tabs) : ?>
  <!-- Mentions -->
  <div class="gca-profile-tabs__panel" role="tabpanel" id="gca-tab-mentions" aria-labelledby="gca-tab-btn-mentions" hidden>
    <p class="gca-profile-tabs__loading" id="gca-mentions-loading" hidden>Loading&hellip;</p>
    <ul class="gca-profile-item-list gca-profile-item-list--mentions" id="gca-mentions-list" hidden></ul>
    <p class="gca-profile-tabs__empty" id="gca-mentions-empty" hidden>No mentions yet.</p>
  </div>

  <!-- My Posts -->
  <div class="gca-profile-tabs__panel" role="tabpanel" id="gca-tab-posts" aria-labelledby="gca-tab-btn-posts" hidden>
    <p class="gca-profile-tabs__loading" id="gca-posts-loading" hidden>Loading&hellip;</p>
    <ul class="gca-profile-item-list" id="gca-posts-list" hidden></ul>
    <p class="gca-profile-tabs__empty" id="gca-posts-empty" hidden>You haven&rsquo;t published any posts yet.</p>
  </div>
  <?php endif; ?>

  <?php if ($show_notifications) : ?>
  <!-- Notification settings -->
  <div class="gca-profile-tabs__panel" role="tabpanel" id="gca-tab-notifications" aria-labelledby="gca-tab-btn-notifications" hidden>
    <ul class="gca-notify-settings" id="gca-notify-settings-list">
      <li class="gca-notify-settings__row">
        <div class="gca-notify-settings__text">
          <span class="gca-notify-settings__label">Mention notifications</span>
          <span class="gca-notify-settings__hint">Notifies you when mentioned in a comment</span>
        </div>
        <label class="gca-toggle">
          <input type="checkbox" id="gca-notify-mentions" data-notify-type="mentions">
          <span class="gca-toggle-slider" aria-hidden="true"></span>
        </label>
      </li>
      <li class="gca-notify-settings__row">
        <div class="gca-notify-settings__text">
          <span class="gca-notify-settings__label">Shout-out notifications</span>
          <span class="gca-notify-settings__hint">Notifies you when you receive a shout-out on the wall</span>
        </div>
        <label class="gca-toggle">
          <input type="checkbox" id="gca-notify-shoutouts" data-notify-type="shoutouts">
          <span class="gca-toggle-slider" aria-hidden="true"></span>
        </label>
      </li>
      <li class="gca-notify-settings__row">
        <div class="gca-notify-settings__text">
          <span class="gca-notify-settings__label">Question and answer notifications</span>
          <span class="gca-notify-settings__hint">Notifies you when your question has been answered on the wall</span>
        </div>
        <label class="gca-toggle">
          <input type="checkbox" id="gca-notify-qa" data-notify-type="qa">
          <span class="gca-toggle-slider" aria-hidden="true"></span>
        </label>
      </li>
    </ul>
  </div>
  <?php endif; ?>

</div>

<script>
(function () {
    'use strict';

    var REST             = '<?php echo $rest_url; ?>';
    var NONCE            = '<?php echo $nonce; ?>';
    var PROFILE_USER_ID  = <?php echo $profile_user_id; ?>;
    var IS_OWN_PROFILE   = <?php echo $is_own_profile ? 'true' : 'false'; ?>;
    var SHOW_SHOUTOUTS   = <?php echo $show_shoutouts ? 'true' : 'false'; ?>;
    var cache = {};

    // ── Utilities ──────────────────────────────────────────────────────────

    function apiFetch(path) {
        if (cache[path]) { return Promise.resolve(cache[path]); }
        return fetch(REST + path, {
            headers: { 'X-WP-Nonce': NONCE }
        }).then(function (r) {
            if (!r.ok) { throw new Error(r.status); }
            return r.json();
        }).then(function (data) {
            cache[path] = data;
            return data;
        });
    }

    function relativeTime(isoString) {
        if (!isoString) { return null; }
        var diff = Math.floor((Date.now() - new Date(isoString)) / 1000);
        if (diff < 60)     { return 'just now'; }
        if (diff < 3600)   { return Math.floor(diff / 60) + ' minutes ago'; }
        if (diff < 86400)  { return Math.floor(diff / 3600) + ' hours ago'; }
        if (diff < 172800) { return 'yesterday'; }
        if (diff < 604800) { return Math.floor(diff / 86400) + ' days ago'; }
        return new Date(isoString).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function show(el) { el.hidden = false; }
    function hide(el) { el.hidden = true; }

    // ── Renderers ──────────────────────────────────────────────────────────

    function renderSaves(items) {
        var loadingEl = document.getElementById('gca-saves-loading');
        var listEl    = document.getElementById('gca-saves-list');
        var emptyEl   = document.getElementById('gca-saves-empty');

        hide(loadingEl);

        if (!items.length) {
            show(emptyEl);
            return;
        }

        listEl.innerHTML = items.map(function (item) {
            var meta = 'From ' + esc(item.post_type_label);
            var rel  = relativeTime(item.saved_at);
            if (rel) { meta += ' &bull; Saved ' + rel; }

            return '<li class="gca-profile-item">' +
                '<div class="gca-profile-item__body">' +
                    '<span class="gca-profile-item__label">Saved ' + esc(item.post_type_label.toLowerCase()) + '</span>' +
                    '<a href="' + esc(item.url) + '" class="gca-profile-item__title">' + esc(item.title) + '</a>' +
                    '<span class="gca-profile-item__meta">' + meta + '</span>' +
                '</div>' +
                '<button class="gca-profile-item__star gca-profile-item__star--saved" ' +
                    'aria-label="Unsave this item" data-post-id="' + item.id + '" title="Unsave">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                '</button>' +
            '</li>';
        }).join('');

        show(listEl);

        // Wire unsave buttons
        listEl.querySelectorAll('[data-post-id]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var postId = btn.dataset.postId;
                btn.disabled = true;
                fetch(REST + '/posts/' + postId + '/save', {
                    method:  'POST',
                    headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' }
                }).then(function () {
                    delete cache['/profile/me/saves'];
                    var li = btn.closest('li');
                    if (li) { li.remove(); }
                    if (!listEl.children.length) {
                        hide(listEl);
                        show(emptyEl);
                    }
                }).catch(function () { btn.disabled = false; });
            });
        });
    }

    function renderShoutouts(data) {
        var loadingEl = document.getElementById('gca-shoutouts-loading');
        var listEl    = document.getElementById('gca-shoutouts-list');
        var emptyEl   = document.getElementById('gca-shoutouts-empty');

        if (loadingEl) { hide(loadingEl); }

        var shoutouts = data.shoutouts || [];

        if (!shoutouts.length) {
            show(emptyEl);
            return;
        }

        listEl.innerHTML = shoutouts.map(function (s) {
            var rel = relativeTime(s.date_iso);
            var avatarHtml = s.giver_avatar
                ? '<img class="gca-profile-item__avatar" src="' + esc(s.giver_avatar) + '" alt="" aria-hidden="true">'
                : '<span class="gca-profile-item__avatar gca-profile-item__avatar--initial" aria-hidden="true">' + esc((s.giver_name || '?').charAt(0).toUpperCase()) + '</span>';
            var giverHtml = s.giver_profile
                ? '<a href="' + esc(s.giver_profile) + '" class="gca-profile-item__author">' + esc(s.giver_name) + '</a>'
                : '<strong class="gca-profile-item__author">' + esc(s.giver_name) + '</strong>';
            var teamHtml = s.giver_team ? '<span class="gca-profile-item__meta-text">' + esc(s.giver_team) + '</span>' : '';
            var catHtml  = s.category   ? ' &bull; <span class="gca-profile-item__label">' + esc(s.category) + '</span>' : '';
            return '<li class="gca-profile-item gca-profile-item--mention">' +
                avatarHtml +
                '<div class="gca-profile-item__body">' +
                    '<span class="gca-profile-item__mention-meta">' +
                        giverHtml +
                        (teamHtml ? ' &bull; ' + teamHtml : '') +
                        catHtml +
                        (rel ? ' &bull; ' + rel : '') +
                    '</span>' +
                    '<p class="gca-profile-item__comment-text">' + s.content_html + '</p>' +
                '</div>' +
            '</li>';
        }).join('');

        show(listEl);
    }

    function renderMentions(items) {
        var loadingEl = document.getElementById('gca-mentions-loading');
        var listEl    = document.getElementById('gca-mentions-list');
        var emptyEl   = document.getElementById('gca-mentions-empty');
        var badge     = document.getElementById('gca-mentions-badge');

        hide(loadingEl);

        if (badge && items.length) {
            badge.textContent = '(' + items.length + ')';
            show(badge);
        }

        if (!items.length) {
            show(emptyEl);
            return;
        }

        listEl.innerHTML = items.map(function (item) {
            var rel = item.date ? relativeTime(item.date) : null;

            if (item.type === 'shoutout') {
                var giverLink = item.author_profile
                    ? '<a href="' + esc(item.author_profile) + '" class="gca-profile-item__author">' + esc(item.author_name) + '</a>'
                    : '<strong>' + esc(item.author_name) + '</strong>';
                var avatarHtml = item.author_avatar
                    ? '<img class="gca-profile-item__avatar" src="' + esc(item.author_avatar) + '" alt="" aria-hidden="true">'
                    : '<span class="gca-profile-item__avatar gca-profile-item__avatar--initial" aria-hidden="true">' + esc((item.author_name || '?').charAt(0).toUpperCase()) + '</span>';
                return '<li class="gca-profile-item gca-profile-item--mention">' +
                    avatarHtml +
                    '<div class="gca-profile-item__body">' +
                        '<span class="gca-profile-item__mention-meta">' +
                            giverLink + ' gave you a Shout-out' +
                            (rel ? ' &bull; ' + rel : '') +
                        '</span>' +
                        '<p class="gca-profile-item__comment-text">' + item.content_html + '</p>' +
                    '</div>' +
                '</li>';
            }

            // Comment @mention
            return '<li class="gca-profile-item gca-profile-item--mention">' +
                '<img class="gca-profile-item__avatar" src="' + esc(item.author_avatar) + '" alt="" aria-hidden="true">' +
                '<div class="gca-profile-item__body">' +
                    '<span class="gca-profile-item__mention-meta">' +
                        '<a href="' + esc(item.author_profile) + '" class="gca-profile-item__author">' + esc(item.author_name) + '</a>' +
                        ' mentioned you in <a href="' + esc(item.post_url) + '" class="gca-profile-item__post-link">' + esc(item.post_title) + '</a>' +
                        (rel ? ' &bull; ' + rel : '') +
                    '</span>' +
                    '<p class="gca-profile-item__comment-text">' + item.content_html + '</p>' +
                '</div>' +
            '</li>';
        }).join('');

        show(listEl);
    }

    function renderPosts(items) {
        var loadingEl = document.getElementById('gca-posts-loading');
        var listEl    = document.getElementById('gca-posts-list');
        var emptyEl   = document.getElementById('gca-posts-empty');

        hide(loadingEl);

        if (!items.length) {
            show(emptyEl);
            return;
        }

        listEl.innerHTML = items.map(function (item) {
            var rel = relativeTime(item.date);

            if (item.type === 'shoutout') {
                var recipLink = item.recipient_profile
                    ? '<a href="' + esc(item.recipient_profile) + '" class="gca-profile-item__post-link">' + esc(item.recipient_name) + '</a>'
                    : '<strong>' + esc(item.recipient_name) + '</strong>';
                return '<li class="gca-profile-item">' +
                    '<div class="gca-profile-item__body">' +
                        '<span class="gca-profile-item__label">Shout-out</span>' +
                        '<span class="gca-profile-item__mention-meta">You gave a shout-out to ' + recipLink +
                            (rel ? ' &bull; ' + rel : '') +
                        '</span>' +
                        '<p class="gca-profile-item__comment-text">' + item.content_html + '</p>' +
                    '</div>' +
                '</li>';
            }

            if (item.post_type === 'community_post') {
                return '<li class="gca-profile-item">' +
                    '<div class="gca-profile-item__body">' +
                        '<span class="gca-profile-item__label">Community update</span>' +
                        (rel ? '<span class="gca-profile-item__meta">' + rel + '</span>' : '') +
                        '<p class="gca-profile-item__comment-text">' + item.content_html + '</p>' +
                    '</div>' +
                '</li>';
            }

            return '<li class="gca-profile-item">' +
                '<div class="gca-profile-item__body">' +
                    '<span class="gca-profile-item__label">' + esc(item.post_type_label) + '</span>' +
                    '<a href="' + esc(item.url) + '" class="gca-profile-item__title">' + esc(item.title) + '</a>' +
                    (rel ? '<span class="gca-profile-item__meta">Published ' + rel + '</span>' : '') +
                '</div>' +
            '</li>';
        }).join('');

        show(listEl);
    }

    function renderNotificationSettings(data) {
        ['mentions', 'shoutouts', 'qa'].forEach(function (type) {
            var checkbox = document.getElementById('gca-notify-' + type);
            if (checkbox) { checkbox.checked = !!data[type]; }
        });
    }

    var notifySettingsList = document.getElementById('gca-notify-settings-list');
    if (notifySettingsList) {
        notifySettingsList.addEventListener('change', function (e) {
            var checkbox = e.target.closest('[data-notify-type]');
            if (!checkbox) { return; }
            fetch(REST + '/profile/me/notification-settings', {
                method:  'POST',
                headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
                body:    JSON.stringify({ type: checkbox.dataset.notifyType, enabled: checkbox.checked })
            }).catch(function () {
                checkbox.checked = !checkbox.checked;
            });
        });
    }

    // ── Tab switching ──────────────────────────────────────────────────────

    var tabLoaded = {};

    function loadTab(tab) {
        if (tabLoaded[tab]) { return; }
        tabLoaded[tab] = true;

        if (tab === 'saves') {
            apiFetch('/profile/me/saves').then(renderSaves).catch(function () {
                hide(document.getElementById('gca-saves-loading'));
                show(document.getElementById('gca-saves-empty'));
            });
        } else if (tab === 'shoutouts') {
            var shoutoutsLoadingEl = document.getElementById('gca-shoutouts-loading');
            if (shoutoutsLoadingEl) { show(shoutoutsLoadingEl); }
            apiFetch('/shoutouts?recipient_id=' + PROFILE_USER_ID + '&per_page=20').then(renderShoutouts).catch(function () {
                if (shoutoutsLoadingEl) { hide(shoutoutsLoadingEl); }
                show(document.getElementById('gca-shoutouts-empty'));
            });
        } else if (tab === 'mentions') {
            show(document.getElementById('gca-mentions-loading'));
            apiFetch('/profile/me/mentions').then(renderMentions).catch(function () {
                hide(document.getElementById('gca-mentions-loading'));
                show(document.getElementById('gca-mentions-empty'));
            });
        } else if (tab === 'posts') {
            show(document.getElementById('gca-posts-loading'));
            apiFetch('/profile/me/posts').then(renderPosts).catch(function () {
                hide(document.getElementById('gca-posts-loading'));
                show(document.getElementById('gca-posts-empty'));
            });
        } else if (tab === 'notifications') {
            apiFetch('/profile/me/notification-settings').then(renderNotificationSettings);
        }
    }

    function activateTab(tab) {
        var btn = document.getElementById('gca-tab-btn-' + tab);
        if (!btn) { return false; }

        // Deactivate all
        document.querySelectorAll('.gca-profile-tabs__btn').forEach(function (b) {
            b.classList.remove('gca-profile-tabs__btn--active');
            b.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.gca-profile-tabs__panel').forEach(function (p) {
            p.hidden = true;
        });

        // Activate selected
        btn.classList.add('gca-profile-tabs__btn--active');
        btn.setAttribute('aria-selected', 'true');
        var panel = document.getElementById('gca-tab-' + tab);
        if (panel) { panel.hidden = false; }

        loadTab(tab);
        return true;
    }

    document.querySelectorAll('.gca-profile-tabs__btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activateTab(btn.dataset.tab);
        });
    });

    // Deep-link from notification emails, e.g. /profile/me/?tab=notifications
    var requestedTab = new URLSearchParams(window.location.search).get('tab');

    // Load the default tab
    if (IS_OWN_PROFILE) {
        if (!requestedTab || !activateTab(requestedTab)) {
            loadTab('saves');
        }
        // Pre-fetch mention count for badge
        apiFetch('/profile/me/mentions').then(function (items) {
            var badge = document.getElementById('gca-mentions-badge');
            if (badge && items.length) {
                badge.textContent = '(' + items.length + ')';
                show(badge);
            }
        });
    } else if (SHOW_SHOUTOUTS) {
        loadTab('shoutouts');
    }

}());
</script>
