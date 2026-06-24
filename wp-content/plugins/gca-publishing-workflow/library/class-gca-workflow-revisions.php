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
        add_action( 'admin_init', [ __CLASS__, 'seed_revision_options' ] );

        // Grant contributors copy_post permission on pages they can edit (i.e. in their
        // directorate). rvy_is_full_editor() reads allcaps directly, bypassing our
        // user_has_cap filter, so we must use the revisionary_can_copy hook instead.
        add_filter( 'revisionary_can_copy', [ __CLASS__, 'grant_contributor_copy' ], 10, 2 );

        // Include draft pages in the revision queue — by default PublishPress only
        // shows revisions of public/private (published) posts in the pending queue.
        add_filter( 'revisionary_main_post_statuses', [ __CLASS__, 'include_draft_in_queue_statuses' ] );

        // Contributors must not see Approve/Decline/Publish actions — they can only submit.
        add_filter( 'revisionary_bulk_action_approval', [ __CLASS__, 'deny_contributor_bulk_approval' ] );
        add_filter( 'revisionary_queue_row_actions',    [ __CLASS__, 'remove_contributor_row_approval_actions' ], 10, 2 );

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

    public static function seed_revision_options(): void {
        if ( false === get_option( 'rvy_post_types' ) ) {
            update_option( 'rvy_post_types', [ 'page' => 1 ] );
        }

        // rvy_get_option() reads the DB directly — the pp_revisions_option_* filter
        // is only respected by rvy_filter_option(), which the block editor never calls.
        // Writing to the option is the only reliable way to enable draft-page revisions.
        if ( ! get_option( 'rvy_pending_revision_unpublished' ) ) {
            update_option( 'rvy_pending_revision_unpublished', 1 );
        }
    }

    public static function include_draft_in_queue_statuses( array $statuses ): array {
        if ( ! in_array( 'draft', $statuses, true ) ) {
            $statuses[] = 'draft';
        }
        return $statuses;
    }

    public static function grant_contributor_copy( bool $can_copy, int $post_id ): bool {
        if ( $can_copy ) {
            return true;
        }

        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return false;
        }

        // current_user_can goes through our user_has_cap filter which handles
        // the directorate scope check — unlike rvy_is_full_editor() which reads
        // allcaps directly and misses dynamically granted edit_others_pages.
        return current_user_can( 'edit_post', $post_id );
    }

    public static function deny_contributor_bulk_approval( bool $can_approve ): bool {
        if ( GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return false;
        }
        return $can_approve;
    }

    public static function remove_contributor_row_approval_actions( array $actions, WP_Post $post ): array {
        if ( GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            unset( $actions['approve'], $actions['approve_revision'], $actions['decline'], $actions['decline_revision'], $actions['publish'], $actions['publish_revision'] );
        }
        return $actions;
    }

    public static function hide_revisions_ui_for_non_admins(): void {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        // Always hide the native WP revision history — PublishPress handles the workflow.
        foreach ( get_post_types( [ 'public' => true ] ) as $post_type ) {
            remove_meta_box( 'revisionsdiv', $post_type, 'normal' );
        }

        $is_contributor = GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR );

        if ( $is_contributor ) {
            // Contributors need the "Create Revision" button to propose changes on draft pages.
            echo '<style>.editor-last-revision{display:none!important}</style>';
        } else {
            // Publishers publish directly — the revision creation UI is not needed.
            echo '<style>.editor-last-revision,.rvy-creation-ui{display:none!important}</style>';
        }
    }

}
