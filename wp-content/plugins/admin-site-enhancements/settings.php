<?php

/**
 * Initialize options. [TODO] Move this into activation class at v7.
 * 
 * @since 6.4.1
 */
if ( false === get_option( ASENHA_SLUG_U ) ) {
    add_option(
        ASENHA_SLUG_U,
        array(),
        '',
        true
    );
}
if ( false === get_option( ASENHA_SLUG_U . '_stats' ) ) {
    add_option(
        ASENHA_SLUG_U . '_stats',
        array(),
        '',
        false
    );
}
if ( false === get_option( ASENHA_SLUG_U . '_extra' ) ) {
    add_option(
        ASENHA_SLUG_U . '_extra',
        array(),
        '',
        true
    );
}
// Bugfix in v7.1.2 for Custom Content Type module
$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
if ( !isset( $options_extra['cfgroup_next_field_id'] ) ) {
    $options_extra['cfgroup_next_field_id'] = 1;
    update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
}
// Move Admin Menu Organizer options storage to extra table since v7.8.5
if ( !isset( $options_extra['admin_menu'] ) ) {
    $options = get_option( ASENHA_SLUG_U, array() );
    $options_extra['admin_menu']['custom_menu_order'] = ( isset( $options['custom_menu_order'] ) ? $options['custom_menu_order'] : '' );
    unset($options['custom_menu_order']);
    $options_extra['admin_menu']['custom_menu_titles'] = ( isset( $options['custom_menu_titles'] ) ? $options['custom_menu_titles'] : '' );
    unset($options['custom_menu_titles']);
    $options_extra['admin_menu']['custom_menu_hidden'] = ( isset( $options['custom_menu_hidden'] ) ? trim( $options['custom_menu_hidden'], ',' ) : '' );
    unset($options['custom_menu_hidden']);
    update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
    update_option( ASENHA_SLUG_U, $options, true );
}
/**
 * Register admin menu
 *
 * @since 1.0.0
 */
function asenha_register_admin_menu() {
    add_submenu_page(
        'tools.php',
        // Parent page/menu
        'Admin and Site Enhancements',
        // Browser tab/window title
        __( 'Enhancements', 'admin-site-enhancements' ),
        // Sube menu title
        'manage_options',
        // Minimal user capabililty
        ASENHA_SLUG,
        // Page slug. Shows up in URL.
        'asenha_add_settings_page'
    );
}

/**
 * Create the settings page of the plugin
 *
 * @since 1.0.0
 */
