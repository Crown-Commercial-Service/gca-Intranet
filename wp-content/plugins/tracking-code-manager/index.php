<?php
/*
Plugin Name: Tracking Code Manager
Plugin URI: http://intellywp.com/tracking-code-manager/
Description: A plugin to manage ALL your tracking code and conversion pixels, simply. Compatible with Facebook Ads, Google Adwords, WooCommerce, Easy Digital Downloads, WP eCommerce.
Author: Data443
Author URI: https://data443.com/
Email: support@data443.com
Version: 2.5.0
Requires at least: 3.6.0
Requires PHP: 5.6
*/
register_activation_hook(__FILE__, function () {
    if (in_array('tracking-code-manager-pro/index.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        die;
    }
});
if (defined('TCMP_PLUGIN_NAME')) {
	die('This plugin could not be activated because the PRO version of this plugin is active. Deactivate the PRO version before activating this one. No data will be lost.');
}

define( 'TCMP_PLUGIN_PREFIX', 'TCMP_' );
define( 'TCMP_PLUGIN_FILE', __FILE__ );
define( 'TCMP_PLUGIN_SLUG', 'tracking-code-manager' );
define( 'TCMP_PLUGIN_NAME', 'Tracking Code Manager' );
define( 'TCMP_PLUGIN_VERSION', '2.5.0' );
define( 'TCMP_PLUGIN_AUTHOR', 'IntellyWP' );

define( 'TCMP_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
define( 'TCMP_PLUGIN_ASSETS_URI', plugins_url( 'assets/', __FILE__ ) );
define( 'TCMP_PLUGIN_IMAGES_URI', plugins_url( 'assets/images/', __FILE__ ) );
define( 'TCMP_PLUGIN_ACE', plugins_url( 'assets/js/ace/ace.js', __FILE__ ) );

define( 'TCMP_LOGGER', false );
define( 'TCMP_AUTOSAVE_LANG', false );

define( 'TCMP_QUERY_POSTS_OF_TYPE', 1 );
define( 'TCMP_QUERY_POST_TYPES', 2 );
define( 'TCMP_QUERY_CATEGORIES', 3 );
define( 'TCMP_QUERY_TAGS', 4 );
define( 'TCMP_QUERY_CONVERSION_PLUGINS', 5 );
define( 'TCMP_QUERY_TAXONOMY_TYPES', 6 );
define( 'TCMP_QUERY_TAXONOMIES_OF_TYPE', 7 );

define( 'TCMP_INTELLYWP_ENDPOINT', 'http://www.intellywp.com/wp-content/plugins/intellywp-manager/data.php' );
define( 'TCMP_PAGE_FAQ', 'http://www.intellywp.com/tracking-code-manager' );
define( 'TCMP_PAGE_PREMIUM', 'http://www.intellywp.com/tracking-code-manager' );
define( 'TCMP_PAGE_MANAGER', admin_url() . 'options-general.php?page=' . TCMP_PLUGIN_SLUG );
define( 'TCMP_PLUGIN_URI', plugins_url( '/', __FILE__ ) );

define( 'TCMP_POSITION_HEAD', 0 );
define( 'TCMP_POSITION_BODY', 1 );
define( 'TCMP_POSITION_FOOTER', 2 );
define( 'TCMP_POSITION_CONVERSION', 3 );

define( 'TCMP_TRACK_MODE_CODE', 0 );
define( 'TCMP_TRACK_PAGE_ALL', 0 );
define( 'TCMP_TRACK_PAGE_SPECIFIC', 1 );

define( 'TCMP_DEVICE_TYPE_MOBILE', 'mobile' );
define( 'TCMP_DEVICE_TYPE_TABLET', 'tablet' );
define( 'TCMP_DEVICE_TYPE_DESKTOP', 'desktop' );
define( 'TCMP_DEVICE_TYPE_ALL', 'all' );

define( 'TCMP_HOOK_PRIORITY_DEFAULT', 10 );

define( 'TCMP_TAB_EDITOR', 'editor' );
define( 'TCMP_TAB_EDITOR_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_EDITOR );
define( 'TCMP_TAB_MANAGER', 'manager' );
define( 'TCMP_TAB_MANAGER_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_MANAGER );
define( 'TCMP_TAB_ADMIN_OPTIONS', 'admin options' );
define( 'TCMP_TAB_ADMIN_OPTIONS_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_ADMIN_OPTIONS );
define( 'TCMP_TAB_SETTINGS', 'settings' );
define( 'TCMP_TAB_SETTINGS_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_SETTINGS );
define( 'TCMP_TAB_DOCS', 'docs' );
define( 'TCMP_TAB_DOCS_URI', 'http://intellywp.com/docs/category/tracking-code-manager/' );
define( 'TCMP_TAB_DOCS_DCV_URI', 'https://data443.atlassian.net/servicedesk/customer/kb/view/947486813' );
define( 'TCMP_TAB_ABOUT', 'about' );
define( 'TCMP_TAB_ABOUT_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_ABOUT );
define( 'TCMP_TAB_WHATS_NEW', 'whatsnew' );
define( 'TCMP_TAB_WHATS_NEW_URI', TCMP_PAGE_MANAGER . '&tab=' . TCMP_TAB_WHATS_NEW );

define( 'TCMP_SNIPPETS_LIMIT', 6 );

include_once( dirname( __FILE__ ) . '/autoload.php' );
tcmp_include_php( dirname( __FILE__ ) . '/includes/' );

global $tcmp;
$tcmp = new TCMP_Singleton();
$tcmp->init();

include_once( dirname( __FILE__ ) . '/tcmp_free_wp_kses_tags_attrs.php' );

tcmp_free_add_additional_tags_atts();

function tcmp_qs( $name, $default = '' ) {
	global $tcmp;
	$result = $tcmp->utils->qs( $name, $default );
	return $result;
}
//SANITIZED METHODS
function tcmp_sqs( $name, $default = '' ) {
	$result = tcmp_qs( $name, $default );
	$result = sanitize_text_field( $result );
	return $result;
}
function tcmp_isqs( $name, $default = 0 ) {
	$result = tcmp_sqs( $name, $default );
	$result = floatval( $result );
	return $result;
}
function tcmp_bsqs( $name, $default = 0 ) {
	global $tcmp;
	$result = $tcmp->utils->bqs( $name, $default );
	return $result;
}
function tcmp_asqs( $name, $default = array() ) {
	$result = tcmp_qs( $name, $default );
	if ( is_array( $result ) ) {
		foreach ( $result as $k => $v ) {
			$result[ $k ] = sanitize_text_field( $v );
		}
	} else {
		$result = sanitize_text_field( $result );
	}
	return $result;
}
