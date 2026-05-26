<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Archives event posts that ended (or started) more than one month ago.
 *
 * Registers the WordPress cron hook `gca_purge_events` so it can be
 * scheduled via the Cron Jobs admin page (Tools → Cron Jobs). To schedule it,
 * enter `gca_purge_events` as the hook name.
 *
 * Also registers the WP-CLI command `wp gca purge-events` for manual runs.
 *
 * Archive logic:
 *  - If the event has an `end_date`, that date is used as the reference.
 *  - If `end_date` is absent or unparseable, `start_date` is used instead.
 *  - Events whose reference date is more than one month in the past are
 *    moved to the custom `gca_archived` post status so they no longer appear
 *    on the frontend but remain recoverable in the admin.
 *  - Events with no parseable date are skipped and logged.
 *
 * ACF storage formats used by this plugin:
 *   start_date – date_picker       → stored as Ymd       (e.g. 20260415)
 *   end_date   – date_time_picker  → stored as Y-m-d H:i:s (e.g. 2026-04-15 00:00:00)
 */
class GCA_Purge_Events {

    const CRON_HOOK       = 'gca_purge_events';
    const ARCHIVED_STATUS = 'gca_archived';

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    public static function init(): void {
        self::register_archived_status();
        add_action( self::CRON_HOOK, [ __CLASS__, 'run' ] );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'gca purge-events', [ __CLASS__, 'cli_command' ] );
        }
    }

    public static function register_archived_status(): void {
        register_post_status( self::ARCHIVED_STATUS, [
            'label'                     => _x( 'Archived', 'post status', 'gca' ),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => false,
            'show_in_admin_status_list' => true,
            /* translators: %s: number of archived events */
            'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'gca' ),
        ] );
    }

    // -------------------------------------------------------------------------
    // WP-CLI entry point
    // -------------------------------------------------------------------------

    /**
     * Delete events that ended (or started) more than one month ago.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : List events that would be archived without actually archiving them.
     *
     * ## EXAMPLES
     *
     *     wp gca purge-events
     *     wp gca purge-events --dry-run
     *
     * @when after_wp_load
     */
    public static function cli_command( array $args, array $assoc_args ): void {
        $dry_run = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

        if ( $dry_run ) {
            WP_CLI::log( 'Dry-run mode enabled — no events will be archived.' );
        }

        WP_CLI::log( 'Starting event archive…' );

        try {
            $stats = self::purge(
                function ( string $message ) {
                    WP_CLI::log( $message );
                },
                $dry_run
            );

            WP_CLI::success( sprintf(
                'Archive complete. Archived: %d, Skipped: %d.',
                $stats['archived'],
                $stats['skipped']
            ) );
        } catch ( Exception $e ) {
            WP_CLI::error( 'Purge failed: ' . $e->getMessage() );
        }
    }

    // -------------------------------------------------------------------------
    // Cron entry point
    // -------------------------------------------------------------------------

    public static function run(): void {
        if ( function_exists( 'gca_flag_enabled' ) && ! gca_flag_enabled( 'purge-events' ) ) {
            error_log( '[GCA Event Purge] Skipped: feature flag is disabled.' );
            do_action( 'gca_cron_run_completed', self::CRON_HOOK, null, [ 'Skipped: feature flag is disabled.' ] );
            return;
        }

        $log_lines = [];

        try {
            $stats = self::purge( function ( string $message ) use ( &$log_lines ) {
                $log_lines[] = $message;
                error_log( '[GCA Event Purge] ' . $message );
            } );

            $summary = sprintf(
                'Complete. Archived: %d, Skipped: %d.',
                $stats['archived'],
                $stats['skipped']
            );
            $log_lines[] = $summary;
            error_log( '[GCA Event Purge] ' . $summary );

            do_action( 'gca_cron_run_completed', self::CRON_HOOK, $stats, $log_lines );
        } catch ( Exception $e ) {
            $log_lines[] = 'Error: ' . $e->getMessage();
            error_log( '[GCA Event Purge] Error: ' . $e->getMessage() );

            do_action( 'gca_cron_run_completed', self::CRON_HOOK, null, $log_lines );
        }
    }

    // -------------------------------------------------------------------------
    // Core purge logic
    // -------------------------------------------------------------------------

    /**
     * @param callable|null $logger  Receives log message strings.
     * @param bool          $dry_run When true, events are logged but not archived.
     * @return array{archived:int, skipped:int}
     */
    private static function purge( ?callable $logger = null, bool $dry_run = false ): array {
        $log = $logger ?? static function ( string $message ): void {
            error_log( '[GCA Event Purge] ' . $message );
        };

        $stats = [
            'archived' => 0,
            'skipped'  => 0,
        ];

        $threshold = new DateTime( '-1 month' );

        $event_ids = get_posts( [
            'post_type'      => 'event',
            'post_status'    => [ 'publish', 'private', 'draft' ],
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        $log( sprintf( 'Found %d event(s) to evaluate.', count( $event_ids ) ) );

        foreach ( $event_ids as $post_id ) {
            $end_raw   = get_post_meta( $post_id, 'end_date', true );
            $start_raw = get_post_meta( $post_id, 'start_date', true );

            [ $reference_date, $reference_field ] = self::resolve_reference_date( $end_raw, $start_raw );

            if ( null === $reference_date ) {
                $log( sprintf( 'Skipping event #%d "%s": no parseable date found.', $post_id, get_the_title( $post_id ) ) );
                $stats['skipped']++;
                continue;
            }

            if ( $reference_date >= $threshold ) {
                $stats['skipped']++;
                continue;
            }

            $title = get_the_title( $post_id );

            if ( $dry_run ) {
                $log( sprintf(
                    '[dry-run] Would archive event #%d "%s" (%s: %s).',
                    $post_id,
                    $title,
                    $reference_field,
                    $reference_date->format( 'd-m-Y' )
                ) );
            } else {
                wp_update_post( [
                    'ID'          => $post_id,
                    'post_status' => self::ARCHIVED_STATUS,
                ] );
                $log( sprintf(
                    'Archived event #%d "%s" (%s: %s).',
                    $post_id,
                    $title,
                    $reference_field,
                    $reference_date->format( 'd-m-Y' )
                ) );
            }

            $stats['archived']++;
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the date to use as the deletion threshold reference and the field
     * name it was taken from.
     *
     * Priority: end_date → start_date. Returns [null, ''] when neither can be
     * parsed.
     *
     * @param string $end_raw   Raw meta value for end_date.
     * @param string $start_raw Raw meta value for start_date.
     * @return array{0: DateTime|null, 1: string}
     */
    private static function resolve_reference_date( string $end_raw, string $start_raw ): array {
        // end_date is a date_time_picker — ACF stores as "Y-m-d H:i:s".
        if ( '' !== $end_raw ) {
            $date = DateTime::createFromFormat( 'Y-m-d H:i:s', $end_raw )
                 ?: DateTime::createFromFormat( 'Ymd', $end_raw );

            if ( $date instanceof DateTime ) {
                return [ $date, 'end_date' ];
            }
        }

        // start_date is a date_picker — ACF stores as "Ymd".
        if ( '' !== $start_raw ) {
            $date = DateTime::createFromFormat( 'Ymd', $start_raw )
                 ?: DateTime::createFromFormat( 'Y-m-d H:i:s', $start_raw );

            if ( $date instanceof DateTime ) {
                return [ $date, 'start_date' ];
            }
        }

        return [ null, '' ];
    }
}
