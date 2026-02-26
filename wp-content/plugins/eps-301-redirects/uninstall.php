<?php
//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit();
}

global $wpdb;
//phpcs:ignore as we're using a custom table
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . "redirects"); //phpcs:ignore

delete_option('eps_pointers');
delete_option('eps_redirects_404_log');
delete_option('301-redirects-notices');
delete_option('eps_redirects_version');
