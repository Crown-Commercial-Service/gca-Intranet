<?php

namespace ASENHA\Classes;

use WP_Admin_Bar;
/**
 * Class for Hide Admin Notices module
 *
 * @since 6.9.5
 */
class Hide_Admin_Notices {
    /**
     * Wrapper for admin notices being output on admin screens
     *
     * @since 1.2.0
     */
    public function admin_notices_wrapper() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $hide_for_nonadmins = ( isset( $options['hide_admin_notices_for_nonadmins'] ) ? $options['hide_admin_notices_for_nonadmins'] : false );
        $minimum_capability = 'manage_options';
        if ( current_user_can( $minimum_capability ) ) {
            echo '<div class="asenha-admin-notices-drawer" style="display:none;"><h2>' . __( 'Admin Notices', 'admin-site-enhancements' ) . '</h2></div>';
        }
    }

    /**
     * FOR TESTING: show an admin notice that is visible for all user roles
     * 
     * @since 8.0.4
     */
    public function show_test_admin_notice_for_all_user_roles() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php 
        esc_html_e( "This notice is visible for all user roles.", 'wpturbo' );
        ?></p>
        </div>
        <?php 
    }

    /**
     * Admin bar menu item for the hidden admin notices
     *
     * @link https://developer.wordpress.org/reference/classes/wp_admin_bar/add_menu/
     * @link https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
     * @since 1.2.0
     */
    public function admin_notices_menu( WP_Admin_Bar $wp_admin_bar ) {
        // Only show Notices menu in wp-admin but when not in Customizer preview
        if ( is_admin() && !is_customize_preview() ) {
            $options = get_option( ASENHA_SLUG_U, array() );
            $hide_for_nonadmins = ( isset( $options['hide_admin_notices_for_nonadmins'] ) ? $options['hide_admin_notices_for_nonadmins'] : false );
            $hide_menu_for_nonadmins = ( isset( $options['hide_admin_notices_menu_for_nonadmins'] ) ? $options['hide_admin_notices_menu_for_nonadmins'] : false );
            $minimum_capability = 'manage_options';
            if ( current_user_can( $minimum_capability ) ) {
                $wp_admin_bar->add_menu( array(
                    'id'     => 'asenha-hide-admin-notices',
                    'parent' => 'top-secondary',
                    'group'  => null,
                    'title'  => __( 'Notices', 'admin-site-enhancements' ) . '<span class="asenha-admin-notices-counter" style="opacity:0;">0</span>',
                    'meta'   => array(
                        'class' => 'asenha-admin-notices-menu hidden',
                        'title' => __( 'Click to view hidden admin notices', 'admin-site-enhancements' ),
                    ),
                ) );
            }
        }
    }

    /**
     * Inline CSS for the hiding notices on page load in wp admin pages
     *
     * @since 1.2.0
     */
    public function admin_notices_menu_inline_css() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $hide_for_nonadmins = ( isset( $options['hide_admin_notices_for_nonadmins'] ) ? $options['hide_admin_notices_for_nonadmins'] : false );
        $minimum_capability = 'manage_options';
        if ( is_admin() && !is_customize_preview() && current_user_can( $minimum_capability ) ) {
            // Below we pre-emptively hide notices to avoid having them shown briefly before being moved into the notices panel via JS
            ?>
            <style type="text/css">
                #wpadminbar .asenha-admin-notices-menu .ab-empty-item {
                    cursor: pointer;
                }
                
                #wpadminbar .asenha-admin-notices-counter {
                    box-sizing: border-box;
                    margin: 1px 0 -1px 6px ;
                    padding: 2px 6px 3px 5px;
                    min-width: 18px;
                    height: 18px;
                    border-radius: 50%;
                    background-color: #ca4a1f;
                    color: #fff;
                    font-size: 11px;
                    line-height: 1.6;
                    text-align: center;
                }

                /* #wpbody-content .notice:not(.system-notice,.update-message),
                #wpbody-content .notice-error,
                #wpbody-content .error,
                #wpbody-content .notice-info,
                #wpbody-content .notice-information,
                #wpbody-content #message,
                #wpbody-content .notice-warning:not(.update-message),
                #wpbody-content .notice-success:not(.update-message),
                #wpbody-content .notice-updated,
                #wpbody-content .updated:not(.active, .inactive, .plugin-update-tr),
                #wpbody-content .update-nag, */
                #wpbody-content > .wrap > .notice:not(#plugin-activated-successfully,.system-notice,.updated,.hidden,.inline,.wcml-notice,.asenha-media-replacement-notice),
                #wpbody-content > .wrap > .notice-error,
                #wpbody-content > .wrap > .error:not(.hidden),
                #wpbody-content > .wrap > .notice-info,
                #wpbody-content > .wrap > .notice-information,
                #wpbody-content > .wrap > #message:not(.updated,.asenha-media-replacement-notice),
                #wpbody-content > .wrap > .notice-warning:not(.hidden),
                #wpbody-content > .wrap > .notice-success:not(.updated,#plugin-activated-successfully,.updated,.asenha-media-replacement-notice),
                #wpbody-content > .wrap > .notice-updated,
                #wpbody-content > .wrap > .updated:not(.inline),
                #wpbody-content > .wrap > .update-nag,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice:not(.system-notice,.hidden),
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-error,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .error:not(.hidden),
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-info,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-information,
                #wpbody-content > .wrap > div > #message,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-warning:not(.hidden),
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-success,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-updated,
                #wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .updated,
                #wpbody-content > .wrap > div > .update-nag,
                #wpbody-content > div > .wrap > .notice:not(.system-notice,.hidden),
                #wpbody-content > div > .wrap > .notice-error,
                #wpbody-content > div > .wrap > .error:not(.hidden),
                #wpbody-content > div > .wrap > .notice-info,
                #wpbody-content > div > .wrap > .notice-information,
                #wpbody-content > div > .wrap > #message,
                #wpbody-content > div > .wrap > .notice-warning:not(.hidden),
                #wpbody-content > div > .wrap > .notice-success,
                #wpbody-content > div > .wrap > .notice-updated,
                #wpbody-content > div > .wrap > .updated:not(.inline),
                #wpbody-content > div > .wrap > .update-nag,
                /* e.g. on user deletion screen */
                #wpbody-content > form > .wrap > .notice:not(.system-notice,.hidden),
                #wpbody-content > form > .wrap > .notice-error,
                #wpbody-content > form > .wrap > .error:not(.hidden),
                #wpbody-content > form > .wrap > .notice-info,
                #wpbody-content > form > .wrap > .notice-information,
                #wpbody-content > form > .wrap > #message,
                #wpbody-content > form > .wrap > .notice-warning:not(.hidden),
                #wpbody-content > form > .wrap > .notice-success,
                #wpbody-content > form > .wrap > .notice-updated,
                #wpbody-content > form > .wrap > .updated:not(.inline),
                #wpbody-content > form > .wrap > .update-nag,
                /* WooCommerce */
                #wpbody-content > .wrap.woocommerce > form > .notice:not(.system-notice,.hidden),
                #wpbody-content > .wrap.woocommerce > form > .notice-error,
                #wpbody-content > .wrap.woocommerce > form > .error:not(.hidden),
                #wpbody-content > .wrap.woocommerce > form > .notice-info,
                #wpbody-content > .wrap.woocommerce > form > .notice-information,
                #wpbody-content > .wrap.woocommerce > form > #message,
                #wpbody-content > .wrap.woocommerce > form > .notice-warning:not(.hidden),
                #wpbody-content > .wrap.woocommerce > form > .notice-success,
                #wpbody-content > .wrap.woocommerce > form > .notice-updated,
                #wpbody-content > .wrap.woocommerce > form > .updated:not(.inline),
                #wpbody-content > .wrap.woocommerce > form > .update-nag,
                /* TranslatePress */
                #wpbody-content > #trp-main-settings > form > .notice:not(.system-notice,.hidden),
                #wpbody-content > #trp-main-settings > form > .notice-error,
                #wpbody-content > #trp-main-settings > form > .error:not(.hidden),
                #wpbody-content > #trp-main-settings > form > .notice-info,
                #wpbody-content > #trp-main-settings > form > .notice-information,
                #wpbody-content > #trp-main-settings > form > #message,
                #wpbody-content > #trp-main-settings > form > .notice-warning:not(.hidden),
                #wpbody-content > #trp-main-settings > form > .notice-success,
                #wpbody-content > #trp-main-settings > form > .notice-updated,
                #wpbody-content > #trp-main-settings > form > .updated:not(.inline),
                #wpbody-content > #trp-main-settings > form > .update-nag,
                /* WordFence */
                #wpbody-content > .wrap > .wf-container-fluid .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                #wpbody-content > .wrap > .wf-container-fluid .notice-error,
                #wpbody-content > .wrap > .wf-container-fluid .error:not(.hidden),
                #wpbody-content > .wrap > .wf-container-fluid .notice-info,
                #wpbody-content > .wrap > .wf-container-fluid .notice-information,
                #wpbody-content > .wrap > .wf-container-fluid #message,
                #wpbody-content > .wrap > .wf-container-fluid .notice-warning:not(.hidden),
                #wpbody-content > .wrap > .wf-container-fluid .notice-success:not(#plugin-activated-successfully),
                #wpbody-content > .wrap > .wf-container-fluid .notice-updated,
                #wpbody-content > .wrap > .wf-container-fluid .updated:not(.inline),
                #wpbody-content > .wrap > .wf-container-fluid .update-nag,
                /* WP All Import */
                #wpbody-content > .wrap .wpallimport-wrapper .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                #wpbody-content > .wrap .wpallimport-wrapper .notice-error,
                #wpbody-content > .wrap .wpallimport-wrapper .error:not(.hidden),
                #wpbody-content > .wrap .wpallimport-wrapper .notice-info,
                #wpbody-content > .wrap .wpallimport-wrapper .notice-information,
                #wpbody-content > .wrap .wpallimport-wrapper #message,
                #wpbody-content > .wrap .wpallimport-wrapper .notice-warning:not(.hidden),
                #wpbody-content > .wrap .wpallimport-wrapper .notice-success:not(#plugin-activated-successfully),
                #wpbody-content > .wrap .wpallimport-wrapper .notice-updated,
                #wpbody-content > .wrap .wpallimport-wrapper .updated:not(.inline),
                #wpbody-content > .wrap .wpallimport-wrapper .update-nag,
                /* WP All Export */
                #wpbody-content > .wrap .wpallexport-wrapper .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                #wpbody-content > .wrap .wpallexport-wrapper .notice-error,
                #wpbody-content > .wrap .wpallexport-wrapper .error:not(.hidden),
                #wpbody-content > .wrap .wpallexport-wrapper .notice-info,
                #wpbody-content > .wrap .wpallexport-wrapper .notice-information,
                #wpbody-content > .wrap .wpallexport-wrapper #message,
                #wpbody-content > .wrap .wpallexport-wrapper .notice-warning:not(.hidden),
                #wpbody-content > .wrap .wpallexport-wrapper .notice-success:not(#plugin-activated-successfully),
                #wpbody-content > .wrap .wpallexport-wrapper .notice-updated,
                #wpbody-content > .wrap .wpallexport-wrapper .updated:not(.inline),
                #wpbody-content > .wrap .wpallexport-wrapper .update-nag,
                /* WS Form */
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-error,
                #wpbody-content > #wsf-layout-editor > #poststuff > .error:not(.hidden),
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-info,
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-information,
                #wpbody-content > #wsf-layout-editor > #poststuff > #message,
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-warning:not(.hidden),
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-success:not(#plugin-activated-successfully),
                #wpbody-content > #wsf-layout-editor > #poststuff > .notice-updated,
                #wpbody-content > #wsf-layout-editor > #poststuff > .updated:not(.inline),
                #wpbody-content > #wsf-layout-editor > #poststuff > .update-nag,
                /* Pods */
                #wpbody-content .pods-submittable-fields > .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                #wpbody-content .pods-submittable-fields > .notice-error,
                #wpbody-content .pods-submittable-fields > .error:not(.hidden),
                #wpbody-content .pods-submittable-fields > .notice-info,
                #wpbody-content .pods-submittable-fields > .notice-information,
                #wpbody-content .pods-submittable-fields > #message,
                #wpbody-content .pods-submittable-fields > .notice-warning:not(.hidden),
                #wpbody-content .pods-submittable-fields > .notice-success:not(#plugin-activated-successfully),
                #wpbody-content .pods-submittable-fields > .notice-updated,
                #wpbody-content .pods-submittable-fields > .updated:not(.inline),
                #wpbody-content .pods-submittable-fields > .update-nag,
                /* Meta Box Lite */
                .mb-main > .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                .mb-main > .notice-error,
                .mb-main > .error:not(.hidden),
                .mb-main > .notice-info,
                .mb-main > .notice-information,
                .mb-main > #message,
                .mb-main > .notice-warning:not(.hidden),
                .mb-main > .notice-success:not(#plugin-activated-successfully),
                .mb-main > .notice-updated,
                .mb-main > .updated:not(.inline),
                .mb-main > .update-nag,
                /* Meta Box AIO */
                .mb-header__left > .notice:not(#plugin-activated-successfully,.system-notice,.hidden),
                .mb-header__left > .notice-error,
                .mb-header__left > .error:not(.hidden),
                .mb-header__left > .notice-info,
                .mb-header__left > .notice-information,
                .mb-header__left > #message,
                .mb-header__left > .notice-warning:not(.hidden),
                .mb-header__left > .notice-success:not(#plugin-activated-successfully),
                .mb-header__left > .notice-updated,
                .mb-header__left > .updated:not(.inline),
                .mb-header__left > .update-nag,
                /* Funnel Builder for WordPress by FunnelKit */
                #wpbody-content > .bwfan_header > .notice:not(.system-notice,.hidden),
                #wpbody-content > .bwfan_header > .notice-error,
                #wpbody-content > .bwfan_header > .error:not(.hidden),
                #wpbody-content > .bwfan_header > .notice-info,
                #wpbody-content > .bwfan_header > .notice-information,
                #wpbody-content > .bwfan_header > #message,
                #wpbody-content > .bwfan_header > .notice-warning:not(.hidden),
                #wpbody-content > .bwfan_header > .notice-success,
                #wpbody-content > .bwfan_header > .notice-updated,
                #wpbody-content > .bwfan_header > .updated:not(.inline),
                #wpbody-content > .bwfan_header > .update-nag,
                #wpbody-content > .notice:not(.otgs-notice,.wcml-notice),
                #wpbody-content > .error,
                #wpbody-content > .updated:not(.inline),
                #wpbody-content > .update-nag,
                #wpbody-content > .jp-connection-banner,
                #wpbody-content > .jitm-banner,
                #wpbody-content > .jetpack-jitm-message,
                #wpbody-content > .ngg_admin_notice,
                #wpbody-content > .imagify-welcome,
                #wpbody-content #wordfenceAutoUpdateChoice,
                #wpbody-content #easy-updates-manager-dashnotice,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice:not(.system-notice,.hidden),
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-error,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .error:not(.hidden),
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-info,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-information,
                #wpbody-content > .wrap.gblocks-dashboard-wrap #message,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-warning:not(.hidden),
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-success,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .notice-updated,
                #wpbody-content > .wrap.gblocks-dashboard-wrap .updated:not(.inline),
                #wpbody-content > .wrap.gblocks-dashboard-wrap .update-nag,
                /* WPML */
                #wpbody-content > .otgs-notice,
                /* WooCommerce Stock Sync */
                #wpbody-content > .wrap > .ssgs-influencer-banner,
                #wpbody-content > .wrap > .ssgs-upgrade-banner,
                #wpbody-content > .wrap > .ssgs-rating-banner,
                /* Shortpixel */
                #wpbody-content > .shortpixel-notice,
                /* Dokan */
                #wpbody-content > .wrap .dokan-dashboard .notice:not(.system-notice,.hidden,.wcml-notice),
                #wpbody-content > .wrap .dokan-dashboard .notice-error,
                #wpbody-content > .wrap .dokan-dashboard .error:not(.hidden),
                #wpbody-content > .wrap .dokan-dashboard .notice-info,
                #wpbody-content > .wrap .dokan-dashboard .notice-information,
                #wpbody-content > .wrap .dokan-dashboard #message,
                #wpbody-content > .wrap .dokan-dashboard .notice-warning:not(.hidden),
                #wpbody-content > .wrap .dokan-dashboard .notice-success:not(#plugin-activated-successfully),
                #wpbody-content > .wrap .dokan-dashboard .notice-updated,
                #wpbody-content > .wrap .dokan-dashboard .updated:not(.inline),
                #wpbody-content > .wrap .dokan-dashboard .update-nag,
                /* BdThemes Element Pack Pro */
                #wpbody-content > .wrap > .biggopti
                {
                    position: absolute !important;
                    visibility: hidden !important;
                }
            </style>
            <?php 
        }
    }

}
