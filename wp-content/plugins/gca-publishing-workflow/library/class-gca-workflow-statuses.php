<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Statuses {

    const ARCHIVED_STATUS = 'gca_archived';

    public static function init(): void {
        // Register the archived status early so it's available for queries and
        // admin list tables. Guards against GCA_Purge_Events not being active.
        add_action( 'init', [ __CLASS__, 'register_archived_status' ], 9 );

        // Surface gca_archived in the page editor status dropdown.
        add_filter( 'display_post_states', [ __CLASS__, 'add_archived_state_label' ], 10, 2 );

        // Prevent archived pages from appearing on the frontend.
        add_action( 'pre_get_posts', [ __CLASS__, 'exclude_archived_from_frontend' ] );
    }

    public static function register_archived_status(): void {
        if ( post_status_exists( self::ARCHIVED_STATUS ) ) {
            return;
        }
        register_post_status( self::ARCHIVED_STATUS, [
            'label'                     => _x( 'Archived', 'post status', 'gca' ),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => false,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Archived <span class="count">(%s)</span>',
                'Archived <span class="count">(%s)</span>',
                'gca'
            ),
        ] );
    }

    public static function add_archived_state_label( array $states, WP_Post $post ): array {
        if ( self::ARCHIVED_STATUS === $post->post_status ) {
            $states[ self::ARCHIVED_STATUS ] = _x( 'Archived', 'post status', 'gca' );
        }
        return $states;
    }

    public static function exclude_archived_from_frontend( WP_Query $query ): void {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }
        // Ensure archived posts/pages never appear in frontend queries.
        $statuses = (array) $query->get( 'post_status' );
        $statuses = array_diff( $statuses, [ self::ARCHIVED_STATUS ] );
        if ( empty( $statuses ) ) {
            // Default to publish only.
            $query->set( 'post_status', [ 'publish' ] );
        } else {
            $query->set( 'post_status', $statuses );
        }
    }
}
