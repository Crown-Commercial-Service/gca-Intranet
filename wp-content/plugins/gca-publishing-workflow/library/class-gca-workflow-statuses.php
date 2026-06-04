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

        // Inject the status into the classic editor dropdown — WordPress does not
        // do this automatically for custom statuses.
        add_action( 'admin_footer-post.php', [ __CLASS__, 'inject_archived_status_js' ] );

        // WordPress forces post_status back to 'publish' when the Update button
        // (name="publish") is clicked, overriding the dropdown selection. Restore
        // the intended gca_archived status before the row is written.
        add_filter( 'wp_insert_post_data', [ __CLASS__, 'preserve_archived_status' ], 10, 2 );

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

    public static function inject_archived_status_js(): void {
        global $post;
        if ( ! $post || ! in_array( $post->post_type, [ 'page', 'post' ], true ) ) {
            return;
        }
        $is_archived = self::ARCHIVED_STATUS === $post->post_status;
        $label       = esc_js( _x( 'Archived', 'post status', 'gca' ) );
        $status      = esc_js( self::ARCHIVED_STATUS );
        ?>
        <script>
        jQuery(function($) {
            var $select = $('#post_status');
            if ($select.find('option[value="<?php echo $status; ?>"]').length === 0) {
                $select.append('<option value="<?php echo $status; ?>"><?php echo $label; ?></option>');
            }
            <?php if ( $is_archived ) : ?>
            $select.val('<?php echo $status; ?>');
            $('#post-status-display').text('<?php echo $label; ?>');
            <?php endif; ?>
        });
        </script>
        <?php
    }

    public static function preserve_archived_status( array $data, array $postarr ): array {
        $intended = isset( $_POST['post_status'] ) ? sanitize_key( $_POST['post_status'] ) : '';
        if ( self::ARCHIVED_STATUS === $intended && current_user_can( 'publish_pages' ) ) {
            $data['post_status'] = self::ARCHIVED_STATUS;
        }
        return $data;
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
