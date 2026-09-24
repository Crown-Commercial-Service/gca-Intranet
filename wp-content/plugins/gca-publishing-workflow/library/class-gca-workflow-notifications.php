<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Notifications {

    public static function init(): void {
        add_action( 'transition_post_status', [ __CLASS__, 'on_status_transition' ], 10, 3 );
        add_action( 'revision_submitted', [ __CLASS__, 'on_revision_submitted' ], 10, 2 );
        add_action( 'gca_workflow_page_rejected', [ __CLASS__, 'on_page_rejected' ], 10, 3 );
        // Intercept trash attempts by contributors before WordPress processes the cap check.
        // This is necessary because when delete_pages is denied, wp_trash_post never fires.
        add_action( 'admin_init', [ __CLASS__, 'intercept_contributor_retirement_request' ] );
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    public static function on_status_transition( string $new_status, string $old_status, WP_Post $post ): void {
        if ( ! in_array( $post->post_type, GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return;
        }
        if ( $new_status === $old_status ) {
            return;
        }

        if ( 'pending' === $new_status || 'pending-revision' === $new_status ) {
            self::handle_pending( $post );
            return;
        }

        if ( 'publish' === $new_status && 'pending' === $old_status ) {
            self::notify_contributor_published( $post );
            return;
        }

        // Clear rejection comments when contributor re-submits (draft → pending handled above,
        // but also clear on any transition away from draft to avoid stale feedback).
        if ( 'draft' === $old_status && 'pending' === $new_status ) {
            delete_post_meta( $post->ID, GCA_Workflow_Rejection::META_KEY );
        }
    }

    public static function on_page_rejected( int $post_id, int $reviewer_id, string $comments ): void {
        $post = get_post( $post_id );
        if ( ! $post || ! in_array( $post->post_type, GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return;
        }

        $author = get_userdata( (int) $post->post_author );
        if ( ! $author || ! $author->user_email ) {
            return;
        }

        $page_title    = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
        $type_label    = self::content_type_label( $post );
        $edit_link     = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
        $reviewer      = get_userdata( $reviewer_id );
        $reviewer_name = $reviewer ? $reviewer->display_name : 'A reviewer';

        $subject = sprintf( 'Your %s "%s" has been returned with feedback', $type_label, $page_title );
        $body    = sprintf(
            "%s has reviewed your %s and returned it for changes.\n\n%s: %s\n\nReviewer feedback:\n\n%s\n\nPlease log in to update it and re-submit for review:\n%s",
            $reviewer_name,
            $type_label,
            ucfirst( $type_label ),
            $page_title,
            $comments,
            $edit_link
        );

        $headers = [];
        if ( $reviewer && $reviewer->user_email ) {
            $headers[] = sprintf( 'Cc: %s <%s>', $reviewer->display_name, $reviewer->user_email );
            $headers[] = sprintf( 'Reply-To: %s <%s>', $reviewer->display_name, $reviewer->user_email );
        }

        wp_mail( $author->user_email, $subject, $body, $headers );
    }

    /**
     * Intercepts a contributor's attempt to trash any page from the admin UI.
     *
     * WordPress never fires wp_trash_post when delete_pages is denied, so we hook
     * admin_init early — before the cap check — to send the retirement notification
     * and redirect with a user-friendly notice instead of a "Cheatin'" error.
     */
    public static function intercept_contributor_retirement_request(): void {
        if ( ! isset( $_GET['action'], $_GET['post'] ) || 'trash' !== $_GET['action'] ) {
            return;
        }

        $post_id = absint( $_GET['post'] );
        if ( ! $post_id ) {
            return;
        }

        $post = get_post( $post_id );
        if ( ! $post || 'page' !== $post->post_type ) {
            return;
        }

        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        // Verify the nonce WordPress would check itself.
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'trash-post_' . $post_id ) ) {
            return;
        }

        self::notify_retirement_request( $post );

        wp_safe_redirect( add_query_arg(
            [ 'post_type' => 'page', 'gca_retirement_requested' => '1' ],
            admin_url( 'edit.php' )
        ) );
        exit;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Lowercase singular label for use mid-sentence in notification copy,
     * e.g. "page", "blog", "work update".
     */
    private static function content_type_label( WP_Post $post ): string {
        $object = get_post_type_object( $post->post_type );
        return $object ? strtolower( $object->labels->singular_name ) : 'page';
    }

    public static function on_revision_submitted( int $published_id, int $revision_id ): void {
        $published_post = get_post( $published_id );
        if ( ! $published_post || ! in_array( $published_post->post_type, GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return;
        }

        $revision = get_post( $revision_id );
        if ( $revision ) {
            self::handle_pending( $revision );
        }
    }

    private static function handle_pending( WP_Post $post ): void {
        static $processed = [];
        if ( isset( $processed[ $post->ID ] ) ) {
            return;
        }
        $processed[ $post->ID ] = true;

        $reviewer_email = get_option( GCA_Workflow_Settings::OPTION_REVIEWER_EMAIL, '' );
        if ( ! $reviewer_email ) {
            return;
        }

        // PublishPress Revisions uses '-revision' mime types instead of 'revision' post_type.
        $is_pp_revision = strpos( (string) $post->post_mime_type, '-revision' ) !== false;
        $is_revision    = $is_pp_revision || wp_is_post_revision( $post->ID );

        $parent_id = 0;
        if ( $is_pp_revision ) {
            // PublishPress stores the parent post ID in comment_count for revisions.
            $parent_id = (int) $post->comment_count;
        } elseif ( $is_revision ) {
            $parent_id = wp_get_post_parent_id( $post->ID );
        }

        $page_title   = $parent_id
            ? get_the_title( $parent_id )
            : get_the_title( $post );
        $page_title   = html_entity_decode( $page_title, ENT_QUOTES, 'UTF-8' );
        $admin_link   = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );

        if ( $is_revision ) {
            $subject = sprintf( 'Update submitted for %s for review', $page_title );
            $body    = sprintf(
                "A revision of the page \"%s\" has been submitted for review.\n\nReview the changes here:\n%s",
                $page_title,
                $admin_link
            );
        } else {
            $type_label = self::content_type_label( $post );
            $subject    = sprintf( 'New %s submitted for review: %s', $type_label, $page_title );
            $body       = sprintf(
                "A %s has been submitted for review.\n\n%s: %s\n\nReview it here:\n%s",
                $type_label,
                ucfirst( $type_label ),
                $page_title,
                $admin_link
            );
        }

        // Clear any stale rejection comments when re-submitting.
        delete_post_meta( $post->ID, GCA_Workflow_Rejection::META_KEY );

        wp_mail( $reviewer_email, $subject, $body );
    }

    private static function notify_contributor_published( WP_Post $post ): void {
        $author = get_userdata( (int) $post->post_author );
        if ( ! $author || ! $author->user_email ) {
            return;
        }

        $page_title = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
        $page_url   = get_permalink( $post->ID );
        $type_label = self::content_type_label( $post );

        $subject = sprintf( 'Your %s "%s" is now live', $type_label, $page_title );
        $body    = sprintf(
            "Your %s has been reviewed and published.\n\n%s: %s\nURL: %s",
            $type_label,
            ucfirst( $type_label ),
            $page_title,
            $page_url ?: admin_url( 'post.php?post=' . $post->ID . '&action=edit' )
        );

        wp_mail( $author->user_email, $subject, $body );
    }

    public static function notify_retirement_request( WP_Post $post ): void {
        $reviewer_email = get_option( GCA_Workflow_Settings::OPTION_REVIEWER_EMAIL, '' );
        if ( ! $reviewer_email ) {
            return;
        }

        $current_user = wp_get_current_user();
        $page_title   = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
        $page_url     = get_permalink( $post->ID );
        $admin_link   = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );

        $subject = sprintf( 'Retirement request for page: %s', $page_title );
        $body    = sprintf(
            "%s has requested that the following page be retired:\n\nPage: %s\nStatus: %s%s\n\nPlease log in to review this request:\n%s",
            $current_user->display_name ?: 'A contributor',
            $page_title,
            ucfirst( $post->post_status ),
            $page_url ? "\nURL: " . $page_url : '',
            $admin_link
        );

        wp_mail( $reviewer_email, $subject, $body );
    }
}
