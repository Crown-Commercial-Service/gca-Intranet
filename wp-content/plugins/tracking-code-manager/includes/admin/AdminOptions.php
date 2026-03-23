<?php
function tcmp_ui_admin_options() {
	global $tcmp;

	?>
	<div style="float:left; min-width:750px">

	<?php

	$tcmp->form->prefix = 'AdminOptions';
	$tcmp->form->form_starts();

	if ( $tcmp->check->nonce( 'tcmp_admin_options' ) ) {
		$tcmp->options->setSkipCodeSanitization( $tcmp->utils->iqs( 'skipCodeSanitization' ) );
		$tcmp->options->setModifySuperglobalVariable( $tcmp->utils->iqs( 'checkbox' ) );
		$tcmp->options->setAdditionalRecognizedTags( $tcmp->utils->qs( 'tags' ) );
		$tcmp->options->setAdditionalRecognizedAttributes( $tcmp->utils->qs( 'attributes' ) );
		tcmp_free_add_additional_tags_atts();
		$tcmp->options->writeMessages();
	}

	$tcmp->form->p( __( 'Add additional tags and/or attributes to the code whitelist' ) );

	$tags = $tcmp->options->getAdditionalRecognizedTags();
	$attributes = $tcmp->options->getAdditionalRecognizedAttributes();

	$tcmp->form->textarea( 'tags', $tags, array('rows' => 2) );
	$tcmp->form->textarea( 'attributes', $attributes, array('rows' => 2) );

	$tcmp->form->p( __( 'Skip the Sanitization of all Tracking Codes' ) );

	$skip = $tcmp->options->getSkipCodeSanitization();

	$tcmp->form->checkbox( 'skipCodeSanitization', $skip );

	$tcmp->form->p( __( 'Enable option to change cache behavior' ) );

	$modify = $tcmp->options->getModifySuperglobalVariable();

	$tcmp->form->checkbox( 'checkbox', $modify );
	$tcmp->form->p( 'NOTE: From time to time, Support may recommend the superglobal switch to be turned on. Please do not turn it on unless support gives you direction to do so.' );

	$tcmp->form->nonce( 'tcmp_admin_options' );
	$tcmp->form->br();
	$tcmp->form->submit( 'Save' );
	$tcmp->form->form_ends();

	?>
	</div> 
	<?php
}
