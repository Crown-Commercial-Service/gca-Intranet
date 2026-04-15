<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Deletes event posts that ended (or started) more than one month ago.
 *
 * Registers the WordPress cron hook `gca_purge_events` so it can be
 * scheduled via the Cron Jobs admin page (Tools → Cron Jobs). To schedule it,
 * enter `gca_purge_events` as the hook name.
 *
 * Also registers the WP-CLI command `wp gca purge-events` for manual runs.
 *
 * Deletion logic:
 *  - If the event has an `end_date`, that date is used as the reference.
 *  - If `end_date` is absent or unparseable, `start_date` is used instead.
 *  - Events whose reference date is more than one month in the past are
 *    permanently deleted (i.e. bypassing the trash).
 *  - Events with no parseable date are skipped and logged.
 *
 * ACF storage formats used by this plugin:
 *   start_date – date_picker       → stored as Ymd       (e.g. 20260415)
 *   end_date   – date_time_picker  → stored as Y-m-d H:i:s (e.g. 2026-04-15 00:00:00)
 */
class GCA_Purge_Events {

    const CRON_HOOK = 'gca_purge_events';

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    public static function init(): void {
        add_action( self::CRON_HOOK, [ __CLASS__, 'run' ] );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'gca purge-events', [ __CLASS__, 'cli_command' ] );
        }
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
     * : List events that would be deleted without actually deleting them.
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
            WP_CLI::log( 'Dry-run mode enabled — no events will be deleted.' );
        }

        WP_CLI::log( 'Starting event purge…' );

        try {
            $stats = self::purge(
                function ( string $message ) {
                    WP_CLI::log( $message );
                },
                $dry_run
            );

            WP_CLI::success( sprintf(
                'Purge complete. Deleted: %d, Skipped: %d.',
                $stats['deleted'],
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
                'Complete. Deleted: %d, Skipped: %d.',
                $stats['deleted'],
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
     * @param bool          $dry_run When true, events are logged but not deleted.
     * @return array{deleted:int, skipped:int}
     */
    private static function purge( ?callable $logger = null, bool $dry_run = false ): array {
        $log = $logger ?? static function ( string $message ): void {
            error_log( '[GCA Event Purge] ' . $message );
        };

        $stats = [
            'deleted' => 0,
            'skipped' => 0,
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
                    '[dry-run] Would delete event #%d "%s" (%s: %s).',
                    $post_id,
                    $title,
                    $reference_field,
                    $reference_date->format( 'd-m-Y' )
                ) );
            } else {
                wp_delete_post( $post_id, true );
                $log( sprintf(
                    'Deleted event #%d "%s" (%s: %s).',
                    $post_id,
                    $title,
                    $reference_field,
                    $reference_date->format( 'd-m-Y' )
                ) );
            }

            $stats['deleted']++;
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
