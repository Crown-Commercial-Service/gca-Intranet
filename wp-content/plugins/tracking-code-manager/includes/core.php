<?php
//per agganciarsi ogni volta che viene scritto un contenuto
add_filter( 'wp_head', 'tcmp_head', get_option( 'TCM_HookPriority', TCMP_HOOK_PRIORITY_DEFAULT ) );
function tcmp_head() {
	global $post, $tcmp;

	$tcmp->options->setPostShown( null );
	if ( $post && isset( $post->ID ) && $post->ID > 0 ) {
		$tcmp->options->setPostShown( $post );
		$tcmp->log->info( 'POST ID=%s IS SHOWN', $post->ID );
	}

	$tcmp->body_written = false;

	//future development
	//is_archive();
	//is_post_type_archive();
	//is_post_type_hierarchical();
	//is_attachment();
	$tcmp->manager->write_codes( TCMP_POSITION_HEAD );
}

add_action( 'wp_body_open', 'tcmp_body', get_option( 'TCM_HookPriority', TCMP_HOOK_PRIORITY_DEFAULT ) );
function tcmp_body() {
	global $tcmp;

	$tcmp->manager->write_codes( TCMP_POSITION_BODY );
	$tcmp->body_written = true;
}

add_action( 'wp_footer', 'tcmp_footer', get_option( 'TCM_HookPriority', TCMP_HOOK_PRIORITY_DEFAULT ) );
function tcmp_footer() {
	global $tcmp;

	if ( ! $tcmp->body_written ) {
		// this is a fallback if wp_body_open() is not called by the theme
		$tcmp->manager->write_codes( TCMP_POSITION_BODY );
	}

	$tcmp->manager->write_codes( TCMP_POSITION_CONVERSION );
	$tcmp->manager->write_codes( TCMP_POSITION_FOOTER );

	if ( $tcmp->options->getModifySuperglobalVariable() ) {
		if ( function_exists( 'wp_cache_set_home' ) ) {
			unset( $_POST );
		}
	}
}

//volendo funziona anche con gli shortcode
add_shortcode( 'tcmp', 'tcmp_shortcode' );
add_shortcode( 'tcm', 'tcmp_shortcode' );
function tcmp_shortcode( $atts, $content = '' ) {
	global $tcmp;
	extract( shortcode_atts( array( 'id' => false ), $atts ) );

	if ( ! isset( $id ) || ! $id ) {
		return '';
	}

	$snippet = $tcmp->manager->get( $id, true );
	return $snippet['code'];
}

function tcmp_ui_first_time() {
	global $tcmp;
	if ( $tcmp->options->isShowActivationNotice() ) {
		//$tcmp->options->pushSuccessMessage('FirstTimeActivation');
		//$tcmp->options->writeMessages();
		$tcmp->options->setShowActivationNotice( false );
	}
}
function tcmp_admin_footer() {
	global $tcmp;
	if ( $tcmp->lang->bundle->autoPush && TCMP_AUTOSAVE_LANG ) {
		$tcmp->lang->bundle->store( TCMP_PLUGIN_DIR . 'languages/Lang.txt' );
	}
}
add_filter( 'admin_footer', 'tcmp_admin_footer' );
