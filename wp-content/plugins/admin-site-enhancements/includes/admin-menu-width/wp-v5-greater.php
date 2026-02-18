<?php

if ( ! is_rtl() ) {
	$margin_property = 'margin-left';
	$position = 'left';
} else {
	$margin_property = 'margin-right';
	$position = 'right';
}

/* Gravity Form - Form Editor */
$custom_width_no_px = intval( str_replace( 'px', '', $custom_width ) );
$gf_editor_width = 594 - 160 + $custom_width_no_px;
$gf_editor_width = (string) $gf_editor_width . 'px';
$fluent_cart_inner_wrapper = $custom_width_no_px - 30;
$fluent_cart_inner_wrapper = (string) $fluent_cart_inner_wrapper . 'px';

?>
<style>

#wpcontent, #wpfooter {
	<?php echo esc_html( esc_html( $margin_property ) ); ?>: <?php echo esc_html( esc_html( $custom_width ) ); ?>;
}

#adminmenuback, #adminmenuwrap, #adminmenu, #adminmenu .wp-submenu {
	width: <?php echo esc_html( $custom_width ); ?>;
}

#adminmenu .wp-submenu {
	<?php echo esc_html( $position ); ?>: <?php echo esc_html( $custom_width ); ?>;
}

#adminmenu .wp-not-current-submenu .wp-submenu, .folded #adminmenu .wp-has-current-submenu .wp-submenu {
	min-width: <?php echo esc_html( $custom_width ); ?>;
}

@media (min-width:960px) {
	/* WooCommerce header fix */
	.woocommerce-layout__header,
	/* Elementor Settings */
	#e-admin-top-bar-root {
		width: calc(100% - <?php echo esc_html( $custom_width ); ?>);
	}	
}

/* Gutenberg / Block Editor fix */
.auto-fold .interface-interface-skeleton {
	<?php echo esc_html( $position ); ?>: <?php echo esc_html( $custom_width ); ?>;	
}

/* ASE Form Builder */
.fb-header-nav {
    width: calc(100% - <?php echo esc_html( esc_html( $custom_width ) ); ?>) !important;
}

/* Gravity Form - Form Editor */
body.toplevel_page_gf_edit_forms .gform-form-toolbar {
	width: calc(100% - <?php echo esc_html( esc_html( $custom_width ) ); ?>) !important;
}

.form_editor_fields_container {
	max-width: calc(100% - <?php echo esc_html( esc_html( $gf_editor_width ) ); ?>) !important;
}

/* Formidable Forms */
.auto-fold.frm-admin-page-styles:not(.frm-full-screen) .frm_page_container, 
.auto-fold:not(.frm-full-screen) .frm_wrap .frm_page_container, 
.frm-unfold.frm-admin-page-styles:not(.frm-full-screen) .frm_page_container, 
.frm-unfold:not(.frm-full-screen) .frm_wrap .frm_page_container {
	left: <?php echo esc_html( $custom_width ); ?> !important;
}

/* FluentCart */
#wpbody-content #fct_admin_menu_holder .fct_admin_menu_wrap{
	width: calc(100% - <?php echo esc_html( esc_html( $custom_width ) ); ?>) !important;;
}

.fct-reports-view-inner {
	padding-left: <?php echo esc_html( esc_html( $fluent_cart_inner_wrapper ) ); ?> !important;
}

.fct-setting-container .fct-tab-wrapper {
	padding-left: <?php echo esc_html( $custom_width ); ?> !important;
}

</style>