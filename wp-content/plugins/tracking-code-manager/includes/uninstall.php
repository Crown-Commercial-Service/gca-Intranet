<?php

function tcmp_uninstall( $networkwide = null ) {
	global $wpdb;

}

register_uninstall_hook( TCMP_PLUGIN_FILE, 'tcmp_uninstall' );

