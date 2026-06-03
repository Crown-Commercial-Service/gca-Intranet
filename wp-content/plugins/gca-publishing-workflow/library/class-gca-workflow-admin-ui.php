<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Admin_UI {

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'restrict_contributor_menu' ], 999 );
        add_action( 'admin_init',  [ __CLASS__, 'redirect_contributor_dashboard' ] );
    }

    /**
     * Removes all admin menu items except Pages for contributors.
     * Runs at priority 999 so all plugins/themes have registered their menus first.
     * remove_menu_page() is visual only — capabilities enforce actual access control.
     */
    public static function restrict_contributor_menu(): void {
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        $slugs = [
            'index.php',                               // Dashboard
            'upload.php',                              // Media (upload inline via page editor instead)
            'activity-log-page',                       // Activity Log
            'gf_edit_forms',                           // Forms (Gravity Forms)
            'edit.php?post_type=community_shoutout',   // Shout-outs
            'edit-comments.php',                       // Comments
            'options-general.php',                     // Settings
            'edit.php?post_type=community_poll',       // Community Polls
            'edit.php?post_type=news',                 // News
            'edit.php?post_type=qa_question',          // Q&A Questions
            'edit.php?post_type=blog',                 // Blogs
            'edit.php?post_type=event',                // Events
            'edit.php?post_type=work_update',          // Work Updates
            'edit.php?post_type=community_post',       // Community Posts
            'edit.php',                                // Posts
            'themes.php',                              // Appearance
            'plugins.php',                             // Plugins
            'users.php',                               // Users
            'tools.php',                               // Tools
            'pp-capabilities',                         // Capabilities (Capability Manager Enhanced)
            'pp-capabilities-dashboard',               // Capabilities (multisite variant)
        ];

        foreach ( $slugs as $slug ) {
            remove_menu_page( $slug );
        }
    }

    /**
     * Redirects contributors away from the Dashboard to the Pages list.
     * WordPress lands users on /wp-admin/ (index.php) after login by default.
     */
    public static function redirect_contributor_dashboard(): void {
        global $pagenow;
        if ( ! GCA_Workflow_Roles::user_has_role( get_current_user_id(), GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }
        if ( 'index.php' === $pagenow ) {
            wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
            exit;
        }
    }
}
