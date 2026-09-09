<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * A single "Review Queue" screen for publishers, listing everything awaiting
 * review in one place instead of requiring separate checks across Pages,
 * Blogs, News, Events and Work Updates.
 *
 * Combines two distinct kinds of pending item:
 *   - New content (never published) sitting in post_status = 'pending'.
 *   - Edits to already-live content, staged by PublishPress Revisions as a
 *     shadow copy that shares the SAME post_type as the live original (not
 *     'revision' — that's only core WP's unrelated autosave-revision type),
 *     marked by post_status = 'pending' and post_mime_type = 'pending-revision'.
 *     The live parent's ID isn't in post_parent — PublishPress stores it in
 *     comment_count (or postmeta as a fallback), resolved via rvy_post_id().
 */
class GCA_Workflow_Review_Queue {

    const SLUG = 'gca-review-queue';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ], 5 );
    }

    public static function register_menu(): void {
        if ( ! self::current_user_is_reviewer() ) {
            return;
        }
        add_menu_page(
            'Review Queue',
            'Review Queue',
            'read',
            self::SLUG,
            [ __CLASS__, 'render' ],
            'dashicons-yes-alt',
            3
        );
    }

    private static function current_user_is_reviewer(): bool {
        return GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::PUBLISHER )
            || current_user_can( 'manage_options' );
    }

    // -------------------------------------------------------------------------
    // Data
    // -------------------------------------------------------------------------

    private static function get_all_pending_posts(): array {
        $posts = [];
        // Query post types individually to bypass a WP core bug where passing an array
        // of post types causes get_post_type_object() to fail, which in turn causes WP to
        // assume the user lacks edit_others_posts and restrict the query to the current user's posts.
        foreach ( GCA_Workflow_Roles::CONTRIBUTOR_ALLOWED_POST_TYPES as $post_type ) {
            $query = new WP_Query( [
                'post_type'        => $post_type,
                'post_status'      => 'pending',
                'posts_per_page'   => -1,
                // Suppress filters to bypass third-party query modifications
                // (like PublishPress Permissions).
                'suppress_filters' => true,
            ] );
            $posts = array_merge( $posts, $query->posts );
        }

        usort( $posts, static fn( WP_Post $a, WP_Post $b ): int => strtotime( $b->post_modified ) <=> strtotime( $a->post_modified ) );
        return $posts;
    }

    /**
     * New (never-published) content awaiting review.
     */
    private static function get_pending_new_content(): array {
        return array_values( array_filter(
            self::get_all_pending_posts(),
            static fn( WP_Post $post ): bool => 'pending-revision' !== $post->post_mime_type
        ) );
    }

    /**
     * Edits to already-live content, staged by PublishPress Revisions.
     */
    private static function get_pending_revisions(): array {
        return array_values( array_filter(
            self::get_all_pending_posts(),
            static fn( WP_Post $post ): bool => 'pending-revision' === $post->post_mime_type
        ) );
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public static function render(): void {
        if ( ! self::current_user_is_reviewer() ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'gca' ) );
        }

        $new_content = self::get_pending_new_content();
        $revisions   = self::get_pending_revisions();
        $total       = count( $new_content ) + count( $revisions );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Review Queue', 'gca' ); ?></h1>

            <?php if ( 0 === $total ) : ?>
                <p><?php esc_html_e( 'Nothing is currently awaiting review.', 'gca' ); ?></p>
                <?php return; ?>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Type', 'gca' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'gca' ); ?></th>
                        <th><?php esc_html_e( 'Author', 'gca' ); ?></th>
                        <th><?php esc_html_e( 'Submitted', 'gca' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'gca' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $new_content as $post ) : ?>
                        <?php self::render_row(
                            self::type_label( $post->post_type ),
                            get_the_title( $post ),
                            (int) $post->post_author,
                            $post->post_modified,
                            admin_url( 'post.php?post=' . $post->ID . '&action=edit' )
                        ); ?>
                    <?php endforeach; ?>

                    <?php foreach ( $revisions as $revision ) : ?>
                        <?php // The shadow copy carries the live post's own type and title directly
                        // (see the class docblock) — no parent lookup needed for display. ?>
                        <?php self::render_row(
                            self::type_label( $revision->post_type ) . ' — ' . esc_html__( 'update', 'gca' ),
                            get_the_title( $revision ),
                            (int) $revision->post_author,
                            $revision->post_modified,
                            admin_url( 'post.php?post=' . $revision->ID . '&action=edit' )
                        ); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private static function render_row( string $type, string $title, int $author_id, string $modified, string $edit_url ): void {
        $author = get_userdata( $author_id );
        ?>
        <tr>
            <td><?php echo esc_html( $type ); ?></td>
            <td><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $title ); ?></a></td>
            <td><?php echo esc_html( $author ? $author->display_name : __( 'Unknown', 'gca' ) ); ?></td>
            <td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified ) ); ?></td>
            <td><a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php esc_html_e( 'Review', 'gca' ); ?></a></td>
        </tr>
        <?php
    }

    private static function type_label( string $post_type ): string {
        $object = get_post_type_object( $post_type );
        return $object ? $object->labels->singular_name : ucfirst( $post_type );
    }
}
