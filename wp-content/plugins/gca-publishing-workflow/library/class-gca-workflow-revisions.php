<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Revisions {

    public static function init(): void {
        // Limit PublishPress Revisions to the post types contributors can edit.
        // NOTE: the correct hook is 'revisionary_enabled_post_types' — applied inside
        // Revisionary_Main::setPostTypes(). ('revisionary_post_types', used here
        // previously, doesn't exist anywhere in the plugin; it silently did nothing,
        // and "Pages only" worked purely by accident because Revisionary's own
        // default auto-detection only picks up post types with a distinct
        // capability_type, which none of our custom post types have.)
        add_filter( 'revisionary_enabled_post_types', [ __CLASS__, 'limit_to_workflow_post_types' ] );

        // After a revision is approved, ensure post_modified is refreshed.
        // NOTE: the correct hook is 'revision_approved' — fired in
        // admin/revision-action_rvy.php as do_action('revision_approved', $post->ID,
        // $revision->ID). ('rvy_after_revision_approve' doesn't exist in the plugin;
        // this callback was silently never firing. WF-3.6 still passed only because
        // WordPress's own post_modified update on merge happened to cover it anyway.)
        add_action( 'revision_approved', [ __CLASS__, 'refresh_modified_date' ], 10, 2 );

        // Grant contributors copy_post permission on pages they can edit (i.e. in their
        // directorate). rvy_is_full_editor() reads allcaps directly, bypassing our
        // user_has_cap filter, so we must use the revisionary_can_copy hook instead.
        add_filter( 'revisionary_can_copy', [ __CLASS__, 'grant_contributor_copy' ], 10, 2 );

        // Contributors must not see Approve/Decline/Publish actions — they can only submit.
        add_filter( 'revisionary_bulk_action_approval', [ __CLASS__, 'deny_contributor_bulk_approval' ] );
        add_filter( 'revisionary_queue_row_actions',    [ __CLASS__, 'remove_contributor_row_approval_actions' ], 10, 2 );

        // Hide native WP revision history UI from non-admin users — PublishPress
        // Revisions handles the workflow, so the native UI is redundant and confusing.
        add_action( 'admin_head', [ __CLASS__, 'hide_revisions_ui_for_non_admins' ] );

        // Rename "Update Revision" → "Save Revision" for contributors to avoid confusion
        // with the "Submit Revision" button.
        add_filter( 'gettext', [ __CLASS__, 'rename_update_revision_button' ], 10, 3 );

        // Only needs to disable a stray legacy option — see seed_revision_options().
        add_action( 'admin_init', [ __CLASS__, 'seed_revision_options' ] );

        // Quick Edit can't touch post_content (WordPress discards whatever's
        // submitted there and re-reads the current value from the DB before
        // saving), but it CAN change title/slug/categories/etc. on an already-live
        // post immediately, with no revision and no review — bypassing the one
        // rule this whole plugin exists to enforce. Hide the link for contributors
        // on already-published content...
        add_filter( 'post_row_actions', [ __CLASS__, 'remove_contributor_quick_edit_on_live_content' ], 10, 2 );
        add_filter( 'page_row_actions', [ __CLASS__, 'remove_contributor_quick_edit_on_live_content' ], 10, 2 );

        // The full editor screen has the exact same problem: PublishPress decides
        // whether to show its own "Create Revision" UI *alongside* (not instead
        // of) WordPress's native Update button based on rvy_is_full_editor(),
        // which reads $current_user->allcaps directly — bypassing our per-post
        // user_has_cap filters entirely. Contributors have base edit_published_pages
        // (needed just to open a live page at all and propose a revision), which is
        // enough to clear that check, so they see both buttons — and clicking the
        // native Update saves straight to the live post, no review, no revision.
        // Hide that button for them on already-published content...
        add_action( 'admin_head', [ __CLASS__, 'hide_contributor_update_button_on_live_content' ] );

        // ...and back BOTH of these with one real server-side block, since hiding a
        // button/link is cosmetic — someone could still submit the same request
        // directly. This does not affect the actual Create Revision flow (that
        // creates a separate revision post via its own AJAX action, never writing
        // to the live post's row) or the publisher's merge-on-approve (different
        // user, not a contributor).
        add_filter( 'wp_insert_post_data', [ __CLASS__, 'block_contributor_direct_edit_of_live_content' ], 10, 2 );

        // rvy_revision_approve()/rvy_revision_publish() only require EITHER a
        // dedicated 'approve_revision' capability (which contributors don't have)
        // OR plain current_user_can('edit_post', $live_post_id) — and contributors
        // DO have edit rights on live posts (needed just to propose a revision at
        // all), so that second branch lets them approve/publish their own or
        // anyone's pending revision outright. Must intercept on 'wp_loaded' at an
        // early priority, NOT 'admin_init' — Revisionary schedules its own approve
        // handler via add_action('wp_loaded', ...) at the default priority, and
        // 'wp_loaded' fires (and, on a nonce failure or successful approval, can
        // wp_die()/exit) BEFORE 'admin_init' ever gets a chance to run.
        add_action( 'wp_loaded', [ __CLASS__, 'block_contributor_revision_approval' ], 5 );
    }

    /**
     * Replaces (not merges with) Revisionary's own auto-detected default set —
     * that default only ever picks up 'post' and 'page' plus any post type with
     * a distinct capability_type, which none of our custom post types have, so
     * merging would never actually add Blog/News/Event/Work Update.
     */
    public static function limit_to_workflow_post_types( array $post_types ): array {
        return array_fill_keys( GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true );
    }

    public static function remove_contributor_quick_edit_on_live_content( array $actions, WP_Post $post ): array {
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return $actions;
        }
        if ( ! in_array( $post->post_type, GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return $actions;
        }
        if ( 'publish' === $post->post_status ) {
            unset( $actions['inline hide-if-no-js'] );
        }
        return $actions;
    }

    /**
     * Covers both Quick Edit's AJAX save and the full editor's native Update
     * button — both write directly to the live post's own row via this same
     * filter. The legitimate Create Revision flow never hits this: it creates
     * a separate revision post (a fresh insert, so $postarr['ID'] isn't yet an
     * already-'publish' post), and further edits to that revision update ITS
     * own row, which carries a 'draft'/'pending' status, never 'publish'.
     */
    public static function block_contributor_direct_edit_of_live_content( array $data, array $postarr ): array {
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return $data;
        }
        if ( ! in_array( $data['post_type'], GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return $data;
        }
        if ( empty( $postarr['ID'] ) || 'publish' !== get_post_status( $postarr['ID'] ) ) {
            return $data;
        }
        wp_die( esc_html__( 'Live content can only be changed by submitting a revision for review.', 'gca' ) );
    }

    public static function block_contributor_revision_approval(): void {
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        $page   = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
        $action = isset( $_REQUEST['action'] ) && ! is_numeric( $_REQUEST['action'] )
            ? sanitize_key( wp_unslash( $_REQUEST['action'] ) )
            : ( isset( $_REQUEST['action2'] ) ? sanitize_key( wp_unslash( $_REQUEST['action2'] ) ) : '' );

        if ( 'rvy-revisions' !== $page && 'revisionary-q' !== $page ) {
            return;
        }

        if ( in_array( $action, [ 'approve', 'publish', 'approve_revision', 'publish_revision' ], true ) ) {
            wp_die( esc_html__( 'Only a publisher can approve a revision.', 'gca' ) );
        }
    }

    public static function refresh_modified_date( int $post_id, int $revision_id ): void {
        wp_update_post( [
            'ID'                => $post_id,
            'post_modified'     => current_time( 'mysql' ),
            'post_modified_gmt' => current_time( 'mysql', true ),
        ] );
    }

    public static function seed_revision_options(): void {
        // Deliberately do NOT enable 'rvy_pending_revision_unpublished'. That option
        // makes PublishPress show its "Create Revision" UI on posts that have never
        // been published — but a page that's never been live has nothing to revise;
        // the correct action there is always "Submit for Review" (the Draft → Review
        // → Publish pipeline). Revisions only apply once a page is genuinely live,
        // where post_status is already 'publish'/'private' and this option isn't
        // needed at all. If it was left on from an earlier version, turn it back off.
        if ( get_option( 'rvy_pending_revision_unpublished' ) ) {
            update_option( 'rvy_pending_revision_unpublished', 0 );
        }
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
        $user_id = get_current_user_id();

        if ( GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            unset( $actions['approve'], $actions['approve_revision'], $actions['decline'], $actions['decline_revision'], $actions['publish'], $actions['publish_revision'] );
            return $actions;
        }

        // Add an Approve row action for publishers — PublishPress Free omits this by default.
        // We append it to the decline action string rather than using a separate key,
        // because WP's row_actions() hides unknown span classes in some admin themes.
        if ( isset( $actions['decline'] )
             && 'pending-revision' === $post->post_mime_type
             && ( GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
                  || current_user_can( 'manage_options' ) )
             && (int) $post->comment_count ) {
            $parent_id = (int) $post->comment_count;
            $nonce     = wp_create_nonce( "approve-post_{$parent_id}|{$post->ID}" );
            $url       = admin_url( "admin.php?page=rvy-revisions&action=approve&revision={$post->ID}&_wpnonce={$nonce}" );
            $actions['decline'] .= ' | <a href="' . esc_url( $url ) . '">' . esc_html__( 'Approve' ) . '</a>';
        }

        return $actions;
    }

    public static function hide_contributor_update_button_on_live_content(): void {
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'post' !== $screen->base ) {
            return;
        }
        if ( ! in_array( $screen->post_type, GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return;
        }
        $post = get_post();
        if ( ! $post || 'publish' !== $post->post_status ) {
            return;
        }
        // #publish is WordPress's native Update button — Create Revision (.rvy-creation-ui)
        // is a separate element PublishPress injects alongside it and stays visible.
        echo '<style>#publishing-action #publish{display:none!important}</style>';
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
            // Contributors need the "Create Revision"/"Submit" UI on already-live
            // pages (WF-3.x) — but any "Approve Revision" button (shown both when
            // viewing an already-submitted pending revision, class 'revision-approve',
            // AND on an unsubmitted draft-revision before its first save, class
            // 'rvy-direct-approve' — two different classes for the same action) must
            // never be available to them; only a publisher approves. The first shares
            // .rvy-creation-ui with Create Revision, distinguished by lacking the
            // 'revision-create' class. See block_contributor_revision_approval() for
            // the real enforcement — this is UI-only.
            echo '<style>.editor-last-revision{display:none!important}a.revision-approve:not(.revision-create),a.rvy-direct-approve{display:none!important}</style>';
        } else {
            // Publishers publish directly, so the "Create Revision" button on a
            // live post's own edit screen isn't needed — but that button and the
            // "Approve Revision" button (shown when reviewing a pending revision)
            // share the exact same wrapper, .rvy-creation-ui. Hiding the whole
            // wrapper here was hiding Approve Revision too. Target only the
            // Create-Revision button itself (class 'revision-create', absent
            // from the Approve button) instead.
            echo '<style>.editor-last-revision{display:none!important}.rvy-creation-ui .revision-create{display:none!important}</style>';
        }
    }

    public static function rename_update_revision_button( string $translation, string $text, string $domain ): string {
        if ( 'revisionary' === $domain && 'Update Revision' === $text ) {
            $user_id = get_current_user_id();
            if ( GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
                return 'Save Revision';
            }
        }
        return $translation;
    }

}
