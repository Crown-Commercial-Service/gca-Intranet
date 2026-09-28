<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Roles {

    const CONTRIBUTOR    = 'gca_contributor';
    const PUBLISHER      = 'gca_publisher';
    const PUBLISHER_ADMIN = 'gca_publisher_admin';
    const COMMUNITY_HOST = 'gca_community_host';

    // Post types contributors may create/edit content in, per the brief. 'blog',
    // 'news', 'event' and 'work_update' share WordPress's generic 'post' capability
    // namespace with core Posts — see block_contributor_posts_screen() and
    // remove_contributor_posts_menu(), which use this list to allow these four while
    // still blocking core Posts (and anything else sharing that namespace).
    const CONTRIBUTOR_ALLOWED_POST_TYPES = [ 'page', 'blog', 'news', 'event', 'work_update' ];

    private const CONTRIBUTOR_CAPS = [
        'read'                 => true,
        'edit_pages'           => true,
        'edit_published_pages' => true,
        'upload_files'         => true,
        // Needed to avoid a WordPress core quirk (a role lacking 'edit_posts' collides
        // the hidden "Posts" menu with the "Pages" menu, both using pagenow 'edit.php',
        // locking contributors out of Pages too) — and doubles as the base capability
        // for editing their own Blog/News/Event/Work Update posts, which share this
        // same generic capability namespace. Core Posts is explicitly re-blocked —
        // see block_contributor_posts_screen() and remove_contributor_posts_menu().
        'edit_posts'           => true,
        // Generic-namespace equivalent of edit_published_pages below — needed to
        // revise already-published Blog/News/Event/Work Update content (WF-3.x).
        'edit_published_posts' => true,
        // Base grant needed so WP core's admin list table doesn't force the Pages
        // list to "my posts only" (it does this automatically for any role lacking
        // edit_others_pages, regardless of directorate scoping). Per-post access to
        // out-of-scope pages is still denied — see block_contributor_out_of_scope_edit()
        // in GCA_Workflow_Category_Permissions.
        'edit_others_pages'    => true,
        // Generic-namespace equivalent of the above, for cross-author parity with
        // Pages on Blog/News/Event/Work Update. Unlike Pages, these types have no
        // directorate-scoped out-of-scope check — there's no responsible_team
        // taxonomy on them — so this is unrestricted: any contributor can edit any
        // other contributor's content of these types, matching how Pages already
        // behaves for a contributor with no directorate assigned (the documented
        // "no team = unrestricted" default).
        'edit_others_posts'    => true,
    ];

    private const PUBLISHER_CAPS = [
        'read'                      => true,
        'edit_pages'                => true,
        'edit_others_pages'         => true,
        'edit_published_pages'      => true,
        'publish_pages'             => true,
        'delete_pages'              => true,
        'delete_others_pages'       => true,
        'delete_published_pages'    => true,
        'upload_files'              => true,
        // Custom post types: news, blog, event, work_update
        'edit_posts'                => true,
        'edit_others_posts'         => true,
        'edit_published_posts'      => true,
        'publish_posts'             => true,
        'delete_posts'              => true,
        'delete_others_posts'       => true,
        'delete_published_posts'    => true,
        'moderate_comments'         => true,
        'manage_gca_announcement'   => true,
    ];


    private const COMMUNITY_HOST_CAPS = [
        'read'                  => true,
        'edit_posts'            => true,
        'publish_posts'         => true,
        'delete_posts'          => true,
        'upload_files'          => true,
        'community_host_access' => true,
    ];

    // -------------------------------------------------------------------------
    // Bootstrap — runs on every init, idempotent
    // -------------------------------------------------------------------------

    public static function init(): void {
        self::maybe_create_role( self::CONTRIBUTOR,    'GCA Contributor',    self::CONTRIBUTOR_CAPS );
        self::maybe_create_role( self::PUBLISHER,      'GCA Publisher',      array_merge( self::CONTRIBUTOR_CAPS, self::PUBLISHER_CAPS ) );
        self::maybe_create_role( self::COMMUNITY_HOST, 'GCA Community Host', self::COMMUNITY_HOST_CAPS );

        // One-time cleanup — idempotent, safe to run on every init.
        self::retire_publisher_admin_role();
        self::remove_deprecated_legacy_roles();
        self::deprecate_revisor_role();

        // Block contributors from creating new pages. user_has_cap covers actual
        // capability enforcement; admin_menu/admin_head handle the UI removal.
        add_filter( 'user_has_cap', [ __CLASS__, 'block_contributor_page_creation' ], 10, 4 );
        add_action( 'admin_menu',   [ __CLASS__, 'remove_contributor_add_page_menu' ] );
        add_action( 'admin_head',   [ __CLASS__, 'hide_contributor_add_page_button' ] );

        // Block contributors from trashing live (published) pages.
        add_filter( 'user_has_cap', [ __CLASS__, 'block_contributor_live_page_delete' ], 10, 4 );

        // Contributors are granted base 'edit_posts' (see CONTRIBUTOR_CAPS comment) purely
        // to avoid a core menu bug — re-block the standard Posts screen explicitly here.
        add_action( 'admin_menu', [ __CLASS__, 'remove_contributor_posts_menu' ], 999 );
        add_action( 'admin_init', [ __CLASS__, 'block_contributor_posts_screen' ] );
    }

    // -------------------------------------------------------------------------
    // Activation hook — one-time migration
    // -------------------------------------------------------------------------

    public static function activate(): void {
        self::init();
        self::migrate_and_deprecate_legacy_roles();
    }

    // -------------------------------------------------------------------------
    // Capability filters
    // -------------------------------------------------------------------------

    public static function block_contributor_page_creation( array $all_caps, array $cap_list, array $args, WP_User $user ): array {
        if ( in_array( 'create_pages', $cap_list, true ) && self::user_has_role( $user->ID, self::CONTRIBUTOR ) ) {
            $all_caps['create_pages'] = false;
        }
        return $all_caps;
    }

    public static function remove_contributor_add_page_menu(): void {
        if ( self::user_has_role( get_current_user_id(), self::CONTRIBUTOR ) ) {
            remove_submenu_page( 'edit.php?post_type=page', 'post-new.php?post_type=page' );
        }
    }

    public static function hide_contributor_add_page_button(): void {
        if ( ! self::user_has_role( get_current_user_id(), self::CONTRIBUTOR ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( $screen && 'edit-page' === $screen->id ) {
            echo '<style>.page-title-action { display: none !important; }</style>';
        }
    }

    public static function block_contributor_live_page_delete( array $all_caps, array $cap_list, array $args, WP_User $user ): array {
        if ( ! in_array( 'delete_published_pages', $cap_list, true ) ) {
            return $all_caps;
        }
        if ( ! self::user_has_role( $user->ID, self::CONTRIBUTOR ) ) {
            return $all_caps;
        }
        // Deny the capability so the trash action is blocked.
        $all_caps['delete_published_pages'] = false;
        return $all_caps;
    }

    public static function remove_contributor_posts_menu(): void {
        if ( ! self::user_has_role( get_current_user_id(), self::CONTRIBUTOR ) ) {
            return;
        }

        remove_menu_page( 'edit.php' ); // Core "Posts".

        foreach ( get_post_types( [ 'show_ui' => true ], 'objects' ) as $post_type => $object ) {
            if ( in_array( $post_type, self::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
                continue;
            }
            if ( ! empty( $object->cap->edit_posts ) && 'edit_posts' === $object->cap->edit_posts ) {
                remove_menu_page( 'edit.php?post_type=' . $post_type );
            }
        }
    }

    public static function block_contributor_posts_screen(): void {
        global $pagenow;

        if ( ! self::user_has_role( get_current_user_id(), self::CONTRIBUTOR ) ) {
            return;
        }

        $post_type = null;
        if ( in_array( $pagenow, [ 'edit.php', 'post-new.php' ], true ) ) {
            $post_type = empty( $_GET['post_type'] ) ? 'post' : sanitize_key( wp_unslash( $_GET['post_type'] ) );
        } elseif ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
            $post_type = get_post_type( (int) $_GET['post'] ) ?: null;
        }

        if ( null === $post_type || in_array( $post_type, self::CONTRIBUTOR_ALLOWED_POST_TYPES, true ) ) {
            return;
        }

        $post_type_object = get_post_type_object( $post_type );

        // Only block post types sharing WordPress's generic 'post' capability
        // namespace (Posts, News, Events) — types with their own capability_type
        // (like 'page') are governed entirely by their own caps already.
        $shares_generic_post_caps = $post_type_object
            && ! empty( $post_type_object->cap->edit_posts )
            && 'edit_posts' === $post_type_object->cap->edit_posts;

        if ( $shares_generic_post_caps ) {
            wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function maybe_create_role( string $slug, string $display_name, array $caps ): void {
        $role = get_role( $slug );
        if ( null === $role ) {
            add_role( $slug, $display_name, $caps );
            return;
        }
        // Role exists — ensure caps are up to date (idempotent).
        foreach ( $caps as $cap => $grant ) {
            if ( $grant ) {
                $role->add_cap( $cap );
            } else {
                $role->remove_cap( $cap );
            }
        }
    }

    private static function migrate_and_deprecate_legacy_roles(): void {
        $migrations = [
            'editor'      => self::PUBLISHER,
            'author'      => self::CONTRIBUTOR,
            'contributor' => self::CONTRIBUTOR,
        ];

        foreach ( $migrations as $old_slug => $new_slug ) {
            $users = get_users( [ 'role' => $old_slug ] );
            foreach ( $users as $user ) {
                $user->set_role( $new_slug );
            }
        }

        // Users migrated — delete the shells entirely.
        self::remove_deprecated_legacy_roles();
    }

    // Moves any remaining gca_publisher_admin users to administrator and deletes
    // the role. The administrator role already covers all the same capabilities.
    private static function retire_publisher_admin_role(): void {
        if ( ! get_role( self::PUBLISHER_ADMIN ) ) {
            return;
        }
        foreach ( get_users( [ 'role' => self::PUBLISHER_ADMIN ] ) as $user ) {
            $user->set_role( 'administrator' );
        }
        remove_role( self::PUBLISHER_ADMIN );
    }

    // Removes the empty shells left by migrate_and_deprecate_legacy_roles.
    private static function remove_deprecated_legacy_roles(): void {
        foreach ( [ 'editor', 'author', 'contributor' ] as $slug ) {
            if ( get_role( $slug ) ) {
                remove_role( $slug );
            }
        }
    }

    // The revisionary plugin recreates the revisor role on every load so it
    // cannot be permanently deleted — rename it to make its status clear.
    private static function deprecate_revisor_role(): void {
        $all_roles = get_option( 'wp_user_roles', [] );
        if ( isset( $all_roles['revisor'] ) && false === strpos( $all_roles['revisor']['name'], '[Deprecated]' ) ) {
            $all_roles['revisor']['name'] = '[Deprecated] ' . $all_roles['revisor']['name'];
            update_option( 'wp_user_roles', $all_roles );
        }
    }

    public static function user_has_role( int $user_id, string $role ): bool {
        $user = get_userdata( $user_id );
        return $user && in_array( $role, (array) $user->roles, true );
    }
}
