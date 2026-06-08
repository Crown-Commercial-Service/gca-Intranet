<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Rejection {

    const META_KEY         = '_gca_workflow_rejection_comments';
    const HISTORY_META_KEY = '_gca_workflow_rejection_history';
    const NONCE_ACTION     = 'gca_rejection_comments_save';
    const NONCE_FIELD      = 'gca_rejection_nonce';

    public static function init(): void {
        add_action( 'add_meta_boxes', [ __CLASS__, 'register_meta_boxes' ] );
        add_action( 'save_post_page', [ __CLASS__, 'save_rejection_meta' ], 10, 2 );
        // Archive comments to history when a rejection is filed (fires before notifications).
        add_action( 'gca_workflow_page_rejected', [ __CLASS__, 'archive_rejection' ], 5, 3 );
        // Show admin notice when a contributor's submission is blocked from trashing a live page.
        add_action( 'admin_notices', [ __CLASS__, 'show_delete_blocked_notice' ] );
    }

    // -------------------------------------------------------------------------
    // Meta boxes
    // -------------------------------------------------------------------------

    public static function register_meta_boxes(): void {
        $user_id     = get_current_user_id();
        $is_reviewer = GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
                    || current_user_can( 'manage_options' );

        // Reviewer meta box — visible only to publishers and above.
        if ( $is_reviewer ) {
            add_meta_box(
                'gca_rejection_comments',
                'Rejection Comments',
                [ __CLASS__, 'render_reviewer_meta_box' ],
                'page',
                'normal',
                'high'
            );
        } else {
            // Contributor notice — shown only when rejection comments exist.
            add_meta_box(
                'gca_rejection_notice',
                'Reviewer Feedback',
                [ __CLASS__, 'render_contributor_notice' ],
                'page',
                'normal',
                'high'
            );
        }
    }

    public static function render_reviewer_meta_box( WP_Post $post ): void {
        $value   = get_post_meta( $post->ID, self::META_KEY, true );
        $history = get_post_meta( $post->ID, self::HISTORY_META_KEY, true );
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
        ?>
        <p class="description">Add feedback for the contributor. Click <strong>Submit Rejection</strong> to return the page to Draft status and notify the contributor.</p>
        <textarea
            name="gca_rejection_comments"
            id="gca_rejection_comments"
            rows="5"
            style="width:100%;margin-top:8px;"
        ><?php echo esc_textarea( $value ); ?></textarea>
        <input type="hidden" name="gca_submit_rejection" id="gca_submit_rejection_flag" value="" />
        <p style="margin-top:8px;">
            <button
                type="button"
                class="button button-secondary"
                onclick="if(confirm('Return this page to the contributor with your feedback?')){ document.getElementById('gca_submit_rejection_flag').value='1'; document.getElementById('post').submit(); }"
            >Submit Rejection</button>
        </p>
        <?php if ( ! empty( $history ) && is_array( $history ) ) : ?>
        <hr style="margin:16px 0;">
        <h4 style="margin:0 0 8px;">Rejection History</h4>
        <?php foreach ( array_reverse( $history ) as $entry ) :
            $reviewer      = get_userdata( $entry['reviewer_id'] );
            $reviewer_name = $reviewer ? $reviewer->display_name : 'Unknown reviewer';
            $date          = date_i18n(
                get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                $entry['timestamp']
            );
        ?>
        <div style="background:#f9f9f9;border-left:4px solid #ddd;padding:8px 12px;margin-bottom:8px;">
            <p style="margin:0 0 4px;">
                <strong><?php echo esc_html( $reviewer_name ); ?></strong>
                &mdash;
                <em><?php echo esc_html( $date ); ?></em>
            </p>
            <p style="margin:0;white-space:pre-wrap;"><?php echo esc_html( $entry['comments'] ); ?></p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php
    }

    public static function render_contributor_notice( WP_Post $post ): void {
        $comments = get_post_meta( $post->ID, self::META_KEY, true );
        if ( empty( $comments ) ) {
            // Hide the meta box entirely when there is no feedback.
            // We can't easily remove the meta box at this stage, so output nothing.
            echo '<style>#gca_rejection_notice{display:none}</style>';
            return;
        }
        ?>
        <div class="notice notice-warning inline" style="margin:0;padding:10px 12px;">
            <p><?php echo nl2br( esc_html( $comments ) ); ?></p>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Rejection history — fires on gca_workflow_page_rejected at priority 5
    // -------------------------------------------------------------------------

    public static function archive_rejection( int $post_id, int $reviewer_id, string $comments ): void {
        $history   = get_post_meta( $post_id, self::HISTORY_META_KEY, true );
        $history   = is_array( $history ) ? $history : [];
        $history[] = [
            'timestamp'   => time(),
            'reviewer_id' => $reviewer_id,
            'comments'    => $comments,
        ];
        update_post_meta( $post_id, self::HISTORY_META_KEY, $history );
    }

    // -------------------------------------------------------------------------
    // Save handler
    // -------------------------------------------------------------------------

    public static function save_rejection_meta( int $post_id, WP_Post $post ): void {
        // Bail on autosave, revisions, and missing nonce.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        if (
            ! isset( $_POST[ self::NONCE_FIELD ] ) ||
            ! wp_verify_nonce(
                sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ),
                self::NONCE_ACTION
            )
        ) {
            return;
        }
        if ( ! current_user_can( 'publish_pages', $post_id ) ) {
            return;
        }

        $comments = isset( $_POST['gca_rejection_comments'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['gca_rejection_comments'] ) )
            : '';

        update_post_meta( $post_id, self::META_KEY, $comments );

        // Handle rejection submission.
        if ( ! empty( $_POST['gca_submit_rejection'] ) && '' !== $comments ) {
            // Remove our save hook temporarily to avoid infinite loops.
            remove_action( 'save_post_page', [ __CLASS__, 'save_rejection_meta' ], 10 );

            wp_update_post( [
                'ID'          => $post_id,
                'post_status' => 'draft',
            ] );

            add_action( 'save_post_page', [ __CLASS__, 'save_rejection_meta' ], 10, 2 );

            do_action( 'gca_workflow_page_rejected', $post_id, get_current_user_id(), $comments );
        }
    }

    // -------------------------------------------------------------------------
    // Admin notice when a contributor's delete is blocked
    // -------------------------------------------------------------------------

    public static function show_delete_blocked_notice(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'edit-page' !== $screen->id ) {
            return;
        }
        if ( isset( $_GET['gca_retirement_requested'] ) ) {
            echo '<div class="notice notice-info is-dismissible"><p>'
                . esc_html__( 'Your retirement request has been sent to the reviewer group.', 'gca' )
                . '</p></div>';
            return;
        }
        if ( isset( $_GET['gca_delete_blocked'] ) ) {
            echo '<div class="notice notice-warning is-dismissible"><p>'
                . esc_html__( 'Pages cannot be deleted directly. The reviewer group has been notified of your retirement request.', 'gca' )
                . '</p></div>';
        }
    }
}
