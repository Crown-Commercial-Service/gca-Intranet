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

        // WordPress core's post_status dropdown only lists a fixed set of built-in
        // statuses — registering a status via register_post_status() does NOT add it
        // there. PublishPress Statuses adds its own custom statuses to that dropdown
        // via JS on admin_footer; we do the same for Archived, since there's no
        // server-side hook for it.
        add_action( 'admin_footer-post.php', [ __CLASS__, 'add_archived_option_to_status_dropdown' ] );

        // wp-admin/includes/post.php's edit_post() does `$post_data = &$_POST` (a
        // REFERENCE, not a copy) and then unconditionally forces post_status back to
        // 'publish' whenever the submitted form has a non-empty 'publish' field
        // (name="publish" — the same field the "Update" button on an already-published
        // post uses) and the status isn't 'private'. Because it's a reference, that
        // overwrite mutates the real $_POST superglobal itself — so by the time
        // wp_insert_post_data runs, $_POST['post_status'] has already been corrupted
        // to 'publish' too. Snapshot the real submitted value on admin_init, before
        // edit_post() runs, and use that snapshot instead.
        add_action( 'admin_init', [ __CLASS__, 'capture_submitted_post_status' ], 1 );
        add_filter( 'wp_insert_post_data', [ __CLASS__, 'preserve_archived_status_on_save' ], 10, 2 );
    }

    private static $submitted_post_status = null;

    public static function capture_submitted_post_status(): void {
        if ( isset( $_POST['post_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            self::$submitted_post_status = sanitize_key( wp_unslash( $_POST['post_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        }
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

    public static function add_archived_option_to_status_dropdown(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'page' !== $screen->id ) {
            return;
        }

        // Only publishers and admins archive pages per the documented workflow.
        $can_archive = GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::PUBLISHER )
                     || current_user_can( 'manage_options' );
        if ( ! $can_archive ) {
            return;
        }

        $post    = get_post();
        $current = $post ? $post->post_status : '';
        ?>
        <script>
        jQuery( function ( $ ) {
            var $select = $( 'select#post_status' );
            if ( ! $select.length || $select.find( 'option[value="<?php echo esc_js( self::ARCHIVED_STATUS ); ?>"]' ).length ) {
                return;
            }
            $select.append(
                <?php echo wp_json_encode( '<option value="' . self::ARCHIVED_STATUS . '">' . _x( 'Archived', 'post status', 'gca' ) . '</option>' ); ?>
            );
            <?php if ( self::ARCHIVED_STATUS === $current ) : ?>
            $select.val( '<?php echo esc_js( self::ARCHIVED_STATUS ); ?>' );
            $( '#post-status-display' ).text( '<?php echo esc_js( _x( 'Archived', 'post status', 'gca' ) ); ?>' );
            <?php endif; ?>
        } );
        </script>
        <?php
    }

    public static function preserve_archived_status_on_save( array $data, array $postarr ): array {
        if ( 'page' !== $data['post_type'] ) {
            return $data;
        }
        if ( self::ARCHIVED_STATUS !== self::$submitted_post_status ) {
            return $data;
        }

        $can_archive = GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::PUBLISHER )
                     || current_user_can( 'manage_options' );
        if ( ! $can_archive ) {
            return $data;
        }

        $data['post_status'] = self::ARCHIVED_STATUS;
        return $data;
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
