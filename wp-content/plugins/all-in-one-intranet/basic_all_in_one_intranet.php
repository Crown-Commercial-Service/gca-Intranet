<?php
/**
 * Plugin Name:       All-In-One Intranet
 * Plugin URI:        https://wp-glogin.com/docs/all-in-one-intranet/
 * Description:       Instantly turn WordPress into a private corporate intranet.
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Version:           1.8.1
 * Author:            WP-Glogin
 * Author URI:        https://wp-glogin.com/
 * Network:           true
 * Text Domain:       all-in-one-intranet
 * Domain Path:       /assets/lang
 *
 *  All-In-One Intranet is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 2 of the License, or
 *  any later version.
 *
 *  All-In-One Intranet is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with All-In-One Intranet. If not, see <https://www.gnu.org/licenses/>.
 */

if ( ! class_exists( 'core_all_in_one_intranet' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/core/core_all_in_one_intranet.php' );
}

class aioi_basic_all_in_one_intranet extends core_all_in_one_intranet {

	public $PLUGIN_VERSION = '1.8.1';

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

// Initialize at least once.
BasicAllInOneIntranet();
