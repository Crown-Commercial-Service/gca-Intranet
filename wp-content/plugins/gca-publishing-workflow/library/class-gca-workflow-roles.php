<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Roles {

    const CONTRIBUTOR    = 'gca_contributor';
    const PUBLISHER      = 'gca_publisher';
    const PUBLISHER_ADMIN = 'gca_publisher_admin';
    const COMMUNITY_HOST = 'gca_community_host';

    private const CONTRIBUTOR_CAPS = [
        'read'                 => true,
        'edit_pages'           => true,
        'edit_published_pages' => true,
        'upload_files'         => true,
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
    ];

    private const PUBLISHER_ADMIN_CAPS = [
        'manage_options'     => true,
        'manage_categories'  => true,
        'edit_theme_options' => true,
        'list_users'         => true,
        'edit_users'         => true,
        'promote_users'      => true,
        // install_plugins intentionally omitted: WordPress core auto-grants
        // install_languages to any user with install_plugins, and update-core.php
        // allows access if install_languages is true. Plugins page only needs
        // activate_plugins.
        'activate_plugins'   => true,
        'pp_manage_settings' => true,
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
        self::maybe_create_role( self::PUBLISHER_ADMIN, 'GCA Publisher Admin', array_merge( self::CONTRIBUTOR_CAPS, self::PUBLISHER_CAPS, self::PUBLISHER_ADMIN_CAPS ) );
        self::maybe_create_role( self::COMMUNITY_HOST, 'GCA Community Host', self::COMMUNITY_HOST_CAPS );

        // Block contributors from creating new pages. user_has_cap covers actual
        // capability enforcement; admin_menu/admin_head handle the UI removal.
        add_filter( 'user_has_cap', [ __CLASS__, 'block_contributor_page_creation' ], 10, 4 );
        add_action( 'admin_menu',   [ __CLASS__, 'remove_contributor_add_page_menu' ] );
        add_action( 'admin_head',   [ __CLASS__, 'hide_contributor_add_page_button' ] );

        // Block contributors from trashing live (published) pages.
        add_filter( 'user_has_cap', [ __CLASS__, 'block_contributor_live_page_delete' ], 10, 4 );
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

            $role = get_role( $old_slug );
            if ( $role ) {
                foreach ( array_keys( $role->capabilities ) as $cap ) {
                    $role->remove_cap( $cap );
                }
                // Rename display name so it's obvious in the UI.
                // WP stores role names in the wp_user_roles option.
                $all_roles = get_option( 'wp_user_roles', [] );
                if ( isset( $all_roles[ $old_slug ] ) ) {
                    $all_roles[ $old_slug ]['name'] = '[Deprecated] ' . $all_roles[ $old_slug ]['name'];
                    update_option( 'wp_user_roles', $all_roles );
                }
            }
        }
    }

    public static function user_has_role( int $user_id, string $role ): bool {
        $user = get_userdata( $user_id );
        return $user && in_array( $role, (array) $user->roles, true );
    }
}
