<?php

register_activation_hook( TCMP_PLUGIN_FILE, 'tcmp_install' );
function tcmp_install( $networkwide = null ) {
	global $wpdb, $tcmp;

	$time = $tcmp->options->getPluginInstallDate();
	if ( 0 === $time ) {
		$tcmp->options->setPluginInstallDate( time() );
	}
	$tcmp->options->setPluginUpdateDate( time() );
	$tcmp->options->setShowWhatsNew( true );
	$tcmp->options->setPluginFirstInstall( true );
}

add_action( 'admin_init', 'tcmp_first_redirect' );
function tcmp_first_redirect() {
	global $tcmp;
	$v = $tcmp->options->getShowWhatsNewSeenVersion();
	if ( $v >= 0 && TCMP_WHATSNEW_VERSION != $v ) {
		$tcmp->options->setShowWhatsNewSeenVersion( -1 );
		tcmp_install();
	}

	if ( $tcmp->options->isPluginFirstInstall() ) {
		$tcmp->options->setPluginFirstInstall( false );
		$tcmp->options->setShowActivationNotice( true );
		$tcmp->utils->redirect( TCMP_PAGE_MANAGER );
	}
}
