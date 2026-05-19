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

get_header();
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-9">
  <main id="main-content" tabindex="-1">
    <?php get_template_part('template-parts/profile-card', null, [
        'user'         => $user,
        'display_name' => $profile_data['display_name'],
        'email'        => $profile_data['email'],
        'job_title'    => $profile_data['job_title'],
        'team'         => $profile_data['team'],
        'avatar_url'   => $profile_data['avatar_url'],
    ]); ?>

    <?php if ($is_own_profile) : ?>
      <?php get_template_part('template-parts/profile-tabs', null, [
          'rest_url' => esc_url_raw(rest_url('gca/v1')),
          'nonce'    => wp_create_nonce('wp_rest'),
      ]); ?>
    <?php endif; ?>
  </main>
</div>

<?php get_footer(); ?>
