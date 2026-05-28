<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Syncs WordPress users with the Workday staff list API.
 *
 * Registers the WordPress cron hook `gca_sync_workday_users` so it can be
 * scheduled via the Cron Jobs admin page (Tools → Cron Jobs). To schedule it,
 * enter `gca_sync_workday_users` as the hook name.
 *
 * Also registers the WP-CLI command `wp gca sync-users` for manual runs.
 *
 * Sync behaviour:
 *  - API user exists, WP user exists  → update WP user details and meta.
 *  - API user exists, WP user missing → create WP user (role: subscriber).
 *  - WP user missing from API         → delete, UNLESS:
 *                                         • ENABLE_DELETE is false (deletion
 *                                           is currently disabled), OR
 *                                         • the user's login is in
 *                                           PROTECTED_LOGINS, OR
 *                                         • the user has the administrator role.
 *
 * API record shape (relevant fields):
 *   EmployeeKey  – unique integer identifier for each staff member
 *   EmployeeName – full name, e.g. "Natalie Cadwallader"
 *   Email        – work email, e.g. "natalie.cadwallader@gca.gov.uk"
 *   Manager      – manager's full name
 *   ManagerEmail – manager's email address
 *   JobTitle     – e.g. "Procurement Practitioner - Procurement Management"
 *   Team         – e.g. "Procurement Liverpool (Natalie Kenyon)"
 *   Directorate  – e.g. "Commercial & Procurement Operations"
 *
 * WordPress user mapping (both derived from the email prefix, i.e. the part
 * before "@", e.g. "natalie.cadwallader" from "natalie.cadwallader@gca.gov.uk"):
 *   user_login    = email prefix as-is, e.g. "natalie.cadwallader"
 *   user_nicename = email prefix with dots replaced by hyphens,
 *                   e.g. "natalie-cadwallader"
 *   user_email    = Email
 *   display_name  = EmployeeName
 *
 * User meta mapping:
 *   employee_key  = EmployeeKey
 *   job_title     = JobTitle
 *   team          = Team (with trailing "(Manager Name)" stripped)
 *   directorate   = Directorate
 *   manager       = Manager
 *   manager_email = ManagerEmail
 *   workday_item_id = ItemInternalId (internal Workday record ID)
 */
class GCA_Sync_Users {

    const CRON_HOOK           = 'gca_sync_workday_users';
    const WORKDAY_ID_META_KEY = 'workday_item_id';
    const EMPLOYEE_KEY_META   = 'employee_key';

    /**
     * Whether the sync is allowed to hard-delete WordPress users whose
     * soft-delete grace period has elapsed. Set to true only when you are
     * ready to enable hard purging.
     */
    const ENABLE_DELETE = true;

    /**
     * WordPress user logins that will never be deleted during a sync,
     * regardless of whether they appear in the API response.
     *
     * @var string[]
     */
    const PROTECTED_LOGINS = [
        'admin',
        'former-employee',
    ];

