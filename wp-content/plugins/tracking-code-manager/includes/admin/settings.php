<?php
function tcmp_ui_track() {
	global $tcmp;

	$track = tcmp_sqs( 'track', '' );
	if ( '' != $track ) {
		$track = intval( $track );
		$tcmp->options->setTrackingEnable( $track );
		$tcmp->tracking->sendTracking( true );
	}

	$uri = TCMP_TAB_SETTINGS_URI . '&track=';
	if ( $tcmp->options->isTrackingEnable() ) {
		$uri .= '0';
		$tcmp->options->pushSuccessMessage( 'EnableAllowTrackingNotice', $uri );
	} else {
		$uri .= '1';
		$tcmp->options->pushErrorMessage( 'DisableAllowTrackingNotice', $uri );
	}
	$tcmp->options->writeMessages();
}
function tcmp_ui_settings() {
	global $tcmp;

	$tcmp->form->prefix = 'License';
	if ( $tcmp->check->nonce( 'tcmp_license' ) ) {
		$options = $tcmp->options->getMetaboxPostTypes();
		foreach ( $options as $k => $v ) {
			$v             = tcmp_isqs( 'metabox_' . $k, 0 );
			$options[ $k ] = $v;
		}
		$tcmp->options->setMetaboxPostTypes( $options );

		$tcmp->options->setHookPriority( $tcmp->utils->iqs( 'tcmpHookPriority', TCMP_HOOK_PRIORITY_DEFAULT ) );
	}

	$tcmp->form->form_starts();
	$tcmp->form->prefix = 'Priority';
	$tcmp->form->p( 'PrioritySection' );
	$tcmp->form->number( 'tcmpHookPriority', $tcmp->options->getHookPriority() );

	$tcmp->form->prefix = 'License';
	$tcmp->form->p( 'MetaboxSection' );
	$metaboxes = $tcmp->options->getMetaboxPostTypes();

	$types = $tcmp->utils->query( TCMP_QUERY_POST_TYPES );
	foreach ( $types as $v ) {
		$v = $v['id'];
		//$tcmp->form->tags=TRUE;
		//$tcmp->form->premium=!in_array($v, array('post', 'page'));
		$tcmp->form->checkbox( 'metabox_' . $v, $metaboxes[ $v ] );
	}
	$tcmp->form->nonce( 'tcmp_license' );
	$tcmp->form->br();
	$tcmp->form->submit( 'Save' );
	$tcmp->form->form_ends();
}