function asenha_add_settings_page() {
    $options = get_option( ASENHA_SLUG_U, array() );
    ?>
	<div class="wrap asenha">

		<div id="asenha-header" class="asenha-header">
			<div class="asenha-header-left">
				<img src="<?php 
    echo esc_html( ASENHA_URL ) . 'assets/img/ase_icon.png';
    ?>" class="asenha-icon"/>
				<h1 class="asenha-heading">
					<?php 
    echo esc_html( get_admin_page_title() );
    ?>
					(ASE)
					<?php 
    ?>
				</h1>
				<input id="module-search-input" type="search" placeholder="<?php 
    echo esc_attr__( 'Search...', 'admin-site-enhancements' );
    ?>" />
				<!-- <a href="https://wordpress.org/plugins/admin-site-enhancements/" target="_blank" class="asenha-header-action"><span>&#8505;</span> <?php 
    // esc_html_e( 'Info', 'admin-site-enhancements' );
    ?></a> -->
			</div>
			<div class="asenha-header-right">
                <?php 
    // https://icon-sets.iconify.design/iconamoon/star-bold/
    $svg_star = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="2.5" d="m12 2l3.104 6.728l7.358.873l-5.44 5.03l1.444 7.268L12 18.28L5.534 21.9l1.444-7.268L1.538 9.6l7.359-.873L12 2Z"/></svg>';
    // https://icon-sets.iconify.design/octicon/question-16/
    $svg_support = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="currentColor" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Zm8-6.5a6.5 6.5 0 1 0 0 13a6.5 6.5 0 0 0 0-13ZM6.92 6.085h.001a.749.749 0 1 1-1.342-.67c.169-.339.436-.701.849-.977C6.845 4.16 7.369 4 8 4a2.756 2.756 0 0 1 1.637.525c.503.377.863.965.863 1.725c0 .448-.115.83-.329 1.15c-.205.307-.47.513-.692.662c-.109.072-.22.138-.313.195l-.006.004a6.24 6.24 0 0 0-.26.16a.952.952 0 0 0-.276.245a.75.75 0 0 1-1.248-.832c.184-.264.42-.489.692-.661c.103-.067.207-.132.313-.195l.007-.004c.1-.061.182-.11.258-.161a.969.969 0 0 0 .277-.245C8.96 6.514 9 6.427 9 6.25a.612.612 0 0 0-.262-.525A1.27 1.27 0 0 0 8 5.5c-.369 0-.595.09-.74.187a1.01 1.01 0 0 0-.34.398ZM9 11a1 1 0 1 1-2 0a1 1 0 0 1 2 0Z"/></svg>';
    // https://icon-sets.iconify.design/octicon/comment-16/
    $svg_feedback = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16"><path fill="currentColor" d="M1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0 1 13.25 12H9.06l-2.573 2.573A1.458 1.458 0 0 1 4 13.543V12H2.75A1.75 1.75 0 0 1 1 10.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 0 1 .75.75v2.19l2.72-2.72a.749.749 0 0 1 .53-.22h4.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"/></svg>';
    // https://icon-sets.iconify.design/ic/baseline-translate/
    $svg_translate = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="m12.87 15.07l-2.54-2.51l.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35C8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5l3.11 3.11zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2zm-2.62 7l1.62-4.33L19.12 17z"/></svg>';
    // https://icon-sets.iconify.design/iconamoon/file-document/
    $svg_docs = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="2"><path stroke-linecap="round" d="M7 21a2 2 0 0 1-2-2V3h9l5 5v11a2 2 0 0 1-2 2H7Z"/><path d="M13 3v6h6"/><path stroke-linecap="round" d="M9 13h6m-6 4h6"/></g></svg>';
    // https://icon-sets.iconify.design/pajamas/heart/
    $svg_sponsor = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16"><path fill="currentColor" fill-rule="evenodd" d="M8.753 2.247L8 3l-.753-.753A4.243 4.243 0 0 0 1.25 8.25l5.69 5.69L8 15l1.06-1.06l5.69-5.69a4.243 4.243 0 0 0-5.997-6.003ZM8 12.879l5.69-5.69a2.743 2.743 0 0 0-3.877-3.881l-.752.753L8 5.12L6.94 4.06l-.753-.752v-.001A2.743 2.743 0 0 0 2.31 7.189L8 12.88Z" clip-rule="evenodd"/></svg>';
    ?>
	 				<a href="https://wordpress.org/plugins/admin-site-enhancements/#reviews" target="_blank" class="asenha-header-action review"><?php 
    echo wp_kses( $svg_star, get_kses_with_svg_ruleset() ) . esc_html__( 'Review', 'admin-site-enhancements' );
    ?></a>
					<a href="https://wordpress.org/support/plugin/admin-site-enhancements/" target="_blank" class="asenha-header-action feedback"><?php 
    echo wp_kses( $svg_feedback, get_kses_with_svg_ruleset() ) . esc_html__( 'Feedback', 'admin-site-enhancements' );
    ?></a>
					<a href="https://www.wpase.com/trnslt-plgnhdr" target="_blank" class="asenha-header-action translate"><?php 
    echo wp_kses( $svg_translate, get_kses_with_svg_ruleset() ) . esc_html__( 'Translate', 'admin-site-enhancements' );
    ?></a>
					<!--<a href="https://www.wpasenha.com/docs/" target="_blank" class="asenha-header-action docs"><?php 
    // echo $svg_docs . esc_html__( 'Docs', 'admin-site-enhancements' );
    ?></a>-->
					<!--<a id="plugin-sponsor" href="#" class="asenha-header-action sponsor"><?php 
    // echo $svg_sponsor . esc_html__( 'Sponsor', 'admin-site-enhancements' );
    ?></a>-->
	                <a href="https://www.wpase.com/upgrade-btn" target="_blank" id="plugin-upgrade" class="button button-primary plugin-upgrade"><?php 
    echo esc_html__( 'Get ASE Pro', 'admin-site-enhancements' );
    ?></a>
				<?php 
    ?>
				<a class="button button-primary asenha-save-button"><?php 
    echo esc_html__( 'Save Changes', 'admin-site-enhancements' );
    ?></a>
				<!-- https://icon-sets.iconify.design/svg-spinners/180-ring-with-bg/ -->
				<div class="asenha-saving-changes" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2271b1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="#2271b1" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg></div>
				<div class="asenha-changes-saved" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><path fill="seagreen" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zM9.29 16.29L5.7 12.7a.996.996 0 1 1 1.41-1.41L10 14.17l6.88-6.88a.996.996 0 1 1 1.41 1.41l-7.59 7.59a.996.996 0 0 1-1.41 0z"/></svg></div>
			</div>
		</div>

		<div class="asenha-body">
			<?php 
    $is_yearend_promo_period = is_yearend_promo_period();
    ?>
			<?php 
    if ( $is_yearend_promo_period ) {
        ?>
				<!-- <div class="asenha-upgrade-nudge" style="display: none;">
					<div class="asenha-upgrade-nudge__message"><?php 
        // echo esc_html__( 'Lifetime Deal (LTD) is available for the Pro version of ASE.', 'admin-site-enhancements' );
        ?></div>
					<a href="https://www.wpase.com/upgrade-ndg" class="button asenha-upgrade-nudge__button" target="_blank"><?php 
        // echo esc_html__( 'Find Out More', 'admin-site-enhancements' );
        ?></a>
					<a href="#" id="dismiss-upgrade-nudge" class="asenha-upgrade-nudge__dismiss">
						<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="currentColor" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg>
					</a>
				</div> -->
				<div class="asenha-promo-nudge" style="display: none;">
					<div class="asenha-promo-nudge__message"><?php 
        echo esc_html__( 'ASE Pro YEAR-END SALE! 20% DISCOUNT. Lifetime Deal (LTD) available.', 'admin-site-enhancements' );
        ?></div>
					<a href="https://www.wpase.com/promo-ndg" class="button asenha-promo-nudge__button" target="_blank"><?php 
        echo esc_html__( 'Get It Now', 'admin-site-enhancements' );
        ?></a>
					<a href="#" id="dismiss-promo-nudge" class="asenha-promo-nudge__dismiss">
						<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="currentColor" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg>
					</a>
				</div>
				<?php 
    } else {
        ?>
				<div class="asenha-upgrade-nudge" style="display: none;">
					<div class="asenha-upgrade-nudge__message"><?php 
        echo esc_html__( 'Lifetime Deal (LTD) is available for the Pro version of ASE.', 'admin-site-enhancements' );
        ?></div>
					<a href="https://www.wpase.com/upgrade-ndg" class="button asenha-upgrade-nudge__button" target="_blank"><?php 
        echo esc_html__( 'Find Out More', 'admin-site-enhancements' );
        ?></a>
					<a href="#" id="dismiss-upgrade-nudge" class="asenha-upgrade-nudge__dismiss">
						<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path fill="currentColor" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg>
					</a>
				</div>
				<?php 
    }
    ?>
			<?php 
    ?>
			<div class="asenha-support-nudge nudge-show-more is-enabled" style="display: none;">
				<h3><?php 
    _e( 'Looks like some of these free enhancements have been useful for your site?', 'admin-site-enhancements' );
    ?></h3> 
				<p class="nudge-description intro"><?php 
    _e( 'Please consider supporting ASE.', 'admin-site-enhancements' );
    ?></p>
				<div class="nudge-content">
					<div class="nudge-primary">
						<h4><?php 
    _e( 'Write a quick, 5-star review for ASE', 'admin-site-enhancements' );
    ?></h4>
						<div class="nudge-ctas">
							<a href="https://wordpress.org/plugins/admin-site-enhancements/#reviews" target="_blank" class="button button-outline asenha-review-button"><?php 
    _e( 'Add Your Review', 'admin-site-enhancements' );
    ?></a>
							<a href="#" id="have-reviewed" class="asenha-have-supported"><?php 
    _e( 'I\'ve reviewed', 'admin-site-enhancements' );
    ?></a>
						</div>
						<p class="nudge-description"><?php 
    _e( 'Or, help improve ASE by <a href="https://wordpress.org/support/plugin/admin-site-enhancements/" target="_blank">providing feedback</a>.', 'admin-site-enhancements' );
    ?></p>
					</div>
					<div class="nudge-secondary">
						<h4><?php 
    _e( 'Share about ASE on:', 'admin-site-enhancements' );
    ?></h4>
						<div class="nudge-ctas">
							<a href="https://www.facebook.com/sharer.php?u=<?php 
    echo urlencode( 'https://www.wpase.com' );
    ?>" target="_blank" class="button button-outline asenha-share-button"><?php 
    _e( 'Facebook', 'admin-site-enhancements' );
    ?></a>
							<a href="https://twitter.com/intent/post?url=<?php 
    echo urlencode( 'https://www.wpase.com' );
    ?>&text=<?php 
    echo esc_attr( urlencode( 'Admin and Site Enhancements (ASE)' ) );
    ?>" target="_blank" class="button button-outline asenha-share-button"><?php 
    _e( 'X / Twitter', 'admin-site-enhancements' );
    ?></a>
							<a href="https://www.wpase.com/video-reviews/" target="_blank" class="button button-outline asenha-share-button"><?php 
    _e( 'YouTube', 'admin-site-enhancements' );
    ?></a>
							<a href="#" id="have-shared" class="asenha-have-supported"><?php 
    _e( 'I\'ve shared', 'admin-site-enhancements' );
    ?></a>
						</div>
						<div class="nudge-info">
							<p class="nudge-description"><?php 
    _e( 'Help others discover ASE and maybe <a href="https://www.wpase.com/trnslt-plgnndg" target="_blank">translate</a> to your language.', 'admin-site-enhancements' );
    ?></p>
						</div>
					</div>
				</div>
				<div class="dismiss-support-nudge"><a href="#" id="support-nudge-dismiss" class="asenha-support-nudge-dismiss"><?php 
    _e( 'Dismiss', 'admin-site-enhancements' );
    ?></a></div>
			</div>
			<?php 
    ?>
			<form action="options.php" method="post">
				<div class="asenha-vertical-tabs">
					<div class="asenha-tab-buttons">
						<?php 
    // https://icon-sets.iconify.design/mdi/database-check-outline/ -- db check
    // https://icon-sets.iconify.design/mdi/file-document-box-multiple-outline/ -- docs
    // https://icon-sets.iconify.design/fluent/content-view-28-regular/ -- content
    // https://icon-sets.iconify.design/lucide/shapes/ -- shapes
    $icon_content_management = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M8.3 10a.7.7 0 0 1-.626-1.079L11.4 3a.7.7 0 0 1 1.198-.043L16.3 8.9a.7.7 0 0 1-.572 1.1Z"/><rect width="7" height="7" x="3" y="14" rx="1"/><circle cx="17.5" cy="17.5" r="3.5"/></g></svg>';
    // https://icon-sets.iconify.design/mingcute/layout-line/
    $icon_admin_interface = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><path d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z"/><path fill="currentColor" d="M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5Zm16 0H5v3h14V5ZM5 19v-9h4v9H5Zm6 0h8v-9h-8v9Z"/></g></svg>';
    // https://icon-sets.iconify.design/ri/login-circle-line/
    $icon_login_logout = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m10.998 16l5-4l-5-4v3h-9v2h9z"/><path fill="currentColor" d="M12.999 2.999a8.938 8.938 0 0 0-6.364 2.637L8.049 7.05c1.322-1.322 3.08-2.051 4.95-2.051s3.628.729 4.95 2.051S20 10.13 20 12s-.729 3.628-2.051 4.95s-3.08 2.051-4.95 2.051s-3.628-.729-4.95-2.051l-1.414 1.414c1.699 1.7 3.959 2.637 6.364 2.637s4.665-.937 6.364-2.637C21.063 16.665 22 14.405 22 12s-.937-4.665-2.637-6.364a8.938 8.938 0 0 0-6.364-2.637z"/></svg>';
    // https://icon-sets.iconify.design/mingcute/code-line/
    $icon_custom_code = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path d="M0 0h24v24H0z"/><path fill="currentColor" d="M14.486 3.143a1 1 0 0 1 .692 1.233l-4.43 15.788a1 1 0 0 1-1.926-.54l4.43-15.788a1 1 0 0 1 1.234-.693ZM7.207 7.05a1 1 0 0 1 0 1.414L3.672 12l3.535 3.535a1 1 0 1 1-1.414 1.415L1.55 12.707a1 1 0 0 1 0-1.414L5.793 7.05a1 1 0 0 1 1.414 0Zm9.586 1.414a1 1 0 1 1 1.414-1.414l4.243 4.243a1 1 0 0 1 0 1.414l-4.243 4.242a1 1 0 0 1-1.414-1.414L20.328 12l-3.535-3.536Z"/></g></svg>';
    // https://icon-sets.iconify.design/mdi/forbid/
    $icon_disable_components = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2c5.5 0 10 4.5 10 10s-4.5 10-10 10S2 17.5 2 12S6.5 2 12 2m0 2c-1.9 0-3.6.6-4.9 1.7l11.2 11.2c1-1.4 1.7-3.1 1.7-4.9c0-4.4-3.6-8-8-8m4.9 14.3L5.7 7.1C4.6 8.4 4 10.1 4 12c0 4.4 3.6 8 8 8c1.9 0 3.6-.6 4.9-1.7Z"/></svg>';
    // https://icon-sets.iconify.design/jam/shield-check/
    $icon_security = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="m8 1.75l5.25 2v5c0 2.25-2 4.5-5.25 5.5c-3.25-1-5.25-3-5.25-5.5v-5z"/><path d="m5.75 7.75l1.5 1.5l3-3.5"/></g></svg>';
    // https://icon-sets.iconify.design/streamline/image-flash-1-flash-power-connect-charge-electricity-lightning/
    $icon_optimizations = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 14 14"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M4.25.5L2 5.81a.5.5 0 0 0 .46.69h2.79l-2 7l8.59-8.14a.5.5 0 0 0-.34-.86H7.75l2-4Z"/></svg>';
    // https://icon-sets.iconify.design/iconoir/tools/
    $icon_utilities = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="m10.05 10.607l-7.07 7.07a2 2 0 0 0 0 2.83v0a2 2 0 0 0 2.828 0l7.07-7.072m4.315.365l3.878 3.878a2 2 0 0 1 0 2.828v0a2 2 0 0 1-2.828 0l-6.209-6.208M6.733 5.904L4.61 6.61L2.49 3.075l1.414-1.414L7.44 3.782l-.707 2.122Zm0 0l2.83 2.83"/><path d="M10.05 10.607c-.844-2.153-.679-4.978 1.061-6.718c1.74-1.74 4.95-2.121 6.717-1.06l-3.04 3.04l-.283 3.111l3.111-.282l3.04-3.041c1.062 1.768.68 4.978-1.06 6.717c-1.74 1.74-4.564 1.905-6.717 1.061"/></g></svg>';
    ?>
					    <input id="tab-content-management" type="radio" name="tabs" checked><label for="tab-content-management" class="modules-tab"><?php 
    echo wp_kses( $icon_content_management, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Content Management', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-admin-interface" type="radio" name="tabs"><label for="tab-admin-interface" class="modules-tab"><?php 
    echo wp_kses( $icon_admin_interface, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Admin Interface', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-login-logout" type="radio" name="tabs"><label for="tab-login-logout" class="modules-tab"><?php 
    echo wp_kses( $icon_login_logout, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Log In/Out | Register', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-custom-code" type="radio" name="tabs"><label for="tab-custom-code" class="modules-tab"><?php 
    echo wp_kses( $icon_custom_code, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Custom Code', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-disable-components" type="radio" name="tabs"><label for="tab-disable-components" class="modules-tab"><?php 
    echo wp_kses( $icon_disable_components, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Disable Components', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-security" type="radio" name="tabs"><label for="tab-security" class="modules-tab"><?php 
    echo wp_kses( $icon_security, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Security', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-optimizations" type="radio" name="tabs"><label for="tab-optimizations" class="modules-tab"><?php 
    echo wp_kses( $icon_optimizations, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Optimizations', 'admin-site-enhancements' );
    ?></span></label>
					    <input id="tab-utilities" type="radio" name="tabs"><label for="tab-utilities" class="modules-tab"><?php 
    echo wp_kses( $icon_utilities, get_kses_with_svg_ruleset() );
    ?><span><?php 
    echo esc_html__( 'Utilities', 'admin-site-enhancements' );
    ?></span></label>
					    <label for="tab-search" class="search-tab" style="display:none;height:52px;"><span><?php 
    echo esc_html__( 'Search results:', 'admin-site-enhancements' );
    ?></span></label>
					</div>
					<div class="asenha-tab-contents">
					    <section class="asenha-fields fields-content-management"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-admin-interface"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-login-logout"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-custom-code"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-disable-components"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-security"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-optimizations"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					    <section class="asenha-fields fields-utilities"> 
					    	<table class="form-table" role="presentation">
					    		<tbody></tbody>
					    	</table>
					    </section>
					</div>
				</div>
				<div style="display:none;"><!-- Hide to prevent flash of fields appearing at the bottom of the page -->
					<?php 
    settings_fields( ASENHA_ID );
    ?>
					<?php 
    do_settings_sections( ASENHA_SLUG );
    ?>
					<?php 
    submit_button(
        __( 'Save Changes', 'admin-site-enhancements' ),
        // Button copy
        'primary',
        // Type: 'primary', 'small', or 'large'
        'submit',
        // The 'name' attribute
        true,
        // Whether to wrap in <p> tag
        array(
            'id' => 'asenha-submit',
        )
    );
    ?>
				</div>
			</form>
            <?php 
    ?>
			<div id="bottom-upgrade-nudge" class="asenha-upgrade-nudge-bottom" style="display:none;">
				<div class="asenha-upgrade-nudge-bottom__message"><?php 
    echo __( 'Do more with <a href="https://www.wpase.com/upgrade-ndg-btm" target="_blank">ASE Pro</a>. Lifetime deal (LTD) <a href="https://www.wpase.com/upgrade-ndg-btm-prc" target="_blank">available</a>.', 'admin-site-enhancements' );
    ?> <?php 
    if ( $is_yearend_promo_period ) {
        echo __( 'Currently on YEAR-END SALE. <a href="https://www.wpase.com/promo-ndg" target="_blank">20% discount</a>.', 'admin-site-enhancements' );
    }
    ?></div>
			</div>
			<?php 
    ?>
		</div>

		<?php 
    ?>

	</div>
	<?php 
    // Record the number of times changes were saved as well as the date of last save
    $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
    $changes_saved = ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ? true : false );
    if ( $changes_saved ) {
        $current_date = date( 'Y-m-d', time() );
        if ( !isset( $asenha_stats['first_save_date'] ) ) {
            $asenha_stats['first_save_date'] = $current_date;
            $asenha_stats['last_save_date'] = $current_date;
            $asenha_stats['save_count'] = 1;
            $asenha_stats['have_supported'] = false;
            $asenha_stats['support_nudge_dismissed'] = false;
            $asenha_stats['support_nudge_last_shown_date'] = '';
            $asenha_stats['support_nudge_last_shown_save_count'] = 0;
        } else {
            $asenha_stats['last_save_date'] = $current_date;
            $save_count = $asenha_stats['save_count'];
            $save_count++;
            $asenha_stats['save_count'] = $save_count;
        }
        update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
    }
}

/**
 * Check whether it's the year-end promo period
 * 
 * @since 7.8.11
 */
function is_yearend_promo_period() {
    // For year end promo between October 1 to December 31
    $current_month = intval( date( 'n', time() ) );
    // n is for numeric value of month, 1 to 12
    if ( $current_month >= 10 ) {
        $is_yearend_promo_period = true;
        // Clear out last year's promo nudge dismissal data
        $current_year = date( 'Y', time() );
        $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
        if ( isset( $asenha_stats['promo_nudge_dismissed'] ) && $asenha_stats['promo_nudge_dismissed'] ) {
            $last_dismissed_year = date( 'Y', strtotime( $asenha_stats['promo_nudge_dismissed_date'] ) );
            if ( $current_year > $last_dismissed_year ) {
                $asenha_stats['promo_nudge_dismissed'] = false;
                $asenha_stats['promo_nudge_dismissed_date'] = '';
                update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
            }
        }
    } else {
        $is_yearend_promo_period = false;
    }
    // For year end promo between November 15 to December 31
    // $tz = wp_timezone(); // WordPress timezone; or: new DateTimeZone('UTC')
    // $current_date = new DateTimeImmutable('now', $tz);
    // // $current_date = new DateTimeImmutable('2025-11-15', $tz); // For testing
    // // $current_date = new DateTimeImmutable('2026-01-01', $tz); // For testing
    // // $current_date = new DateTimeImmutable('2027-11-15', $tz); // For testing
    // $year  = (int) $current_date->format('Y');
    // $start = new DateTimeImmutable("$year-11-15 00:00:00", $tz);
    // $end   = new DateTimeImmutable("$year-12-31 23:59:59", $tz);
    // $is_between = ($current_date >= $start && $current_date <= $end);
    // if ( $is_between ) {
    // 	$is_yearend_promo_period = true;
    // } else {
    // 	$is_yearend_promo_period = false;
    // }
    // // Clear out last year's promo nudge dismissal data
    // if ( $is_yearend_promo_period ) {
    // 	$current_year = $year;
    // 	$asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
    // 	if ( isset( $asenha_stats['promo_nudge_dismissed'] ) && $asenha_stats['promo_nudge_dismissed'] ) {
    // 		$last_dismissed_year = date( 'Y', strtotime( $asenha_stats['promo_nudge_dismissed_date'] ) );
    // 		if ( $current_year > $last_dismissed_year ) {
    // 			$asenha_stats['promo_nudge_dismissed'] = false;
    // 			$asenha_stats['promo_nudge_dismissed_date'] = '';
    // 			update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
    // 		}
    // 	}
    // }
    return $is_yearend_promo_period;
}

/**
 * Suppress all notices, then add notice for successful settings update
 *
 * @since 1.1.0
 */
function asenha_suppress_add_notices() {
    global $plugin_page;
    // Suppress all notices
    if ( ASENHA_SLUG === $plugin_page ) {
        remove_all_actions( 'admin_notices' );
    }
    // Add notice for successful settings update
    if ( isset( $_GET['page'] ) && ASENHA_SLUG == $_GET['page'] && isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] ) {
        ?>
			<script>
				jQuery(document).ready( function() {
					jQuery('.asenha-changes-saved').fadeIn(400).delay(2500).fadeOut(400);
				});
			</script>

		<?php 
    }
}

/**
 * Suppress all generic notices on the plugin settings page
 *
 * @since 2.7.0
 */
function asenha_suppress_generic_notices() {
    global $plugin_page;
    // Suppress all notices
    if ( ASENHA_SLUG === $plugin_page ) {
        remove_all_actions( 'all_admin_notices' );
    }
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 */
function asenha_admin_scripts(  $hook_suffix  ) {
    global 
        $wp_version,
        $pagenow,
        $typenow,
        $taxnow,
        $hook_suffix,
        $current_user
    ;
    $current_screen = get_current_screen();
    // Get all WP Enhancements options, default to empty array in case it's not been created yet
    $options = get_option( 'admin_site_enhancements', array() );
    // For main page of this plugin
    if ( is_asenha() ) {
        wp_enqueue_style(
            'asenha-jbox',
            ASENHA_URL . 'assets/css/jBox.all.min.css',
            array(),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-jbox',
            ASENHA_URL . 'assets/js/jBox.all.min.js',
            array(),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script(
            'asenha-jsticky',
            ASENHA_URL . 'assets/js/jquery.jsticky.mod.min.js',
            array('jquery'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script(
            'asenha-js-cookie',
            ASENHA_URL . 'assets/js/js.cookie.min.js',
            array(),
            ASENHA_VERSION,
            false
        );
        // First, we unload the CodeMirror libraries included in WP core
        wp_deregister_script( 'wp-codemirror' );
        wp_deregister_script( 'code-editor' );
        wp_deregister_script( 'htmlhint' );
        wp_deregister_script( 'csslint' );
        wp_deregister_script( 'esprima' );
        wp_deregister_script( 'jshint' );
        // Then, we load ASENHA's CodeMirror libraries. In use, e.g. for Utilities >> Enable Custom Admin / Frontend CSS / ads.txt / app-ads.txt
        wp_enqueue_style(
            'asenha-codemirror',
            ASENHA_URL . 'assets/css/codemirror/codemirror.min.css',
            array(),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-codemirror',
            ASENHA_URL . 'assets/js/codemirror/codemirror.min.js',
            array('jquery'),
            ASENHA_VERSION,
            true
        );
        wp_enqueue_script(
            'asenha-codemirror-htmlmixed-mode',
            ASENHA_URL . 'assets/js/codemirror/htmlmixed.js',
            array('asenha-codemirror'),
            ASENHA_VERSION,
            true
        );
        wp_enqueue_script(
            'asenha-codemirror-xml-mode',
            ASENHA_URL . 'assets/js/codemirror/xml.js',
            array('asenha-codemirror'),
            ASENHA_VERSION,
            true
        );
        wp_enqueue_script(
            'asenha-codemirror-javascript-mode',
            ASENHA_URL . 'assets/js/codemirror/javascript.js',
            array('asenha-codemirror'),
            ASENHA_VERSION,
            true
        );
        wp_enqueue_script(
            'asenha-codemirror-css-mode',
            ASENHA_URL . 'assets/js/codemirror/css.js',
            array('asenha-codemirror'),
            ASENHA_VERSION,
            true
        );
        wp_enqueue_script(
            'asenha-codemirror-markdown-mode',
            ASENHA_URL . 'assets/js/codemirror/markdown.js',
            array('asenha-codemirror'),
            ASENHA_VERSION,
            true
        );
        // DataTables. In use, e.g. for Security >> Limit Login Attempts
        wp_enqueue_style(
            'asenha-datatables',
            ASENHA_URL . 'assets/css/datatables/datatables.min.css',
            array(),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-datatables',
            ASENHA_URL . 'assets/js/datatables/datatables.min.js',
            array('jquery'),
            ASENHA_VERSION,
            false
        );
        // Add WP media library assets
        wp_enqueue_media();
        // Add WP color picker assets
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        // We force loading the uncompressed version of TinyMCE. This ensures we load 'wp-tinymce-root' and then 'wp-tinymce',
        // which prevents issue where the Visual tab is unusable in some scenarios
        $wp_scripts = wp_scripts();
        $wp_scripts->remove( 'wp-tinymce' );
        wp_register_tinymce_scripts( $wp_scripts, true );
        // Main style and script for the admin page
        wp_enqueue_style(
            'asenha-admin-page',
            ASENHA_URL . 'assets/css/admin-page.css',
            array(
                'asenha-jbox',
                'asenha-codemirror',
                'asenha-datatables',
                'wp-color-picker'
            ),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-admin-page',
            ASENHA_URL . 'assets/js/admin-page.js',
            array(
                'asenha-jsticky',
                'asenha-jbox',
                'asenha-js-cookie',
                'asenha-codemirror-htmlmixed-mode',
                'asenha-codemirror-xml-mode',
                'asenha-codemirror-javascript-mode',
                'asenha-codemirror-css-mode',
                'asenha-codemirror-markdown-mode',
                'asenha-datatables',
                'wp-color-picker',
                'wp-mediaelement',
                'wp-tinymce-root',
                'wp-tinymce'
            ),
            ASENHA_VERSION,
            false
        );
        $jsvars = array(
            'nonce'                         => wp_create_nonce( 'asenha-' . get_current_user_id() ),
            'siteUrl'                       => get_site_url(),
            'wpcontentUrl'                  => content_url(),
            'mediaFrameTitle'               => __( 'Select an Image', 'admin-site-enhancements' ),
            'mediaFrameButtonText'          => __( 'Use Selected Image', 'admin-site-enhancements' ),
            'resetMenuNonce'                => wp_create_nonce( 'reset-menu-nonce' ),
            'sendTestEmailNonce'            => wp_create_nonce( 'send-test-email-nonce_' . get_current_user_id() ),
            'formBuilderSendTestEmailNonce' => wp_create_nonce( 'formbuilder_ajax' ),
            'expandText'                    => __( 'Expand', 'admin-site-enhancements' ),
            'collapseText'                  => __( 'Collapse', 'admin-site-enhancements' ),
            'dataTable'                     => array(
                'emptyTable'   => __( 'No data available in table', 'admin-site-enhancements' ),
                'info'         => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'admin-site-enhancements' ),
                'infoEmpty'    => __( 'Showing 0 to 0 of 0 entries', 'admin-site-enhancements' ),
                'infoFiltered' => __( '(filtered from _MAX_ total entries)', 'admin-site-enhancements' ),
                'lengthMenu'   => __( 'Show _MENU_ entries', 'admin-site-enhancements' ),
                'search'       => __( 'Search:', 'admin-site-enhancements' ),
                'zeroRecords'  => __( 'No matching records found', 'admin-site-enhancements' ),
                'paginate'     => array(
                    'first'    => __( 'First', 'admin-site-enhancements' ),
                    'last'     => __( 'Last', 'admin-site-enhancements' ),
                    'next'     => __( 'Next', 'admin-site-enhancements' ),
                    'previous' => __( 'Previous', 'admin-site-enhancements' ),
                ),
            ),
            'limitLoginAttempts'            => array(
                'releaseLockSuccess' => __( 'Lock released for %s.', 'admin-site-enhancements' ),
                'releaseLockError'   => __( 'Could not release lock. Please try again.', 'admin-site-enhancements' ),
            ),
        );
        wp_localize_script( 'asenha-admin-page', 'adminPageVars', $jsvars );
    }
    // Enqueue on all wp-admin
    wp_enqueue_style(
        'asenha-wp-admin',
        ASENHA_URL . 'assets/css/wp-admin.css',
        array(),
        ASENHA_VERSION
    );
    // Content Management >> Show IDs, for list tables in wp-admin, e.g. All Posts page
    if ( false !== strpos( $current_screen->base, 'edit' ) || false !== strpos( $current_screen->base, 'users' ) || false !== strpos( $current_screen->base, 'upload' ) ) {
        wp_enqueue_style(
            'asenha-list-table',
            ASENHA_URL . 'assets/css/list-table.css',
            array(),
            ASENHA_VERSION
        );
    }
    // Content Management >> Enable Media Replacement
    if ( $current_screen->base == 'upload' || $current_screen->id == 'attachment' ) {
        // wp_enqueue_style( 'asenha-jbox', ASENHA_URL . 'assets/css/jBox.all.min.css', array(), ASENHA_VERSION );
        // wp_enqueue_script( 'asenha-jbox', ASENHA_URL . 'assets/js/jBox.all.min.js', array(), ASENHA_VERSION, false );
        wp_enqueue_style(
            'asenha-media-replace',
            ASENHA_URL . 'assets/css/media-replace.css',
            array(),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-media-replace',
            ASENHA_URL . 'assets/js/media-replace.js',
            array(),
            ASENHA_VERSION,
            false
        );
        $media_replace = array(
            'selectMediaText'        => __( 'Select New Media File', 'admin-site-enhancements' ),
            'performReplacementText' => __( 'Perform Replacement', 'admin-site-enhancements' ),
        );
        wp_localize_script( 'asenha-media-replace', 'mediaReplace', $media_replace );
    }
    // Admin Interface >> Admin Menu Organizer
    if ( 'settings_page_admin-menu-organizer' == $current_screen->base ) {
        // Re-register and re-enqueue jQuery UI Core and plugins required for sortable, draggable and droppable when ordering menu items
        wp_deregister_script( 'jquery-ui-core' );
        wp_register_script(
            'jquery-ui-core',
            get_site_url() . '/wp-includes/js/jquery/ui/core.min.js',
            array('jquery'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script( 'jquery-ui-core' );
        if ( version_compare( $wp_version, '5.6.0', '>=' ) ) {
            wp_deregister_script( 'jquery-ui-mouse' );
            wp_register_script(
                'jquery-ui-mouse',
                get_site_url() . '/wp-includes/js/jquery/ui/mouse.min.js',
                array('jquery-ui-core'),
                ASENHA_VERSION,
                false
            );
            wp_enqueue_script( 'jquery-ui-mouse' );
        } else {
            wp_deregister_script( 'jquery-ui-widget' );
            wp_register_script(
                'jquery-ui-widget',
                get_site_url() . '/wp-includes/js/jquery/ui/widget.min.js',
                array('jquery'),
                ASENHA_VERSION,
                false
            );
            wp_enqueue_script( 'jquery-ui-widget' );
            wp_deregister_script( 'jquery-ui-mouse' );
            wp_register_script(
                'jquery-ui-mouse',
                get_site_url() . '/wp-includes/js/jquery/ui/mouse.min.js',
                array('jquery-ui-core', 'jquery-ui-widget'),
                ASENHA_VERSION,
                false
            );
            wp_enqueue_script( 'jquery-ui-mouse' );
        }
        wp_deregister_script( 'jquery-ui-sortable' );
        wp_register_script(
            'jquery-ui-sortable',
            get_site_url() . '/wp-includes/js/jquery/ui/sortable.min.js',
            array('jquery-ui-mouse'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_deregister_script( 'jquery-ui-draggable' );
        wp_register_script(
            'jquery-ui-draggable',
            get_site_url() . '/wp-includes/js/jquery/ui/draggable.min.js',
            array('jquery-ui-mouse'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_deregister_script( 'jquery-ui-droppable' );
        wp_register_script(
            'jquery-ui-droppable',
            get_site_url() . '/wp-includes/js/jquery/ui/droppable.min.js',
            array('jquery-ui-draggable'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_script( 'jquery-ui-droppable' );
        // Script to set behaviour and actions of the sortable menu
        wp_enqueue_script(
            'asenha-custom-admin-menu',
            ASENHA_URL . 'assets/js/custom-admin-menu.js',
            array('jquery-ui-draggable'),
            ASENHA_VERSION,
            false
        );
        wp_enqueue_style(
            'asenha-admin-menu-organizer',
            ASENHA_URL . 'assets/css/admin-menu-organizer.css',
            array(),
            ASENHA_VERSION
        );
        wp_enqueue_script(
            'asenha-admin-menu-organizer',
            ASENHA_URL . 'assets/js/admin-menu-organizer.js',
            array('asenha-custom-admin-menu'),
            ASENHA_VERSION,
            false
        );
        wp_localize_script( 'asenha-admin-menu-organizer', 'amoPageVars', array(
            'saveMenuNonce'  => wp_create_nonce( 'save-menu-nonce' ),
            'resetMenuNonce' => wp_create_nonce( 'reset-menu-nonce' ),
        ) );
    }
    // Utilities >> Email Delivery Log
    if ( 'tools_page_email-delivery-log' == $hook_suffix ) {
        wp_enqueue_style( 'jquery-ui-css', ASENHA_URL . 'assets/premium/css/jquery-ui/jquery-ui.min.css' );
        wp_enqueue_style( 'asenha-email-delivery-log', ASENHA_URL . 'assets/premium/css/email-delivery-log.css' );
        wp_enqueue_script( 'jquery-ui' );
        // wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'asenha-email-delivery-log', ASENHA_URL . 'assets/premium/js/email-delivery-log.js' );
        $nonce = wp_create_nonce( 'email-delivery-log' . get_current_user_id() );
        wp_localize_script( 'asenha-email-delivery-log', 'emailLog', array(
            'nonce' => $nonce,
        ) );
    }
    // Utilities >> Image Sizes Panel∂
    if ( 'post' == $current_screen->base && 'attachment' == $current_screen->id ) {
        global $post;
        // Only enqueue if the attachment is an image
        if ( property_exists( $post, 'post_mime_type' ) && false !== strpos( $post->post_mime_type, 'image' ) ) {
            wp_enqueue_style( 'asenha-image-sizes-panel', ASENHA_URL . 'assets/css/image-sizes-panel.css' );
        }
    }
    // Code Snippets Manager
    if ( 'post' == $current_screen->base && 'asenha_code_snippet' == $current_screen->id || 'edit' == $current_screen->base && 'edit-asenha_code_snippet' == $current_screen->id ) {
    }
    // Content Management >> Hide Admin Notices
    if ( array_key_exists( 'hide_admin_notices', $options ) && $options['hide_admin_notices'] ) {
        $hide_for_nonadmins = ( isset( $options['hide_admin_notices_for_nonadmins'] ) ? $options['hide_admin_notices_for_nonadmins'] : false );
        $minimum_capability = 'manage_options';
        if ( function_exists( 'bwasenha_fs' ) ) {
            if ( $hide_for_nonadmins && bwasenha_fs()->can_use_premium_code__premium_only() ) {
                $minimum_capability = 'read';
            }
        }
        if ( current_user_can( $minimum_capability ) ) {
            wp_enqueue_style(
                'asenha-jbox',
                ASENHA_URL . 'assets/css/jBox.all.min.css',
                array(),
                ASENHA_VERSION
            );
            wp_enqueue_script(
                'asenha-jbox',
                ASENHA_URL . 'assets/js/jBox.all.min.js',
                array(),
                ASENHA_VERSION,
                false
            );
            wp_enqueue_style(
                'asenha-hide-admin-notices',
                ASENHA_URL . 'assets/css/hide-admin-notices.css',
                array(),
                ASENHA_VERSION
            );
            wp_enqueue_script(
                'asenha-hide-admin-notices',
                ASENHA_URL . 'assets/js/hide-admin-notices.js',
                array('asenha-jbox'),
                ASENHA_VERSION,
                false
            );
        }
    }
    // Utilities >> Multiple User Roles
    if ( array_key_exists( 'multiple_user_roles', $options ) && $options['multiple_user_roles'] ) {
        if ( 'user-edit.php' == $hook_suffix || 'user-new.php' == $hook_suffix ) {
            // Only replace roles dropdown with checkboxes for users that can assign roles to other users, e.g. administrators
            if ( current_user_can( 'promote_users', get_current_user_id() ) ) {
                wp_enqueue_script(
                    'asenha-multiple-user-roles',
                    ASENHA_URL . 'assets/js/multiple-user-roles.js',
                    array('jquery'),
                    ASENHA_VERSION,
                    false
                );
            }
        }
    }
    $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
    $hide_promo_nudge = false;
    $is_yearend_promo_period = is_yearend_promo_period();
    // Pass on ASENHA stats to admin-page.js to determine whether to show support nudge
    $current_date = date( 'Y-m-d', time() );
    $show_support_nudge = false;
    $asenha_stats_localized = array(
        'firstSaveDate'    => '',
        'lastSaveDate'     => '',
        'saveCount'        => 0,
        'hideUpgradeNudge' => false,
        'hidePromoNudge'   => false,
        'showSupportNudge' => false,
    );
    if ( !empty( $asenha_stats ) ) {
        $hide_upgrade_nudge = ( isset( $asenha_stats['upgrade_nudge_dismissed'] ) ? $asenha_stats['upgrade_nudge_dismissed'] : false );
        $hide_promo_nudge = ( isset( $asenha_stats['promo_nudge_dismissed'] ) ? $asenha_stats['promo_nudge_dismissed'] : false );
        $have_supported = ( isset( $asenha_stats['have_supported'] ) ? $asenha_stats['have_supported'] : false );
        $changes_saved = ( isset( $_GET['settings-updated'] ) && 'true' == sanitize_text_field( $_GET['settings-updated'] ) ? true : false );
        $save_count = ( isset( $asenha_stats['save_count'] ) ? $asenha_stats['save_count'] : 0 );
        // Compensate for redirect from settings-updated=true URL
        if ( $changes_saved ) {
            $save_count = $save_count + 1;
        } else {
            $save_count = $save_count;
        }
        $saves_to_nudge_support = 10;
        if ( $save_count < $saves_to_nudge_support ) {
            $save_count_modulo = -1;
        } else {
            $save_count_modulo = $save_count % $saves_to_nudge_support;
        }
        // User have not supported ASE
        if ( false === $have_supported ) {
            // Support nudge have not been dismissed
            if ( isset( $asenha_stats['support_nudge_dismissed'] ) && false === $asenha_stats['support_nudge_dismissed'] ) {
                // Show support nudge after every x saves
                if ( $save_count_modulo >= 0 ) {
                    $show_support_nudge = true;
                } else {
                    $show_support_nudge = false;
                }
                if ( $show_support_nudge && $save_count_modulo >= 0 ) {
                    $asenha_stats['support_nudge_last_shown_date'] = $current_date;
                    $asenha_stats['support_nudge_last_shown_save_count'] = $save_count;
                    update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
                }
            } else {
                if ( $save_count_modulo == 0 ) {
                    $support_nudge_last_shown_save_count = ( isset( $asenha_stats['support_nudge_last_shown_save_count'] ) ? $asenha_stats['support_nudge_last_shown_save_count'] : 0 );
                    if ( $save_count > $support_nudge_last_shown_save_count ) {
                        $asenha_stats['support_nudge_dismissed'] = false;
                        update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
                        $show_support_nudge = true;
                    } else {
                        $show_support_nudge = false;
                    }
                } else {
                    $show_support_nudge = false;
                }
            }
        }
        $first_save_date = ( isset( $asenha_stats['first_save_date'] ) ? $asenha_stats['first_save_date'] : '' );
        $last_save_date = ( isset( $asenha_stats['last_save_date'] ) ? $asenha_stats['last_save_date'] : '' );
        $asenha_stats_localized = array(
            'firstSaveDate'        => $first_save_date,
            'lastSaveDate'         => $last_save_date,
            'saveCount'            => $save_count,
            'isYearEndPromoPeriod' => $is_yearend_promo_period,
            'hideUpgradeNudge'     => $hide_upgrade_nudge,
            'hidePromoNudge'       => $hide_promo_nudge,
            'showSupportNudge'     => $show_support_nudge,
        );
    }
    wp_localize_script( 'asenha-admin-page', 'asenhaStats', $asenha_stats_localized );
}

/**
 * Inline CSS for Admin Menu Organizer in all wp-admin pages. Previously loaded externally as part of wp-admin.css file
 * 
 * @since 7.6.11
 */
function asenha_admin_menu_organizer_css() {
    ?>
	<style type="text/css">
	/* Admin Interface >> Admin Menu Organizer */
	#adminmenuwrap {
		height: auto !important;
	}

	.toplevel_page_wpide #adminmenuwrap {
		height: calc(100vh - var(--wpide-admin-bar-height)) !important;
	} /* Fix for when in WPIDE plugin's menu / admin page */

	/* Hide "Show Less" toggle by default to avoid initial flash before JS runs. */
	#toplevel_page_asenha_hide_hidden_menu {
		display: none;
	}

	.current.menu-top.hidden,
	.wp-has-current-submenu.hidden {
		display: list-item;
	}

	#adminmenu a.menu-top.hidden,
	ul#adminmenu a.wp-has-current-submenu.hidden {
		display: block;
	}

	/*! <fs_premium_only> */
	.always-hidden {
		display: none !important;
	}

	#adminmenu .wp-submenu a.hidden {
		display: none;
	}
	/*! </fs_premium_only> */
	</style>
	<?php 
}

/**
 * Dequeue scripts that prevents ASE settings page from working properly. Usually from plugins.
 * 
 * @since 6.3.3
 */
function asenha_dequeue_scritps() {
    if ( is_asenha() ) {
        // https://wordpress.org/plugins/user-activity-log/
        wp_dequeue_script( 'chats-js' );
        wp_dequeue_script( 'custom_wp_admin_js' );
        // https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce/
        wp_dequeue_script( 'print-invoices-packing-slip-labels-for-woocommerce' );
        wp_dequeue_script( 'print-invoices-packing-slip-labels-for-woocommerce-form-wizard' );
        // https://wordpress.org/plugins/wp-reading-progress/
        wp_dequeue_script( 'ruigehond006_admin_javascript' );
        // WordPress Mentions Légales plugin v1.2.3 by Jean-Baptiste Aramendy - http://jba-development.fr/
        wp_dequeue_script( 'jquery-ui' );
        wp_dequeue_script( 'wordpress-mentions-legales' );
        // https://wordpress.org/plugins/us-weather-widget-willyweather/
        wp_dequeue_script( 'self' );
        // iThemes Security Pro / Solid Security Pro
        wp_dequeue_script( 'itsec-core-admin-notices' );
        // WPML
        wp_dequeue_script( 'wpml-ate-jobs-sync-ui' );
        wp_dequeue_script( 'wpml-purify' );
        wp_dequeue_script( 'sitepress-scripts' );
        wp_dequeue_script( 'sitepress' );
        wp_dequeue_script( 'wpml-dismiss-notice' );
        wp_dequeue_script( 'wpml-tm-scripts' );
        wp_dequeue_script( 'otgs-installer-components-save-setting' );
        wp_dequeue_script( 'installer-dismiss-nag' );
        wp_dequeue_script( 'install-recommended_plugin' );
        wp_dequeue_script( 'icl-admin-notifier' );
        wp_dequeue_script( 'otgsPopoverTooltip' );
        // WPML String Translation
        wp_dequeue_script( 'wpml-theme-plugin-localization-scan' );
        // Asset Cleanup
        wp_dequeue_script( 'wpassetcleanup-script' );
    }
}

/**
 * Enqueue public scripts
 *
 * @since 3.9.0
 */
function asenha_public_scripts(  $hook_suffix  ) {
    // Get all WP Enhancements options, default to empty array in case it's not been created yet
    $options = get_option( 'admin_site_enhancements', array() );
    // External Permalinks
    $enable_external_permalinks = ( array_key_exists( 'enable_external_permalinks', $options ) ? $options['enable_external_permalinks'] : false );
    if ( $enable_external_permalinks ) {
        wp_enqueue_script(
            'asenha-public',
            ASENHA_URL . 'assets/js/external-permalinks.js',
            array(),
            ASENHA_VERSION,
            false
        );
        wp_localize_script( 'asenha-public', 'phpVars', array(
            'externalPermalinksEnabled' => $enable_external_permalinks,
        ) );
    }
    // Media Categories
    $enable_media_categories = ( array_key_exists( 'enable_media_categories', $options ) ? $options['enable_media_categories'] : false );
    if ( $enable_media_categories && !is_admin() && is_user_logged_in() ) {
        wp_enqueue_style(
            'asenha-media-categories-frontend',
            ASENHA_URL . 'assets/css/media-categories-frontend.css',
            array(),
            ASENHA_VERSION
        );
    }
    // Media Replacement
    $enable_media_replacement = ( array_key_exists( 'enable_media_replacement', $options ) ? $options['enable_media_replacement'] : false );
    if ( $enable_media_replacement && !is_admin() && is_user_logged_in() ) {
        wp_enqueue_style(
            'asenha-media-replace-frontend',
            ASENHA_URL . 'assets/css/media-replace-frontend.css',
            array(),
            ASENHA_VERSION
        );
    }
}

/**
 * Add admin bar styles for wp-admin and frontend
 * 
 * @since 6.2.1
 */
function asenha_admin_bar_item_js_css() {
    if ( is_user_logged_in() ) {
        ?>
		<!--<script></script>-->
		<style>
		#wp-admin-bar-user-info .avatar {
		    object-fit: cover;    
		}
		</style>
		<?php 
    }
}

/**
 * Add 'Access now' plugin action link.
 *
 * @since    1.0.0
 */
function asenha_plugin_action_links(  $links  ) {
    $settings_link = '<a href="tools.php?page=' . ASENHA_SLUG . '">' . __( 'Configure', 'admin-site-enhancements' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Modify footer text
 *
 * @since 1.0.0
 */
function asenha_footer_text() {
    // Show nothing
    ?>
	<?php 
}

/**
 * Change WP version number text in footer
 * 
 * @since 4.8.3
 */
function asenha_footer_version_text() {
    ?>
		ASE <a href="https://www.wpase.com/documentation/changelog/" target="_blank">v<?php 
    echo esc_html( ASENHA_VERSION );
    ?></a>
	<?php 
}

/**
 * Check if current screen is this plugin's main page
 *
 * @since 1.0.0
 */
function is_asenha() {
    $request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );
    // e.g. /wp-admin/index.php?page=page-slug
    if ( strpos( $request_uri, 'page=' . ASENHA_SLUG ) !== false ) {
        return true;
        // Yes, this is the plugin's main page
    } else {
        return false;
        // Nope, this is NOT the plugin's page
    }
}

/**
 * Mark that user have supported ASE
 * 
 * @since 5.2.7
 */
function asenha_have_supported() {
    if ( isset( $_REQUEST ) && current_user_can( 'manage_options' ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'asenha-' . get_current_user_id() ) ) {
            $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
            $asenha_stats['have_supported'] = true;
            $asenha_stats['support_nudge_dismissed'] = true;
            $success = update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
            if ( $success ) {
                echo json_encode( array(
                    'success' => true,
                ) );
            } else {
                echo json_encode( array(
                    'success' => false,
                ) );
            }
        }
    }
}

/**
 * Dismiss support nudge
 * 
 * @since 5.8.2
 */
function asenha_dismiss_upgrade_nudge() {
    if ( isset( $_REQUEST ) && current_user_can( 'manage_options' ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'asenha-' . get_current_user_id() ) ) {
            $current_date = date( 'Y-m-d', time() );
            $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
            $asenha_stats['upgrade_nudge_dismissed'] = true;
            $asenha_stats['upgrade_nudge_dismissed_date'] = $current_date;
            $success = update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
            if ( $success ) {
                echo json_encode( array(
                    'success' => true,
                ) );
            } else {
                echo json_encode( array(
                    'success' => false,
                ) );
            }
        }
    }
}

/**
 * Dismiss promo nudge
 * 
 * @since 7.5.1
 */
function asenha_dismiss_promo_nudge() {
    if ( isset( $_REQUEST ) && current_user_can( 'manage_options' ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'asenha-' . get_current_user_id() ) ) {
            $current_date = date( 'Y-m-d', time() );
            $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
            $asenha_stats['promo_nudge_dismissed'] = true;
            $asenha_stats['promo_nudge_dismissed_date'] = $current_date;
            $success = update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
            if ( $success ) {
                echo json_encode( array(
                    'success' => true,
                ) );
            } else {
                echo json_encode( array(
                    'success' => false,
                ) );
            }
        }
    }
}

/**
 * Dismiss support nudge
 * 
 * @since 5.2.7
 */
function asenha_dismiss_support_nudge() {
    if ( isset( $_REQUEST ) && current_user_can( 'manage_options' ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'asenha-' . get_current_user_id() ) ) {
            $asenha_stats = get_option( ASENHA_SLUG_U . '_stats', array() );
            $asenha_stats['support_nudge_dismissed'] = true;
            $success = update_option( ASENHA_SLUG_U . '_stats', $asenha_stats, false );
            if ( $success ) {
                echo json_encode( array(
                    'success' => true,
                ) );
            } else {
                echo json_encode( array(
                    'success' => false,
                ) );
            }
        }
    }
}

/**
 * Release lock for an IP address in Limit Login Attempts.
 *
 * Deletes the IP entry from the failed login attempts table, which immediately
 * releases the lockout for that IP address.
 *
 * @since 8.3.1
 */
function asenha_release_login_lock() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ),
        ) );
    }
    if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['ip_address'] ) ) {
        wp_send_json_error( array(
            'message' => __( 'Missing required data.', 'admin-site-enhancements' ),
        ) );
    }
    $nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) );
    if ( !wp_verify_nonce( $nonce, 'asenha-' . get_current_user_id() ) ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid security token.', 'admin-site-enhancements' ),
        ) );
    }
    $ip_address = sanitize_text_field( wp_unslash( $_REQUEST['ip_address'] ) );
    $common_methods = new \ASENHA\Classes\Common_Methods();
    if ( !$common_methods->is_ip_valid( $ip_address ) ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid IP address.', 'admin-site-enhancements' ),
        ) );
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'asenha_failed_logins';
    $deleted = $wpdb->delete( $table_name, array(
        'ip_address' => $ip_address,
    ), array('%s') );
    if ( false === $deleted ) {
        wp_send_json_error( array(
            'message' => __( 'Could not release lock.', 'admin-site-enhancements' ),
        ) );
    }
    wp_send_json_success( array(
        'deleted' => (int) $deleted,
    ) );
}
