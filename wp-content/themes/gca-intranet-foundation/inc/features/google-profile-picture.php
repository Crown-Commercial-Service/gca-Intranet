<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Google Profile Picture Sync
//
// On every Google SSO login, downloads the user's Google profile picture,
// stores it in the media library, and uses it as their WordPress avatar.
// -----------------------------------------------------------------------------

gca_register_feature_flag('google-profile-picture', [
    'label'       => 'Google Profile Picture Sync',
    'description' => 'Downloads the user\'s Google profile picture on each SSO login and uses it as their avatar across the site.',
    'default'     => false,
    'tags'        => ['users', 'sso'],
]);

add_action('gal_user_loggedin', function (WP_User $user, object $userinfo): void {
    if (!gca_flag_enabled('google-profile-picture')) {
        return;
    }

    if (empty($userinfo->picture)) {
        return;
    }

    // Strip Google's sizing parameters to get the full-resolution image.
    $picture_url = preg_replace('/=s\d+-c$/', '', $userinfo->picture);

    // Only re-download if the source URL has changed since last login.
    if (get_user_meta($user->ID, 'google_profile_picture_url', true) === $picture_url) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url($picture_url);

    if (is_wp_error($tmp)) {
        return;
    }

    $attachment_id = media_handle_sideload(
        ['name' => 'google-profile-' . $user->ID . '.jpg', 'tmp_name' => $tmp],
        0,
        null,
        ['post_author' => $user->ID]
    );

    if (is_wp_error($attachment_id)) {
        if (file_exists($tmp)) {
            @unlink($tmp);
        }
        return;
    }

    update_user_meta($user->ID, 'google_profile_picture_url', $picture_url);
    update_user_meta($user->ID, 'google_profile_picture_id', $attachment_id);
}, 10, 2);

add_filter('get_avatar_url', function (string $url, mixed $id_or_email, array $args): string {
    if (!gca_flag_enabled('google-profile-picture')) {
        return $url;
    }

    $user = null;

    if (is_numeric($id_or_email)) {
        $user = get_userdata((int) $id_or_email);
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
    } elseif ($id_or_email instanceof WP_User) {
        $user = $id_or_email;
    } elseif ($id_or_email instanceof WP_Comment && $id_or_email->user_id) {
        $user = get_userdata((int) $id_or_email->user_id);
    }

    if (!$user instanceof WP_User) {
        return $url;
    }

    $attachment_id = get_user_meta($user->ID, 'google_profile_picture_id', true);

    if (!$attachment_id) {
        return $url;
    }

    return wp_get_attachment_image_url($attachment_id, 'thumbnail') ?: $url;
}, 10, 3);