    const DELETED_AT_META_KEY    = 'deleted_at';
    const FORMER_EMPLOYEE_LOGIN  = 'former-employee';
    const FORMER_EMPLOYEE_EMAIL  = 'former-employee@gca.co.uk';
    const SOFT_DELETE_GRACE_DAYS = 5;

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    public static function init(): void {
        add_action( self::CRON_HOOK, [ __CLASS__, 'run' ] );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'gca sync-users', [ __CLASS__, 'cli_command' ] );
        }
    }

    // -------------------------------------------------------------------------
    // WP-CLI entry point
    // -------------------------------------------------------------------------

    /**
     * Sync WordPress users with the Workday staff list API.
     *
     * ## EXAMPLES
     *
     *     wp gca sync-users
     *
     * @when after_wp_load
     */
    public static function cli_command( array $args, array $assoc_args ): void {
        WP_CLI::log( 'Starting Workday user sync…' );

        try {
            $stats = self::sync( function ( string $message ) {
                WP_CLI::log( $message );
            } );

            WP_CLI::success( sprintf(
                'Sync complete. Created: %d, Updated: %d, Soft-deleted: %d, Deleted: %d, Skipped: %d, Errors: %d.',
                $stats['created'],
                $stats['updated'],
                $stats['soft_deleted'],
                $stats['deleted'],
                $stats['skipped'],
                $stats['errors']
            ) );
        } catch ( Exception $e ) {
            WP_CLI::error( 'Sync failed: ' . $e->getMessage() );
        }
    }

    // -------------------------------------------------------------------------
    // Cron entry point
    // -------------------------------------------------------------------------

    public static function run(): void {
        // Prevent concurrent runs — WordPress cron can fire the same hook twice
        // if two requests arrive simultaneously (TOCTOU race → duplicate users).
        if ( get_transient( self::CRON_HOOK . '_running' ) ) {
            error_log( '[GCA Workday Sync] Skipped — another instance is already running.' );
            return;
        }
        set_transient( self::CRON_HOOK . '_running', true, 10 * MINUTE_IN_SECONDS );

        $log_lines = [];

        try {
            $stats = self::sync( function ( string $message ) use ( &$log_lines ) {
                $log_lines[] = $message;
                error_log( '[GCA Workday Sync] ' . $message );
            } );

            $summary = sprintf(
                'Complete. Created: %d, Updated: %d, Soft-deleted: %d, Deleted: %d, Skipped: %d, Errors: %d.',
                $stats['created'],
                $stats['updated'],
                $stats['soft_deleted'],
                $stats['deleted'],
                $stats['skipped'],
                $stats['errors']
            );
            $log_lines[] = $summary;
            error_log( '[GCA Workday Sync] ' . $summary );

            do_action( 'gca_cron_run_completed', self::CRON_HOOK, $stats, $log_lines );
        } catch ( Exception $e ) {
            $log_lines[] = 'Error: ' . $e->getMessage();
            error_log( '[GCA Workday Sync] Error: ' . $e->getMessage() );

            do_action( 'gca_cron_run_completed', self::CRON_HOOK, null, $log_lines );
        }
    }

    // -------------------------------------------------------------------------
    // Core sync logic
    // -------------------------------------------------------------------------

    /**
     * @param callable|null $logger Receives log message strings.
     * @return array{created:int, updated:int, deleted:int, skipped:int, errors:int}
     */
    private static function sync( ?callable $logger = null ): array {
        $log = $logger ?? static function ( string $message ): void {
            error_log( '[GCA Workday Sync] ' . $message );
        };

        $stats = [
            'created'      => 0,
            'updated'      => 0,
            'soft_deleted' => 0,
            'deleted'      => 0,
            'skipped'      => 0,
            'errors'       => 0,
        ];

        $api   = new GCA_Workday_API();
        $staff = $api->get_staff();

        $log( sprintf( 'Fetched %d staff records from API.', count( $staff ) ) );

        // Build a lookup of API users indexed by normalised email address.
        $api_users = [];
        foreach ( $staff as $record ) {
            $email = strtolower( trim( $record['Email'] ?? '' ) );
            if ( '' !== $email ) {
                $api_users[ $email ] = $record;
            }
        }

        // --- Upsert: create or update each API user in WordPress ---
        foreach ( $api_users as $email => $record ) {
            $wp_user = get_user_by( 'email', $email );

            // Fallback: look up by employee_key meta in case the email changed.
            if ( ! $wp_user && ! empty( $record['EmployeeKey'] ) ) {
                $users_by_key = get_users( [
                    'meta_key'   => self::EMPLOYEE_KEY_META,
                    'meta_value' => $record['EmployeeKey'],
                    'number'     => 1,
                ] );
                if ( ! empty( $users_by_key ) ) {
                    $wp_user = $users_by_key[0];
                    $log( sprintf( 'Matched user %s by employee_key (email changed from %s).', $email, $wp_user->user_email ) );
                }
            }

            if ( $wp_user ) {
                $data       = self::build_update_data( $record );
                $user_login = $data['user_login'];
                $nicename   = self::derive_nicename( $user_login );

                // Remove user_login and user_nicename from the wp_update_user
                // payload — WordPress runs uniqueness checks on both fields that
                // append a '-2' suffix on repeated runs. We force-write them
                // directly below instead.
                unset( $data['user_login'], $data['user_nicename'] );
                $data['ID'] = $wp_user->ID;
                $result     = wp_update_user( $data );

                if ( is_wp_error( $result ) ) {
                    $log( sprintf( 'Failed to update user %s: %s', $email, $result->get_error_message() ) );
                    $stats['errors']++;
                } else {
                    // Force user_login and user_nicename to the correct values,
                    // bypassing WordPress's uniqueness suffix logic.
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->users,
                        [
                            'user_login'    => $user_login,
                            'user_nicename' => $nicename,
                        ],
                        [ 'ID' => $wp_user->ID ],
                        [ '%s', '%s' ],
                        [ '%d' ]
                    );

                    self::save_user_meta( $wp_user->ID, $record );

                    // If this user was previously soft-deleted but has returned to
                    // the Workday feed within the grace period, cancel the deletion.
                    if ( get_user_meta( $wp_user->ID, self::DELETED_AT_META_KEY, true ) ) {
                        delete_user_meta( $wp_user->ID, self::DELETED_AT_META_KEY );
                        $log( sprintf( 'Reinstated user: %s (returned to Workday feed, deletion cancelled).', $email ) );
                    }

                    $stats['updated']++;
                }
            } else {
                $data  = self::build_insert_data( $record );
                $login = $data['user_login'];

                // If the derived login is already taken by another user, append a counter.
                if ( username_exists( $login ) ) {
                    $i = 2;
                    while ( username_exists( $login . $i ) ) {
                        $i++;
                    }
                    $data['user_login'] = $login . $i;
                }

                $data['user_pass'] = wp_generate_password( 24, true, true );
                $data['role']      = 'subscriber';
                $result            = wp_insert_user( $data );

                if ( is_wp_error( $result ) ) {
                    $log( sprintf( 'Failed to create user %s: %s', $email, $result->get_error_message() ) );
                    $stats['errors']++;
                } else {
                    self::save_user_meta( $result, $record );
                    $log( sprintf( 'Created user: %s', $email ) );
                    $stats['created']++;
                }
            }
        }

        // --- Offboard: soft-delete or hard-delete users absent from the API ---
        $wp_users           = get_users( [ 'fields' => [ 'ID', 'user_email', 'user_login' ] ] );
        $former_employee_id = null; // Resolved lazily on first hard-delete.

        foreach ( $wp_users as $wp_user ) {
            $email = strtolower( $wp_user->user_email );

            if ( isset( $api_users[ $email ] ) ) {
                continue; // Present in API — already handled above.
            }

            // Preserve users whose login is in the protected list.
            if ( in_array( $wp_user->user_login, self::PROTECTED_LOGINS, true ) ) {
                $stats['skipped']++;
                continue;
            }

            // Preserve administrators.
            $user_obj = get_userdata( $wp_user->ID );
            if ( $user_obj && in_array( 'administrator', (array) $user_obj->roles, true ) ) {
                $stats['skipped']++;
                continue;
            }

            $deleted_at = get_user_meta( $wp_user->ID, self::DELETED_AT_META_KEY, true );

            if ( $deleted_at ) {
                // Phase 2: hard-delete if ENABLE_DELETE and grace period elapsed.
                if ( self::ENABLE_DELETE ) {
                    $former_employee_id ??= self::get_or_create_former_employee_user();
                    if ( self::maybe_hard_delete_user( $wp_user->ID, $email, $deleted_at, $former_employee_id, $log ) ) {
                        $stats['deleted']++;
                    }
                }
                continue;
            }

            // Phase 1: first sync absent from API — soft-delete (timestamp flag only).
            self::soft_delete_user( $wp_user->ID, $email, $log );
            $stats['soft_deleted']++;
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Data for wp_insert_user (new users).
     *
     * Sets user_nicename via derive_nicename() — dots in the email prefix are
     * replaced with hyphens, e.g. "natalie.cadwallader" → "natalie-cadwallader".
     *
     * @param array<string, string> $record
     * @return array<string, string>
     */
    private static function build_insert_data( array $record ): array {
        $data   = self::build_common_fields( $record );
        $prefix = $data['user_login']; // already lowercased email prefix

        $data['user_nicename'] = self::derive_nicename( $prefix );
        return $data;
    }

    /**
     * Data for wp_update_user (existing users).
     * The caller re-derives user_nicename from the email prefix on every sync
     * so any stale slug (e.g. with a WordPress-appended "-2" suffix) is corrected.
     *
     * @param array<string, string> $record
     * @return array<string, string>
     */
    private static function build_update_data( array $record ): array {
        return self::build_common_fields( $record );
    }

    /**
     * Fields shared by both insert and update.
     *
     * Mapping:
     *   user_login   = email prefix (part before "@"), e.g. "natalie.cadwallader"
     *   user_email   = Email
     *   display_name = EmployeeName
     *
     * @param array<string, string> $record
     * @return array<string, string>
     */
    private static function build_common_fields( array $record ): array {
        $email      = strtolower( trim( $record['Email'] ?? '' ) );
        $name       = trim( $record['EmployeeName'] ?? '' );
        $name_parts = explode( ' ', $name, 2 );
        $prefix     = (string) strstr( $email, '@', true );

        return [
            'user_login'   => $prefix,
            'user_email'   => $email,
            'display_name' => $name,
            'first_name'   => $name_parts[0] ?? '',
            'last_name'    => $name_parts[1] ?? '',
        ];
    }

    /**
     * Derives user_nicename from the email prefix.
     *
     * Dots are replaced with hyphens so the slug is URL-safe.
     * e.g. "natalie.cadwallader" → "natalie-cadwallader"
     */
    private static function derive_nicename( string $prefix ): string {
        return str_replace( '.', '-', $prefix );
    }

    /**
     * Persist Workday-sourced fields as user meta.
     *
     * Team is stored with any trailing "(Manager Name)" suffix stripped, e.g.
     * "Procurement Liverpool (Natalie Kenyon)" becomes "Procurement Liverpool".
     *
     * @param array<string, string|int> $record
     */
    private static function save_user_meta( int $user_id, array $record ): void {
        $team = trim( (string) ( $record['Team'] ?? '' ) );
        // Strip trailing "(anything)" — the manager name appended by Workday.
        $team = trim( preg_replace( '/\s*\([^)]*\)\s*$/', '', $team ) );

        $meta = [
            self::WORKDAY_ID_META_KEY => $record['ItemInternalId'] ?? '',
            self::EMPLOYEE_KEY_META   => $record['EmployeeKey']    ?? '',
            'job_title'               => $record['JobTitle']       ?? '',
            'team'                    => $team,
            'directorate'             => $record['Directorate']    ?? '',
            'manager'                 => $record['Manager']        ?? '',
            'manager_email'           => $record['ManagerEmail']   ?? '',
        ];

        foreach ( $meta as $key => $value ) {
            update_user_meta( $user_id, $key, $value );
        }
    }

    /**
     * Returns the ID of the permanent "Former Employee" system user, creating
     * it if it does not yet exist.
     *
     * @throws RuntimeException If the user cannot be created.
     */
    private static function get_or_create_former_employee_user(): int {
        $user = get_user_by( 'login', self::FORMER_EMPLOYEE_LOGIN );
        if ( $user && isset( $user->ID ) ) {
            return (int) $user->ID;
        }

        $result = wp_insert_user( [
            'user_login'   => self::FORMER_EMPLOYEE_LOGIN,
            'user_email'   => self::FORMER_EMPLOYEE_EMAIL,
            'display_name' => 'Former Employee',
            'first_name'   => 'Former',
            'last_name'    => 'Employee',
            'user_pass'    => wp_generate_password( 32, true, true ),
            'role'         => 'subscriber',
        ] );

        if ( is_wp_error( $result ) ) {
            throw new RuntimeException(
                'Could not create former-employee system user: ' . $result->get_error_message()
            );
        }

        return $result;
    }

    /**
     * Phase 1 offboard: record the soft-delete timestamp. The user account,
     * posts, and directory entry are left unchanged until hard-delete.
     */
    private static function soft_delete_user( int $user_id, string $email, callable $log ): void {
        update_user_meta( $user_id, self::DELETED_AT_META_KEY, current_time( 'mysql' ) );
        $log( sprintf(
            'Soft-deleted user: %s (will be hard-deleted after %d days).',
            $email,
            self::SOFT_DELETE_GRACE_DAYS
        ) );
    }

    /**
     * Phase 2 offboard: reassign content, clear staff meta, then permanently
     * delete the user. Returns true if the user was deleted, false if the
     * grace period has not yet elapsed.
     */
    private static function maybe_hard_delete_user(
        int      $user_id,
        string   $email,
        string   $deleted_at,
        int      $former_employee_id,
        callable $log
    ): bool {
        $days = (int) floor( ( time() - (int) strtotime( $deleted_at ) ) / DAY_IN_SECONDS );

        if ( $days < self::SOFT_DELETE_GRACE_DAYS ) {
            return false;
        }

        global $wpdb;

        // Reassign posts (blog, work_update, standard post, etc.) to the former-employee user.
        $wpdb->update(
            $wpdb->posts,
            [ 'post_author' => $former_employee_id ],
            [ 'post_author' => $user_id ],
            [ '%d' ],
            [ '%d' ]
        );

        // Reassign comments so they display under the Former Employee account.
        $wpdb->update(
            $wpdb->comments,
            [
                'user_id'              => $former_employee_id,
                'comment_author'       => 'Former Employee',
                'comment_author_email' => self::FORMER_EMPLOYEE_EMAIL,
            ],
            [ 'user_id' => $user_id ],
            [ '%d', '%s', '%s' ],
            [ '%d' ]
        );

        // Clear Workday staff meta so no stale data lingers.
        foreach ( [
            self::WORKDAY_ID_META_KEY,
            self::EMPLOYEE_KEY_META,
            'job_title',
            'team',
            'directorate',
            'manager',
            'manager_email',
        ] as $key ) {
            update_user_meta( $user_id, $key, '' );
        }

        wp_delete_user( $user_id );
        $log( sprintf( 'Hard-deleted user: %s (%d days since soft-delete).', $email, $days ) );

        return true;
    }
}
