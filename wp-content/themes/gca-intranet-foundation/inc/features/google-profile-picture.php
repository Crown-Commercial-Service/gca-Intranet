<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Google Profile Picture Sync
//
// On every Google SSO login, downloads the user's Google profile picture,
// stores it directly in a custom uploads subdirectory (not the media library),
// and uses it as their WordPress avatar.
//
// Generated letter-avatars (Google's default when a user has no real photo) are
// detected via GD colour analysis and discarded — only genuine photographs are
// stored. See GCA_Sync_Profile_Pictures::is_letter_avatar() for the algorithm.
// -----------------------------------------------------------------------------

gca_register_feature_flag('google-profile-picture', [
    'label'       => 'Google Profile Picture Sync',
    'description' => 'Downloads the user\'s Google profile picture on each SSO login and uses it as their avatar across the site. Generated letter-avatars are automatically filtered out using GD colour analysis. Run `wp gca sync-profile-pictures` to retroactively clean up any letter-avatars stored before this check was added.',
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

    $upload_dir  = wp_upload_dir();
    $profile_dir = $upload_dir['basedir'] . '/google-profile-pictures';
    $dest        = $profile_dir . '/user-' . $user->ID . '.jpg';

    // Skip if we've already evaluated this exact Google picture URL.
    // This covers both real photos (local file present) and confirmed letter avatars
    // (URL stored but no local file, so the site falls back to the default avatar).
    if (get_user_meta($user->ID, 'google_profile_picture_url', true) === $picture_url) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $tmp = download_url($picture_url);

    if (is_wp_error($tmp)) {
        return;
    }

    if (!file_exists($profile_dir)) {
        wp_mkdir_p($profile_dir);
        // Prevent directory listing.
        file_put_contents($profile_dir . '/index.php', '<?php // Silence is golden.');
    }

    if (file_exists($dest)) {
        @unlink($dest);
    }

    // copy()+unlink() works across filesystem boundaries (e.g. /tmp → uploads).
    $copied = copy($tmp, $dest);
    @unlink($tmp);

    if (!$copied) {
        return;
    }

    // Discard generated letter-avatars. Store the URL regardless so we don't
    // re-download on every subsequent login; just don't set the local URL,
    // which makes the get_avatar_url filter fall back to the WordPress default.
    if (GCA_Sync_Profile_Pictures::is_letter_avatar($dest)) {
        @unlink($dest);
        update_user_meta($user->ID, 'google_profile_picture_url', $picture_url);
        return;
    }

    $local_url = $upload_dir['baseurl'] . '/google-profile-pictures/user-' . $user->ID . '.jpg';

    update_user_meta($user->ID, 'google_profile_picture_url', $picture_url);
    update_user_meta($user->ID, 'google_profile_picture_local_url', $local_url);
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

    $local_url = get_user_meta($user->ID, 'google_profile_picture_local_url', true);

    if (!$local_url) {
        return $url;
    }

    return $local_url;
}, 10, 3);
