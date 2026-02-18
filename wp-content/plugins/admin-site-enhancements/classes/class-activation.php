<?php

namespace ASENHA\Classes;

/**
 * Plugin Activation
 *
 * @since 1.0.0
 */
class Activation {

	/**
	 * Create failed login log table for Limit Login Attempts feature
	 *
	 * @since 2.5.0
	 */
	public function create_failed_logins_log_table() {
        global $wpdb;

        // Limit Login Attempts Log Table

        $table_name = $wpdb->prefix . 'asenha_failed_logins';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collation_sql = "DEFAULT CHARACTER SET $wpdb->charset";         
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collation_sql .= " COLLATE $wpdb->collate";         
        }

        // Drop table if already exists
        $wpdb->query("DROP TABLE IF EXISTS `". $table_name ."`");

        // Create database table. This procedure may also be called
        $sql = 
        "CREATE TABLE {$table_name} (
            id int(6) unsigned NOT NULL auto_increment,
            ip_address varchar(40) NOT NULL DEFAULT '',
            username varchar(24) NOT NULL DEFAULT '',
            fail_count int(10) NOT NULL DEFAULT '0',
            lockout_count int(10) NOT NULL DEFAULT '0',
            request_uri varchar(24) NOT NULL DEFAULT '',
            unixtime int(10) NOT NULL DEFAULT '0',
            datetime_wp varchar(36) NOT NULL DEFAULT '',
            -- datetime_utc datetime NULL DEFAULT CURRENT_TIMESTAMP,
            info varchar(64) NOT NULL DEFAULT '',
            UNIQUE (ip_address),
            PRIMARY KEY (id)
        ) {$charset_collation_sql}";
		
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';

        dbDelta( $sql );

        return true;
	}

    /**
     * Create email delivery log table for Email Delivery module
     *
     * @since 7.1.0
     */
    public function create_email_delivery_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asenha_email_delivery';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collation_sql = "DEFAULT CHARACTER SET $wpdb->charset";         
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collation_sql .= " COLLATE $wpdb->collate";         
        }

        // Drop table if already exists
        $wpdb->query("DROP TABLE IF EXISTS `". $table_name ."`");

        // Create database table. This procedure may also be called
        $sql = 
        "CREATE TABLE {$table_name} (
            id int(6) unsigned NOT NULL auto_increment,
            status enum('successful','failed','unknown') NOT NULL DEFAULT 'unknown',
            error varchar(250) NOT NULL DEFAULT '',
            subject varchar(250) NOT NULL DEFAULT '',
            message longtext NOT NULL DEFAULT '',
            send_to varchar(256) NOT NULL DEFAULT '',
            sender varchar(256) NOT NULL DEFAULT '',
            reply_to varchar(256) NOT NULL DEFAULT '',            
            headers text NOT NULL DEFAULT '',
            content_type text NOT NULL DEFAULT '',
            attachments text NOT NULL DEFAULT '',
            backtrace text NOT NULL DEFAULT '',
            processor text NOT NULL DEFAULT '',
            sent_on datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            sent_on_unixtime int(10) NOT NULL DEFAULT '0',
            extra longtext NOT NULL DEFAULT '',
            PRIMARY KEY (id)
        ) {$charset_collation_sql}";
        
        require_once ABSPATH . '/wp-admin/includes/upgrade.php';

        dbDelta( $sql );

        return true;
    }

    /**
     * Create tables for the Form Builder module
     *
     * @since 7.8.0
     */
    public function create_form_builder_tables() {
        global $wpdb;

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collation_sql = "DEFAULT CHARACTER SET $wpdb->charset";         
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collation_sql .= " COLLATE $wpdb->collate";         
        }
        
        flush_rewrite_rules();

        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        
        $fields_table_name = $wpdb->prefix . 'asenha_formbuilder_fields';
        $forms_table_name = $wpdb->prefix . 'asenha_formbuilder_forms';
        $entries_table_name = $wpdb->prefix . 'asenha_formbuilder_entries';
        $entry_meta_table_name = $wpdb->prefix . 'asenha_formbuilder_entry_meta';
        
        $sql = "CREATE TABLE {$fields_table_name} (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  field_key varchar(100) NULL,
  name text NULL,
  description longtext NULL,
  type text NULL,
  default_value longtext NULL,
  options longtext NULL,
  field_order int(11) DEFAULT 0,
  required int(1) NULL,
  field_options longtext NULL,
  form_id int(11) NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY form_id (form_id),
  UNIQUE KEY field_key (field_key)
) {$charset_collation_sql}";

        dbDelta( $sql );
        
        $sql = "CREATE TABLE {$forms_table_name} (
  id int(11) NOT NULL AUTO_INCREMENT,
  form_key varchar(100) NULL,
  name varchar(255) NULL,
  description text NULL,
  status varchar(255) NULL,
  options longtext NULL,
  settings longtext NULL,
  styles longtext NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY form_key (form_key)
) {$charset_collation_sql}";

        dbDelta( $sql );

        $sql = "CREATE TABLE {$entries_table_name} (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  ip text NULL,
  form_id bigint(20) NULL,
  user_id bigint(20) NULL,
  delivery_status tinyint(1) DEFAULT 0,
  status varchar(255) NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY form_id (form_id),
  KEY user_id (user_id)
) {$charset_collation_sql}";

        dbDelta( $sql );

        $sql = "CREATE TABLE {$entry_meta_table_name} (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  meta_value longtext NULL,
  field_id bigint(20) NOT NULL,
  item_id bigint(20) NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY field_id (field_id),
  KEY item_id (item_id)
) {$charset_collation_sql}";

        dbDelta( $sql );

        return true;
    }

    /**
     * Part of Disable Embeds module
     * Remove embeds rewrite rules on plugin activation.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L101
     * @since 8.0.0
     */
    public function disable_embeds_remove_rewrite_rules() {
        $common_methods = new Common_Methods;
        add_filter( 'rewrite_rules_array', [ $common_methods, 'disable_embeds_rewrites' ] );
        flush_rewrite_rules( false );
    }

}