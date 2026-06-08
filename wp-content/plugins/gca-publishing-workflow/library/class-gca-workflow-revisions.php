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

        // Hide native WP revision history UI from non-admin users — PublishPress
        // Revisions handles the workflow, so the native UI is redundant and confusing.
        add_action( 'admin_head', [ __CLASS__, 'hide_revisions_ui_for_non_admins' ] );

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

    public static function hide_revisions_ui_for_non_admins(): void {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }
        foreach ( get_post_types( [ 'public' => true ] ) as $post_type ) {
            remove_meta_box( 'revisionsdiv', $post_type, 'normal' );
        }
        // .rvy-creation-ui = "Create Revision" button injected by Revisionary
        echo '<style>.editor-last-revision,.rvy-creation-ui{display:none!important}</style>';
    }

}
