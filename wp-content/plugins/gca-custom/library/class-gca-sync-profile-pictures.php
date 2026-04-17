<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bulk-syncs Google profile pictures for all WordPress users.
 *
 * Mirrors the on-login behaviour of the `google-profile-picture` theme feature:
 * photos are stored in `{uploads}/google-profile-pictures/user-{ID}.jpg` and the
 * same user-meta keys are used.
 *
 * Only users who have previously logged in via Google SSO will have a stored
 * picture URL.  Users without one are skipped — they will be processed
 * automatically the next time they log in.
 *
 * Generated letter-avatars (Google's default when a user has no real photo) are
 * detected using GD colour analysis and discarded.  A real photograph contains
 * hundreds of distinct colours; a letter-avatar has only a handful.
 *
 * Requires:
 *  - The `google-profile-picture` feature flag to be enabled.
 *  - PHP's GD extension (gdimage).
 *
 * WP-CLI usage:
 *
 *     wp gca sync-profile-pictures
 *     wp gca sync-profile-pictures --user=42
 *     wp gca sync-profile-pictures --user=jane@example.com
 */
class GCA_Sync_Profile_Pictures {

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    public static function init(): void {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'gca sync-profile-pictures', [ __CLASS__, 'cli_command' ] );
        }
    }

    // -------------------------------------------------------------------------
    // WP-CLI entry point
    // -------------------------------------------------------------------------

    /**
     * Sync Google profile pictures for all (or one) WordPress users.
     *
     * Only processes users who have previously logged in via Google SSO and
     * therefore have a stored picture URL.  Others are reported as skipped.
     *
     * ## OPTIONS
     *
     * [--user=<id-or-email>]
     * : Limit the sync to a single user, identified by ID or email address.
     *
     * ## EXAMPLES
     *
     *     wp gca sync-profile-pictures
     *     wp gca sync-profile-pictures --user=42
     *     wp gca sync-profile-pictures --user=jane@example.com
     *
     * @when after_wp_load
     */
    public static function cli_command( array $args, array $assoc_args ): void {
        if ( ! function_exists( 'gca_flag_enabled' ) || ! gca_flag_enabled( 'google-profile-picture' ) ) {
            WP_CLI::error( 'The google-profile-picture feature flag is not enabled.' );
        }

        $user_arg = $assoc_args['user'] ?? null;

        if ( $user_arg !== null ) {
            $user = is_numeric( $user_arg )
                ? get_userdata( (int) $user_arg )
                : get_user_by( 'email', $user_arg );

            if ( ! $user instanceof WP_User ) {
                WP_CLI::error( "User not found: {$user_arg}" );
            }

            $users = [ $user ];
        } else {
            // Only fetch users who have a stored picture URL — others cannot be
            // processed without the user's active Google session.
            $users = get_users( [
                'meta_key'     => 'google_profile_picture_url',
                'meta_compare' => 'EXISTS',
            ] );
        }

        WP_CLI::log( sprintf( 'Processing %d user(s)…', count( $users ) ) );

        $stats = self::sync( $users, function ( string $message ) {
            WP_CLI::log( $message );
        } );

        WP_CLI::success( sprintf(
            'Done. Saved: %d, Cleared: %d, Skipped: %d, Errors: %d.',
            $stats['saved'],
            $stats['cleared'],
            $stats['skipped'],
            $stats['errors']
        ) );
    }

    // -------------------------------------------------------------------------
    // Core sync logic
    // -------------------------------------------------------------------------

    /**
     * @param WP_User[]     $users
     * @param callable|null $logger Receives log-message strings.
     * @return array{saved:int, cleared:int, skipped:int, errors:int}
     */
    public static function sync( array $users, ?callable $logger = null ): array {
        $log = $logger ?? static function ( string $message ): void {
            error_log( '[GCA Profile Pic Sync] ' . $message );
        };

        $stats = [
            'saved'   => 0,
            'cleared' => 0,
            'skipped' => 0,
            'errors'  => 0,
        ];

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $upload_dir  = wp_upload_dir();
        $profile_dir = $upload_dir['basedir'] . '/google-profile-pictures';

        foreach ( $users as $user ) {
            $picture_url = get_user_meta( $user->ID, 'google_profile_picture_url', true );

            if ( empty( $picture_url ) ) {
                $log( "Skipping {$user->user_email}: no stored picture URL (user has not logged in via Google SSO)." );
                $stats['skipped']++;
                continue;
            }

            $dest = $profile_dir . '/user-' . $user->ID . '.jpg';

            // If the local file already exists, analyse it directly — no re-download needed.
            if ( file_exists( $dest ) ) {
                if ( self::is_letter_avatar( $dest ) ) {
                    @unlink( $dest );
                    delete_user_meta( $user->ID, 'google_profile_picture_local_url' );
                    $log( "Cleared letter avatar for {$user->user_email}." );
                    $stats['cleared']++;
                } else {
                    $log( "Real photo confirmed for {$user->user_email} — no change." );
                    $stats['saved']++;
                }
                continue;
            }

            // No local file — re-download from the stored Google picture URL.
            $tmp = download_url( $picture_url );

            if ( is_wp_error( $tmp ) ) {
                $log( "Error downloading photo for {$user->user_email}: " . $tmp->get_error_message() );
                $stats['errors']++;
                continue;
            }

            if ( ! file_exists( $profile_dir ) ) {
                wp_mkdir_p( $profile_dir );
                file_put_contents( $profile_dir . '/index.php', '<?php // Silence is golden.' );
            }

            $copied = copy( $tmp, $dest );
            @unlink( $tmp );

            if ( ! $copied ) {
                $log( "Error saving photo for {$user->user_email}: could not write to {$dest}." );
                $stats['errors']++;
                continue;
            }

            if ( self::is_letter_avatar( $dest ) ) {
                @unlink( $dest );
                $log( "Cleared letter avatar for {$user->user_email} (re-downloaded)." );
                $stats['cleared']++;
            } else {
                $local_url = $upload_dir['baseurl'] . '/google-profile-pictures/user-' . $user->ID . '.jpg';
                update_user_meta( $user->ID, 'google_profile_picture_local_url', $local_url );
                $log( "Saved real photo for {$user->user_email}." );
                $stats['saved']++;
            }
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // Letter-avatar detection
    // -------------------------------------------------------------------------

    /**
     * Returns true if the image at the given path appears to be a generated
     * letter-avatar rather than a real photograph.
     *
     * The algorithm resamples the image to a 20×20 thumbnail and counts the
     * number of distinct colours.  Google's letter-avatars use a solid background
     * with a single white initial, producing only 2–5 unique colours.  Real
     * photographs contain hundreds.  A threshold of 30 gives ample headroom.
     *
     * Returns false (assume real photo) if GD is not available or the image
     * cannot be read, so that real photos are never silently discarded.
     */
    public static function is_letter_avatar( string $file_path ): bool {
        if ( ! function_exists( 'imagecreatefromstring' ) ) {
            return false; // GD unavailable — assume real photo.
        }

        $data = @file_get_contents( $file_path );
        if ( $data === false || strlen( $data ) < 100 ) {
            return false;
        }

        $img = @imagecreatefromstring( $data );
        if ( $img === false ) {
            return false;
        }

        // Resample to 20×20 — enough resolution to capture colour variety while
        // keeping the loop fast regardless of the source image dimensions.
        $sample = imagecreatetruecolor( 20, 20 );
        imagecopyresampled( $sample, $img, 0, 0, 0, 0, 20, 20, imagesx( $img ), imagesy( $img ) );
        imagedestroy( $img );

        $unique = [];
        for ( $x = 0; $x < 20; $x++ ) {
            for ( $y = 0; $y < 20; $y++ ) {
                $unique[ imagecolorat( $sample, $x, $y ) ] = true;
            }
        }
        imagedestroy( $sample );

        return count( $unique ) < 30;
    }
}
