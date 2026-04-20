<?php
/**
 * Staff Profile page template.
 * Rendered at /profile/<user_nicename>
 */

declare(strict_types=1);

$profile_slug = (string) get_query_var('gca_profile');
$user         = $profile_slug ? get_user_by('slug', $profile_slug) : false;

if (!$user instanceof WP_User) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}

// Collect user data.
$display_name = $user->display_name ?: $user->user_login;
$email        = $user->user_email;
$job_title    = (string) get_user_meta($user->ID, 'job_title', true);
$team         = (string) get_user_meta($user->ID, 'team', true);
$avatar_url   = (string) get_user_meta($user->ID, 'google_profile_picture_local_url', true);

get_header();
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-9">
  <main id="main-content" tabindex="-1">
    <?php get_template_part('template-parts/profile-card', null, [
        'user'         => $user,
        'display_name' => $display_name,
        'email'        => $email,
        'job_title'    => $job_title,
        'team'         => $team,
        'avatar_url'   => $avatar_url,
    ]); ?>
  </main>
</div>

<?php get_footer(); ?>
