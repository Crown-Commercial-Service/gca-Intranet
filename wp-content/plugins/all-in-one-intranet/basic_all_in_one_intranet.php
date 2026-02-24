<?php
/**
 * Plugin Name: All-In-One Intranet
 * Plugin URI: https://wp-glogin.com/docs/all-in-one-intranet/
 * Description: Instantly turn WordPress into a private corporate intranet.
 * Version: 1.8.0
 * Author: WP-Glogin
 * Author URI: https://wp-glogin.com/
 * License: GPL3
 * Text Domain: all-in-one-intranet
 * Domain Path: /assets/lang
 */

if ( ! class_exists( 'core_all_in_one_intranet' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/core/core_all_in_one_intranet.php' );
}

class aioi_basic_all_in_one_intranet extends core_all_in_one_intranet {

	public $PLUGIN_VERSION = '1.8.0';

	// Singleton.
	private static $instance = null;

	public static function get_instance() {

		if ( self::$instance === null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// AUX

	protected function my_plugin_basename() {

		$basename = plugin_basename( __FILE__ );

		// Maybe due to symlink.
		if ( '/' . $basename === __FILE__ ) {
			$basename = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
		}

		return $basename;
	}

	protected function my_plugin_url() {

		$basename = plugin_basename( __FILE__ );

		// Maybe due to symlink.
		if ( '/' . $basename === __FILE__ ) {
			return plugins_url() . '/' . basename( dirname( __FILE__ ) ) . '/assets/';
		}

		// Normal case (non symlink).
		return plugin_dir_url( __FILE__ ) . 'assets/';
	}
}

/**
 * Global accessor function to singleton.
 *
 * @return aioi_basic_all_in_one_intranet
 */
function BasicAllInOneIntranet() {
	return aioi_basic_all_in_one_intranet::get_instance();
}

// Initialise at least once.
BasicAllInOneIntranet();
