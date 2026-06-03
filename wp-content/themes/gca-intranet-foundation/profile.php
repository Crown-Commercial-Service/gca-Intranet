<?php
/**
 * Staff Profile page template.
 * Rendered at /profile/<user_nicename>
 */

declare(strict_types=1);

$profile_slug = (string) get_query_var('gca_profile');
$user         = $profile_slug && function_exists('gca_get_staff_user_by_profile_slug')
    ? gca_get_staff_user_by_profile_slug($profile_slug)
    : false;

if (!$user instanceof WP_User) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}

$profile_data = function_exists('gca_get_staff_profile_data')
    ? gca_get_staff_profile_data($user)
    : null;

if ($profile_data === null) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}

$is_own_profile = is_user_logged_in() && get_current_user_id() === $user->ID;

add_filter('body_class', function (array $classes): array {
    $classes[] = 'gca-profile-page';
    return $classes;
});

get_header();
?>

<div class="gca-profile-banner" aria-hidden="true"></div>

<div class="govuk-width-container gca-profile-content govuk-!-padding-bottom-9">
  <main id="main-content" tabindex="-1">
    <?php get_template_part('template-parts/profile-card', null, [
        'user'         => $user,
        'display_name' => $profile_data['display_name'],
        'email'        => $profile_data['email'],
        'business_title' => $profile_data['business_title'],
        'team'         => $profile_data['team'],
        'avatar_url'   => $profile_data['avatar_url'],
    ]); ?>

    <?php if ($is_own_profile && gca_flag_enabled('post-saves')) : ?>
      <?php get_template_part('template-parts/profile-tabs', null, [
          'rest_url' => esc_url_raw(rest_url('gca/v1')),
          'nonce'    => wp_create_nonce('wp_rest'),
      ]); ?>
    <?php endif; ?>

    <?php if (gca_flag_enabled('community-shoutouts') && is_user_logged_in()) : ?>
    <section class="gca-profile-shoutouts govuk-!-margin-top-6" id="gca-profile-shoutouts">
      <h2 class="govuk-heading-m">Shout-outs</h2>
      <p class="gca-profile-shoutouts__loading" id="gca-profile-shoutouts-loading">Loading&hellip;</p>
      <ul class="gca-profile-shoutouts__list" id="gca-profile-shoutouts-list" hidden></ul>
      <p class="gca-profile-shoutouts__empty" id="gca-profile-shoutouts-empty" hidden>No shout-outs yet.</p>
    </section>
    <script>
    (function () {
        'use strict';
        var REST    = '<?php echo esc_js(esc_url_raw(rest_url('gca/v1'))); ?>';
        var NONCE   = '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>';
        var USER_ID = <?php echo (int) $user->ID; ?>;

        var listEl    = document.getElementById('gca-profile-shoutouts-list');
        var loadingEl = document.getElementById('gca-profile-shoutouts-loading');
        var emptyEl   = document.getElementById('gca-profile-shoutouts-empty');

        function esc(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function relativeTime(iso) {
            if (!iso) { return ''; }
            var then = new Date(iso);
            var now  = new Date();
            var diff = Math.floor((now - then) / 1000);
            if (diff < 60)    { return 'just now'; }
            if (diff < 3600)  { var m = Math.floor(diff / 60);   return m + ' min' + (m === 1 ? '' : 's') + ' ago'; }
            if (diff < 86400) { var h = Math.floor(diff / 3600); return h + ' hr'  + (h === 1 ? '' : 's') + ' ago'; }
            var days = Math.floor(diff / 86400);
            if (days < 30)    { return days + ' day' + (days === 1 ? '' : 's') + ' ago'; }
            return then.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        fetch(REST + '/shoutouts?recipient_id=' + USER_ID + '&per_page=20', {
            headers: { 'X-WP-Nonce': NONCE }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            loadingEl.hidden = true;
            var shoutouts = data.shoutouts || [];
            if (!shoutouts.length) {
                emptyEl.hidden = false;
                return;
            }
            listEl.innerHTML = shoutouts.map(function (s) {
                var avatarHtml = s.giver_avatar
                    ? '<img class="gca-profile-shoutouts__avatar" src="' + esc(s.giver_avatar) + '" alt="" width="40" height="40">'
                    : '<span class="gca-profile-shoutouts__avatar gca-profile-shoutouts__avatar--initial">' + esc((s.giver_name || '?').charAt(0).toUpperCase()) + '</span>';
                var giverHtml = s.giver_profile
                    ? '<a href="' + esc(s.giver_profile) + '" class="gca-profile-shoutouts__giver-name">' + esc(s.giver_name) + '</a>'
                    : '<strong class="gca-profile-shoutouts__giver-name">' + esc(s.giver_name) + '</strong>';
                var metaHtml = s.giver_team ? '<span class="gca-profile-shoutouts__giver-team">' + esc(s.giver_team) + '</span>' : '';
                var catHtml  = s.category   ? '<span class="gca-profile-shoutouts__category">' + esc(s.category) + '</span>' : '';
                var timeHtml = s.date_iso   ? '<time class="gca-profile-shoutouts__time" datetime="' + esc(s.date_iso) + '">' + relativeTime(s.date_iso) + '</time>' : '';
                return '<li class="gca-profile-shoutouts__item">' +
                    '<div class="gca-profile-shoutouts__header">' +
                        avatarHtml +
                        '<div class="gca-profile-shoutouts__giver-info">' +
                            '<div>' + giverHtml + (timeHtml ? ' &bull; ' + timeHtml : '') + '</div>' +
                            (metaHtml || catHtml ? '<div>' + metaHtml + catHtml + '</div>' : '') +
                        '</div>' +
                    '</div>' +
                    '<p class="gca-profile-shoutouts__message">' + s.content_html + '</p>' +
                '</li>';
            }).join('');
            listEl.hidden = false;
        })
        .catch(function () {
            loadingEl.hidden = true;
            emptyEl.hidden   = false;
        });
    }());
    </script>
    <?php endif; ?>
  </main>
</div>

<?php get_footer(); ?>
