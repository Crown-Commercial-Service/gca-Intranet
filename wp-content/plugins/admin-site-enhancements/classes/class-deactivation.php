<?php

namespace ASENHA\Classes;

/**
 * Plugin Deactivation
 *
 * @since 1.0.0
 */
class Deactivation {

	/**
	 * Delete failed login log table for Limit Login Attempts feature
	 *
	 * @since 2.5.0
	 */
	public function delete_failed_logins_log_table() {

        global $wpdb;

        // Limit Login Attempts Log Table

        $table_name = $wpdb->prefix . 'asenha_failed_logins';

        // Drop table if already exists
        $wpdb->query("DROP TABLE IF EXISTS `". $table_name ."`");

	}

	/**
     * Part of Disable Embeds module
	 * Flush rewrite rules on plugin deactivation.
	 *
	 * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L113
	 * @since 8.0.0
	 */
	public function disable_embeds_flush_rewrite_rules() {
        $common_methods = new Common_Methods;
		remove_filter( 'rewrite_rules_array', [ $common_methods, 'disable_embeds_rewrites' ] );
		flush_rewrite_rules( false );
	}

}