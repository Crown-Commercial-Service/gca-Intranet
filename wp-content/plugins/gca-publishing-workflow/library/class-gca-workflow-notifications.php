<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Notifications {

    public static function init(): void {
        add_action( 'transition_post_status', [ __CLASS__, 'on_status_transition' ], 10, 3 );
        add_action( 'gca_workflow_page_rejected', [ __CLASS__, 'on_page_rejected' ], 10, 3 );
        // Intercept trash attempts by contributors before WordPress processes the cap check.
        // This is necessary because when delete_pages is denied, wp_trash_post never fires.
        add_action( 'admin_init', [ __CLASS__, 'intercept_contributor_retirement_request' ] );
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    public static function on_status_transition( string $new_status, string $old_status, WP_Post $post ): void {
        if ( 'page' !== $post->post_type ) {
            return;
        }
        if ( $new_status === $old_status ) {
            return;
        }

        if ( 'pending' === $new_status ) {
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
        if ( ! $post || 'page' !== $post->post_type ) {
            return;
        }

        $author = get_userdata( (int) $post->post_author );
        if ( ! $author || ! $author->user_email ) {
            return;
        }

        $page_title    = get_the_title( $post );
        $edit_link     = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
        $reviewer      = get_userdata( $reviewer_id );
        $reviewer_name = $reviewer ? $reviewer->display_name : 'A reviewer';

        $subject = sprintf( 'Your page "%s" has been returned with feedback', $page_title );
        $body    = sprintf(
            "%s has reviewed your page and returned it for changes.\n\nPage: %s\n\nReviewer feedback:\n\n%s\n\nPlease log in to update your page and re-submit for review:\n%s",
            $reviewer_name,
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

    private static function handle_pending( WP_Post $post ): void {
        $reviewer_email = get_option( GCA_Workflow_Settings::OPTION_REVIEWER_EMAIL, '' );
        if ( ! $reviewer_email ) {
            return;
        }

        $is_revision  = (bool) wp_is_post_revision( $post->ID );
        $page_title   = $is_revision
            ? get_the_title( wp_get_post_parent_id( $post->ID ) )
            : get_the_title( $post );
        $admin_link   = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );

        if ( $is_revision ) {
            $subject = sprintf( 'Update submitted for %s for review', $page_title );
            $body    = sprintf(
                "A revision of the page \"%s\" has been submitted for review.\n\nReview the changes here:\n%s",
                $page_title,
                $admin_link
            );
        } else {
            $subject = sprintf( 'New page submitted for review: %s', $page_title );
            $body    = sprintf(
                "A page has been submitted for review.\n\nPage: %s\n\nReview it here:\n%s",
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

        $page_title = get_the_title( $post );
        $page_url   = get_permalink( $post->ID );

        $subject = sprintf( 'Your page "%s" is now live', $page_title );
        $body    = sprintf(
            "Your page has been reviewed and published.\n\nPage: %s\nURL: %s",
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
        $page_title   = get_the_title( $post );
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
