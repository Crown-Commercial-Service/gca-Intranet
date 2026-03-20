<?php

/**
 * Plugin Name: Admin and Site Enhancements (ASE)
 * Plugin URI:        https://www.wpase.com/plugin-uri
 * Description:       Easily enable enhancements and features that usually require multiple plugins.
 * Version:           8.5.1
 * Author:            wpase.com
 * Author URI:        https://www.wpase.com/author-uri
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ASENHA_VERSION', '8.5.1' );
define( 'ASENHA_ID', 'asenha' );
define( 'ASENHA_SLUG', 'admin-site-enhancements' );
define( 'ASENHA_SLUG_U', 'admin_site_enhancements' );
define( 'ASENHA_URL', plugins_url( '/', __FILE__ ) ); // e.g. https://www.example.com/wp-content/plugins/this-plugin/
define( 'ASENHA_PATH', plugin_dir_path( __FILE__ ) ); // e.g. /home/user/apps/wp-root/wp-content/plugins/this-plugin/
// define( 'ASENHA_BASE', plugin_basename( __FILE__ ) ); // e.g. plugin-slug/this-file.php
// define( 'ASENHA_FILE', __FILE__ ); // /home/user/apps/wp-root/wp-content/plugins/this-plugin/this-file.php

/**
 * Autoload classes defined by this plugin
 *
 * @param string $class_name e.g. \ASENHA\Classes\The_Name
 * @since 1.0.0
 */
function asenha_autoloader( $class_name ) {

    $namespace = 'ASENHA';

    // Only process classes within this plugin's namespace

    if ( false !== strpos( $class_name, $namespace ) ) {

        // Assemble file path where class is defined

        // \ASENHA\Classes\The_Name => \Classes\The_Name
        $path = str_replace( $namespace, "", $class_name );

        // \Classes\The_Name => /classes/the_name
        $path = str_replace( "\\", DIRECTORY_SEPARATOR, strtolower( $path ) );

        // /classes/the_name =>  /classes/the-name.php
        $path = str_replace( "_", "-", $path ) . '.php';

        // /classes/the-name.php => /classes/class-the-name.php
        $path = str_replace( "classes" . DIRECTORY_SEPARATOR, "classes" . DIRECTORY_SEPARATOR . "class-", $path );

        // Remove first '/'
        $path = substr( $path, 1 );

        // Get /plugin-path/classes/class-the-name.php
        $path = ASENHA_PATH . $path;

        if ( file_exists( $path ) ) {
            require_once( $path );
        }                                                                       

    }

}

// Register autoloading classes
spl_autoload_register( 'asenha_autoloader' );

/**
 * Code that runs on plugin activation
 * 
 * @since 1.0.0
 */
function asenha_on_activation() {
    $activation = new ASENHA\Classes\Activation;
    $activation->create_failed_logins_log_table();
}

/**
 * Code that runs on plugin deactivation
 * 
 * @since 1.0.0
 */
function asenha_on_deactivation() {
    $deactivation = new ASENHA\Classes\Deactivation;
    $deactivation->delete_failed_logins_log_table();
}

// Register code that runs on plugin activation
register_activation_hook( __FILE__, 'asenha_on_activation');

// Register code that runs on plugin deactivation
register_deactivation_hook( __FILE__, 'asenha_on_deactivation' );

// Load translations
function asenha_free_load_textdomain() {
    load_plugin_textdomain( 'admin-site-enhancements', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'asenha_free_load_textdomain' );

// https://make.wordpress.org/core/2024/10/21/i18n-improvements-6-7/
// Use when tracing which code is triggering the "_load_textdomain_just_in_time Called Incorrectly" notice
// add_action(
//     'doing_it_wrong_run',
//     static function ( $function_name ) {
//         if ( '_load_textdomain_just_in_time' === $function_name ) {
//             debug_print_backtrace();
//         }
//     }
// );
        
// Functions for setting up admin menu, admin page, the settings sections and fields and other fondational stuff
require_once ASENHA_PATH . 'settings.php';

// Other required functions
require_once ASENHA_PATH . 'functions.php';

// Load vendor libraries
// require_once ASENHA_PATH . 'vendor/autoload.php';

// Bootstrap all the functionalities of this plugin
require_once ASENHA_PATH . 'bootstrap.php';