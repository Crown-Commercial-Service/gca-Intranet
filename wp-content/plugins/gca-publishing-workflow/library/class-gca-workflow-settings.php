<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Settings {

    const OPTION_REVIEWER_EMAIL = 'gca_workflow_reviewer_email';
    const MENU_SLUG             = 'gca-publishing-workflow';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'add_options_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_options_page(): void {
        add_options_page(
            'Publishing Workflow',
            'Publishing Workflow',
            'manage_options',
            self::MENU_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting(
            'gca_workflow_settings',
            self::OPTION_REVIEWER_EMAIL,
            [ 'sanitize_callback' => 'sanitize_email' ]
        );

        add_settings_section(
            'gca_workflow_main',
            'Email Notifications',
            '__return_false',
            self::MENU_SLUG
        );

        add_settings_field(
            self::OPTION_REVIEWER_EMAIL,
            'Reviewer Group Email',
            [ __CLASS__, 'render_email_field' ],
            self::MENU_SLUG,
            'gca_workflow_main'
        );
    }

    public static function render_email_field(): void {
        $value = get_option( self::OPTION_REVIEWER_EMAIL, '' );
        printf(
            '<input type="email" name="%s" value="%s" class="regular-text" placeholder="reviewers@example.com">
             <p class="description">All pending review and retirement request notifications are sent to this address.</p>',
            esc_attr( self::OPTION_REVIEWER_EMAIL ),
            esc_attr( $value )
        );
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Publishing Workflow Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'gca_workflow_settings' );
                do_settings_sections( self::MENU_SLUG );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
