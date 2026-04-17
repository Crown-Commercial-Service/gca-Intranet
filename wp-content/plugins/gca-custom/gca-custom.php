<?php

/*
Plugin Name: GCA Custom
Description: A WordPress plugin to contain custom code for the GCA intranet
Version: 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GCA_CUSTOM_FILE', __FILE__ );
define( 'GCA_CUSTOM_VERSION', '0.1' );

include('library/custom-post-types.php');
include('library/custom-post-taxonomies.php');
include('library/direct-admin-access.php');
include('library/class-gca-workday-api.php');
include('library/class-gca-sync-users.php');
include('library/class-gca-purge-events.php');
include('library/class-gca-author-selector.php');
include('library/class-gca-sync-profile-pictures.php');

add_action('init', function (): void {
    if (function_exists('gca_flag_enabled') && gca_flag_enabled('workday-user-sync')) {
        GCA_Sync_Users::init();
    }
});

add_action('init', function (): void {
    if (function_exists('gca_flag_enabled') && gca_flag_enabled('purge-events')) {
        GCA_Purge_Events::init();
    }
});

add_action('init', function (): void {
    if (function_exists('gca_flag_enabled') && gca_flag_enabled('author-selector')) {
        GCA_Author_Selector::init();
    }
});

add_action('init', function (): void {
    if (function_exists('gca_flag_enabled') && gca_flag_enabled('google-profile-picture')) {
        GCA_Sync_Profile_Pictures::init();
    }
});

add_action('init', function (): void {
    // ---------------------------------------------------------------------------
    // Helper: author image HTML
    //
    // Returns the <img> tag for a post's author using this priority:
    //   1. ACF custom image field on the post
    //   2. Google profile picture stored in user meta (populated on Google SSO login)
    //   3. Gravatar / WordPress default avatar
    //
    // Usage (inside The Loop):
    //   echo gca_get_author_image_html( get_the_ID(), (int) get_the_author_meta( 'ID' ) );
    // ---------------------------------------------------------------------------
    function gca_get_author_image_html( int $post_id, int $author_id ): string {
        $custom_img = get_field( 'image', $post_id );
        if ( $custom_img ) {
            return wp_get_attachment_image( $custom_img, 'thumbnail', false, [ 'class' => 'avatar' ] );
        }

        // get_avatar() internally calls get_avatar_url(), which is filtered by the
        // google-profile-picture feature to return the locally-cached Google photo
        // when that flag is enabled. Falls back to Gravatar otherwise.
        return get_avatar( $author_id, 96 ) ?: '';
    }
});
