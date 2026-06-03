<?php

/*
Plugin Name: GCA Publishing Workflow
Description: Content publishing workflow with roles, statuses, email notifications, and revision support for the GCA Intranet.
Version: 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GCA_WORKFLOW_FILE',    __FILE__ );
define( 'GCA_WORKFLOW_VERSION', '0.1' );
define( 'GCA_WORKFLOW_DIR',     plugin_dir_path( __FILE__ ) );

require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-settings.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-roles.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-admin-ui.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-statuses.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-notifications.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-rejection.php';
require_once GCA_WORKFLOW_DIR . 'library/class-gca-workflow-revisions.php';

// Register feature flag (available regardless of flag state so it appears in the admin UI).
add_action( 'init', function (): void {
    if ( function_exists( 'gca_register_feature_flag' ) ) {
        gca_register_feature_flag( 'publishing-workflow', [
            'label'       => 'Publishing Workflow',
            'description' => 'Enables the Draft → Pending → Live/Archive workflow for pages, email notifications to publishers and contributors, and PublishPress Revisions support. Configure the reviewer group email under Settings → Publishing Workflow.',
            'default'     => false,
            'tags'        => [ 'workflow', 'publishing', 'roles', 'email' ],
        ] );
    }
}, 5 );

// Settings page and roles always run — settings must be configurable before the flag is
// enabled, and roles must exist so PublishPress Permissions can reference them.
add_action( 'init', function (): void {
    GCA_Workflow_Settings::init();
    GCA_Workflow_Roles::init();
    GCA_Workflow_Admin_UI::init();
}, 10 );

// Workflow features are gated behind the feature flag.
add_action( 'init', function (): void {
    if ( ! function_exists( 'gca_flag_enabled' ) || ! gca_flag_enabled( 'publishing-workflow' ) ) {
        return;
    }
    GCA_Workflow_Statuses::init();
    GCA_Workflow_Notifications::init();
    GCA_Workflow_Rejection::init();
    GCA_Workflow_Revisions::init();
}, 10 );

// One-time migration runs on plugin activation.
register_activation_hook( __FILE__, [ 'GCA_Workflow_Roles', 'activate' ] );
