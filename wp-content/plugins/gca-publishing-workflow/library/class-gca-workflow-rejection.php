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
        // After saving a revision with our rejection form, redirect to PublishPress's
        // decline URL so the full decline pipeline fires (including revisionary_declined).
        add_filter( 'redirect_post_location', [ __CLASS__, 'redirect_revision_decline' ] );
        // Archive comments to history when a rejection is filed (fires before notifications).
        add_action( 'gca_workflow_page_rejected', [ __CLASS__, 'archive_rejection' ], 5, 3 );
        // When a publisher declines a revision via PublishPress, fire our notification action.
        add_action( 'revisionary_declined', [ __CLASS__, 'on_revision_declined' ], 10, 3 );
        // Intercept the Decline link and redirect to a feedback form page before declining.
        add_action( 'admin_footer', [ __CLASS__, 'decline_redirect_script' ] );
        add_action( 'admin_menu',   [ __CLASS__, 'register_decline_feedback_page' ] );
        add_action( 'admin_init',   [ __CLASS__, 'handle_decline_feedback_submit' ] );
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
        }
        if ( ! $is_reviewer ) {
            add_meta_box(
                'gca_rejection_comments',
                'Rejection Feedback',
                [ __CLASS__, 'render_contributor_meta_box' ],
                'page',
                'normal',
                'high'
            );
        }
    }

    public static function show_reviewer_feedback_notice(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'page' !== $screen->id ) {
            return;
        }

        $user_id     = get_current_user_id();
        $is_reviewer = GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
                    || current_user_can( 'manage_options' );
        if ( $is_reviewer ) {
            return;
        }

        $post = get_post();
        if ( ! $post ) {
            return;
        }

        $source_id = $post->ID;
        $history   = get_post_meta( $source_id, self::HISTORY_META_KEY, true );

        if ( ( ! is_array( $history ) || empty( $history ) ) && function_exists( 'rvy_post_id' ) ) {
            $parent_id = (int) rvy_post_id( $post->ID );
            if ( $parent_id && $parent_id !== $post->ID ) {
                $history   = get_post_meta( $parent_id, self::HISTORY_META_KEY, true );
                $source_id = $parent_id;
            }
        }

        if ( ! is_array( $history ) || empty( $history ) ) {
            return;
        }

        $entries = array_reverse( $history ); // newest first
        $rows    = '';
        foreach ( $entries as $entry ) {
            $reviewer = get_userdata( $entry['reviewer_id'] ?? 0 );
            $name     = $reviewer ? esc_html( $reviewer->display_name ) : esc_html__( 'Reviewer', 'gca' );
            $date     = esc_html( date_i18n( get_option( 'date_format' ), $entry['timestamp'] ?? 0 ) );
            $rows    .= '<tr style="border-top:1px solid #e5e5e5;">'
                . '<td style="padding:6px 8px;white-space:nowrap;font-weight:600;">' . $name . '</td>'
                . '<td style="padding:6px 8px;white-space:nowrap;color:#666;">' . $date . '</td>'
                . '<td style="padding:6px 8px;">' . nl2br( esc_html( $entry['comments'] ?? '' ) ) . '</td>'
                . '</tr>';
        }

        echo '<div class="notice notice-warning" style="padding:10px 12px;">'
            . '<p style="margin:0 0 8px;font-weight:600;">' . esc_html__( 'Reviewer Feedback', 'gca' ) . '</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:13px;"><tbody>'
            . $rows
            . '</tbody></table>'
            . '</div>';
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

    public static function render_contributor_meta_box( WP_Post $post ): void {
        // Always resolve to the parent page — revisions may have partial history stored
        // on them directly, so using the parent ensures the full history is shown.
        $source_id = $post->ID;
        if ( function_exists( 'rvy_post_id' ) ) {
            $parent_id = (int) rvy_post_id( $post->ID );
            if ( $parent_id && $parent_id !== $post->ID ) {
                $source_id = $parent_id;
            }
        }
        $history = get_post_meta( $source_id, self::HISTORY_META_KEY, true );

        if ( ! is_array( $history ) || empty( $history ) ) {
            echo '<p class="description">No rejection feedback yet.</p>';
            return;
        }

        foreach ( array_reverse( $history ) as $entry ) :
            $reviewer      = get_userdata( $entry['reviewer_id'] ?? 0 );
            $reviewer_name = $reviewer ? $reviewer->display_name : 'Unknown reviewer';
            $date          = date_i18n(
                get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                $entry['timestamp'] ?? 0
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
            <?php
        endforeach;
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

        // PublishPress revision posts have a non-empty post_mime_type (e.g. 'pending-revision').
        // Regular pages always have an empty post_mime_type.
        $is_rvy_revision = ! empty( $post->post_mime_type ) && false !== strpos( $post->post_mime_type, 'revision' );

        if ( $is_rvy_revision ) {
            // Save comments on the revision so on_revision_declined can copy them to the parent.
            update_post_meta( $post_id, self::META_KEY, $comments );

            if ( ! empty( $_POST['gca_submit_rejection'] ) && '' !== $comments ) {
                // Signal redirect_revision_decline to redirect to the PublishPress decline URL
                // after this save completes. That fires revisionary_declined → on_revision_declined.
                set_transient( 'gca_reject_revision_' . get_current_user_id(), $post_id, 60 );
            }
            return;
        }

        // Regular page (non-revision) flow.
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
    // PublishPress revision decline hook
    // -------------------------------------------------------------------------

    public static function redirect_revision_decline( string $location ): string {
        $transient_key = 'gca_reject_revision_' . get_current_user_id();
        $revision_id   = get_transient( $transient_key );
        if ( ! $revision_id ) {
            return $location;
        }
        delete_transient( $transient_key );
        return admin_url( 'post.php?post=' . (int) $revision_id . '&action=decline_revision&_wpnonce=' . wp_create_nonce( 'decline-revision' ) );
    }

    public static function on_revision_declined( int $published_id, WP_Post $revision, object $revision_before ): void {
        // Edit-page path: publisher entered feedback on the revision's meta box.
        // Check revision meta first so a new comment always overwrites an old one on
        // the parent page (which would otherwise be returned as the "primary" comment).
        $revision_comments = get_post_meta( $revision->ID, self::META_KEY, true );

        if ( ! empty( $revision_comments ) ) {
            update_post_meta( $published_id, self::META_KEY, $revision_comments );
            delete_post_meta( $revision->ID, self::META_KEY );
            $comments = $revision_comments;
        } else {
            // Interstitial form path: feedback was saved directly to the parent page.
            $comments = get_post_meta( $published_id, self::META_KEY, true );
            if ( empty( $comments ) ) {
                return;
            }
        }

        do_action( 'gca_workflow_page_rejected', $published_id, get_current_user_id(), $comments );
    }

    // -------------------------------------------------------------------------
    // Decline feedback interstitial page (publisher-only)
    // -------------------------------------------------------------------------

    public static function register_decline_feedback_page(): void {
        add_submenu_page(
            null,
            __( 'Decline Revision', 'gca' ),
            __( 'Decline Revision', 'gca' ),
            'read',
            'gca-decline-revision',
            [ __CLASS__, 'render_decline_feedback_page' ]
        );
    }

    public static function render_decline_feedback_page(): void {
        $user_id = get_current_user_id();
        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
             && ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', 'gca' ) );
        }

        $revision_id = isset( $_GET['revision_id'] ) ? (int) $_GET['revision_id'] : 0;
        $decline_url = isset( $_GET['decline_url'] )
            ? esc_url_raw( wp_unslash( $_GET['decline_url'] ) )
            : '';

        if ( ! $revision_id || ! $decline_url ) {
            wp_die( esc_html__( 'Invalid request.', 'gca' ) );
        }

        $revision = get_post( $revision_id );
        if ( ! $revision || 'page' !== $revision->post_type ) {
            wp_die( esc_html__( 'Revision not found.', 'gca' ) );
        }

        $parent_id  = (int) rvy_post_id( $revision_id );
        $parent     = $parent_id ? get_post( $parent_id ) : null;
        $page_title = $parent ? $parent->post_title : __( 'Unknown Page', 'gca' );

        $existing = $parent_id ? get_post_meta( $parent_id, self::META_KEY, true ) : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Decline Revision', 'gca' ); ?></h1>
            <p><?php echo wp_kses( sprintf(
                /* translators: %s: page title */
                __( 'You are declining the revision for: <strong>%s</strong>', 'gca' ),
                esc_html( $page_title )
            ), [ 'strong' => [] ] ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'gca_decline_feedback_' . $revision_id ); ?>
                <input type="hidden" name="gca_revision_id"  value="<?php echo (int) $revision_id; ?>">
                <input type="hidden" name="gca_decline_url"  value="<?php echo esc_attr( $decline_url ); ?>">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="gca_decline_feedback">
                                <?php esc_html_e( 'Feedback for contributor', 'gca' ); ?>
                            </label>
                        </th>
                        <td>
                            <textarea
                                id="gca_decline_feedback"
                                name="gca_decline_feedback"
                                rows="6"
                                style="width:500px;max-width:100%;"
                                placeholder="<?php esc_attr_e( 'Optional — leave blank to decline without a message.', 'gca' ); ?>"
                            ><?php echo esc_textarea( $existing ); ?></textarea>
                            <p class="description">
                                <?php esc_html_e( 'This will be shown to the contributor the next time they open the page.', 'gca' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Decline Revision', 'gca' ); ?>
                    </button>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=revisionary-q' ) ); ?>" class="button">
                        <?php esc_html_e( 'Cancel', 'gca' ); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    public static function handle_decline_feedback_submit(): void {
        if ( ! isset( $_POST['gca_revision_id'], $_POST['gca_decline_url'] ) ) {
            return;
        }

        $user_id = get_current_user_id();
        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
             && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $revision_id = (int) $_POST['gca_revision_id'];
        check_admin_referer( 'gca_decline_feedback_' . $revision_id );

        $decline_url = esc_url_raw( wp_unslash( $_POST['gca_decline_url'] ) );
        $feedback    = isset( $_POST['gca_decline_feedback'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['gca_decline_feedback'] ) )
            : '';

        if ( $feedback !== '' && $revision_id ) {
            $parent_id = (int) rvy_post_id( $revision_id );
            if ( $parent_id ) {
                update_post_meta( $parent_id, self::META_KEY, $feedback );
            }
        }

        wp_redirect( $decline_url );
        exit;
    }

    public static function decline_redirect_script(): void {
        $user_id = get_current_user_id();
        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::PUBLISHER )
             && ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $feedback_page = admin_url( 'admin.php?page=gca-decline-revision' );
        ?>
        <script>
        (function () {
            document.addEventListener('click', function (e) {
                var link = e.target.closest('a');
                if (!link) { return; }
                var href = link.getAttribute('href') || '';
                if (href.indexOf('action=decline_revision') === -1) { return; }
                e.preventDefault();
                var match = href.match(/[?&]post=(\d+)/);
                if (!match) {
                    window.location.href = href;
                    return;
                }
                window.location.href = '<?php echo esc_js( $feedback_page ); ?>'
                    + '&revision_id=' + encodeURIComponent(match[1])
                    + '&decline_url=' + encodeURIComponent(href);
            }, true);
        }());
        </script>
        <?php
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
