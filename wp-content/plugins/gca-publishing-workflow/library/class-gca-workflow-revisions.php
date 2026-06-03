<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Revisions {

    public static function init(): void {
        // Limit PublishPress Revisions to pages only.
        add_filter( 'revisionary_post_types', [ __CLASS__, 'limit_to_pages' ] );

        // After a revision is approved, ensure post_modified is refreshed.
        add_action( 'rvy_after_revision_approve', [ __CLASS__, 'refresh_modified_date' ], 10, 2 );

        // Seed the rvy_post_types option so the admin UI reflects our restriction.
        // Only runs when the option is not yet set to avoid overwriting admin changes.
        add_action( 'admin_init', [ __CLASS__, 'seed_revision_post_types_option' ] );
    }

    public static function limit_to_pages( array $post_types ): array {
        return array_intersect_key( $post_types, [ 'page' => true ] );
    }

    public static function refresh_modified_date( int $post_id, int $revision_id ): void {
        wp_update_post( [
            'ID'                => $post_id,
            'post_modified'     => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', true ),
        ] );
    }

    public static function seed_revision_post_types_option(): void {
        if ( false === get_option( 'rvy_post_types' ) ) {
            update_option( 'rvy_post_types', [ 'page' => 1 ] );
        }
    }
}
