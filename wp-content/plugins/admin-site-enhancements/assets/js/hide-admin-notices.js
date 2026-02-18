(function( $ ) {
   'use strict';

   $(document).ready( function() {
   	var noticesCount;

		// Move admin notices at an interval after document is ready and WP core moves the position of notices under the page heading.
	   	// https://plugins.trac.wordpress.org/browser/hide-admin-notices/tags/1.2.2/assets/js/hide-admin-notices.js
	   	// https://plugins.trac.wordpress.org/browser/admin-notices-manager/tags/1.3.1/assets/js/admin/notices.js

		var noticesPanel = $('.asenha-admin-notices-drawer');
		var startTime = new Date().getTime();

		var interval = setInterval(function () {

			// Stop monitoring after 1,05 seconds (some notices has a 1 second delay, so, we make this slightly longer)
			if (new Date().getTime() - startTime > 1050) {
				clearInterval(interval);

		   		// Count hidden notices and append into admin bar counter
		   		noticesCount = $('.asenha-admin-notices-drawer > div').length;

		   		if ( noticesCount > 0 ) {
		   			$('.asenha-admin-notices-menu').show(); // show admin bar menu
		   			// $('.asenha-admin-notices-counter').show(); // show counter
			   		$('.asenha-admin-notices-counter').html(noticesCount); // insert count
		   			$('.asenha-admin-notices-counter').css("opacity", "1"); // show counter
		   		} else {
		   			$('.asenha-admin-notices-menu').hide(); // hide admin bar menu
		   		}

				return;
			}
			
			// Plugins that outputs notices. For testing.
			// Ajax Press - https://wordpress.org/plugins/ajax-press/
			// Atlas Search - https://wordpress.org/plugins/atlas-search/
			// ExactMetrics Analytics - https://wordpress.org/plugins/google-analytics-dashboard-for-wp/
			// JetPack - https://wordpress.org/plugins/jetpack/
			// ManageWP Worker - https://wordpress.org/plugins/worker/
			// WP Smushit - https://wordpress.org/plugins/wp-smushit/
			// FluentSMTP - https://wordpress.org/plugins/fluent-smtp/
			// WP Backend File Search - https://wordpress.org/plugins/wp-backend-file-search-editor-tweaks/
			// TotalPress Custom post types - https://wordpress.org/plugins/custom-post-types/

			// Reposition notices with the following selectors. Excluding 'notice-system'.
			$('#wpbody-content > .wrap > .notice:not(#plugin-activated-successfully,.system-notice,.updated,.hidden,.inline,.wcml-notice,.asenha-media-replacement-notice),'
			+ '#wpbody-content > .wrap > .notice-error,'
			+ '#wpbody-content > .wrap > .error:not(.hidden),'
			+ '#wpbody-content > .wrap > .notice-info,'
			+ '#wpbody-content > .wrap > .notice-information,'
			+ '#wpbody-content > .wrap > #message:not(.updated,.asenha-media-replacement-notice),'
			+ '#wpbody-content > .wrap > .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap > .notice-success:not(.updated,#plugin-activated-successfully,.asenha-media-replacement-notice),'
			+ '#wpbody-content > .wrap > .notice-updated,'
			+ '#wpbody-content > .wrap > .updated:not(.inline),'
			+ '#wpbody-content > .wrap > .update-nag,'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-error,'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .error:not(.hidden),'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-info,'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-information,'
			+ '#wpbody-content > .wrap > div > #message,'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .notice-success,'
			+ '#wpbody-content > .wrap > div :not(#loco-notices,#loco-content)> .notice-updated,'
			+ '#wpbody-content > .wrap > div:not(#loco-notices,#loco-content) > .updated:not(.inline),'
			+ '#wpbody-content > .wrap > div > .update-nag,'
			+ '#wpbody-content > div > .wrap > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > div > .wrap > .notice-error,'
			+ '#wpbody-content > div > .wrap > .error:not(.hidden),'
			+ '#wpbody-content > div > .wrap > .notice-info,'
			+ '#wpbody-content > div > .wrap > .notice-information,'
			+ '#wpbody-content > div > .wrap > #message,'
			+ '#wpbody-content > div > .wrap > .notice-warning:not(.hidden),'
			+ '#wpbody-content > div > .wrap > .notice-success,'
			+ '#wpbody-content > div > .wrap > .notice-updated,'
			+ '#wpbody-content > div > .wrap > .updated:not(.inline),'
			+ '#wpbody-content > div > .wrap > .update-nag,'
			// e.g. on user deletion screen
			+ '#wpbody-content > form > .wrap > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > form > .wrap > .notice-error,'
			+ '#wpbody-content > form > .wrap > .error:not(.hidden),'
			+ '#wpbody-content > form > .wrap > .notice-info,'
			+ '#wpbody-content > form > .wrap > .notice-information,'
			+ '#wpbody-content > form > .wrap > #message,'
			+ '#wpbody-content > form > .wrap > .notice-warning:not(.hidden),'
			+ '#wpbody-content > form > .wrap > .notice-success,'
			+ '#wpbody-content > form > .wrap > .notice-updated,'
			+ '#wpbody-content > form > .wrap > .updated:not(.inline),'
			+ '#wpbody-content > form > .wrap > .update-nag,'
			// WooCommerce
			+ '#wpbody-content > .wrap.woocommerce > form > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-error,'
			+ '#wpbody-content > .wrap.woocommerce > form > .error:not(.hidden),'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-info,'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-information,'
			+ '#wpbody-content > .wrap.woocommerce > form > #message,'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-success,'
			+ '#wpbody-content > .wrap.woocommerce > form > .notice-updated,'
			+ '#wpbody-content > .wrap.woocommerce > form > .updated:not(.inline),'
			+ '#wpbody-content > .wrap.woocommerce > form > .update-nag,'
			// TranslatePress
			+ '#wpbody-content > #trp-main-settings > form > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > #trp-main-settings > form > .notice-error,'
			+ '#wpbody-content > #trp-main-settings > form > .error:not(.hidden),'
			+ '#wpbody-content > #trp-main-settings > form > .notice-info,'
			+ '#wpbody-content > #trp-main-settings > form > .notice-information,'
			+ '#wpbody-content > #trp-main-settings > form > #message,'
			+ '#wpbody-content > #trp-main-settings > form > .notice-warning:not(.hidden),'
			+ '#wpbody-content > #trp-main-settings > form > .notice-success,'
			+ '#wpbody-content > #trp-main-settings > form > .notice-updated,'
			+ '#wpbody-content > #trp-main-settings > form > .updated:not(.inline),'
			+ '#wpbody-content > #trp-main-settings > form > .update-nag,'
			// WordFence
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-error,'
			+ '#wpbody-content > .wrap > .wf-container-fluid .error:not(.hidden),'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-info,'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-information,'
			+ '#wpbody-content > .wrap > .wf-container-fluid #message,'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-success,'
			+ '#wpbody-content > .wrap > .wf-container-fluid .notice-updated,'
			+ '#wpbody-content > .wrap > .wf-container-fluid .updated:not(.inline),'
			+ '#wpbody-content > .wrap > .wf-container-fluid .update-nag,'
			// WP All Import
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-error,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .error:not(.hidden),'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-info,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-information,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper #message,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-success,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .notice-updated,'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .updated:not(.inline),'
			+ '#wpbody-content > .wrap .wpallimport-wrapper .update-nag,'
			// WP All Export
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-error,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .error:not(.hidden),'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-info,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-information,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper #message,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-success,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .notice-updated,'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .updated:not(.inline),'
			+ '#wpbody-content > .wrap .wpallexport-wrapper .update-nag,'
			// WS Form
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-error,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .error:not(.hidden),'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-info,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-information,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > #message,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-warning:not(.hidden),'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-success,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .notice-updated,'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .updated:not(.inline),'
			+ '#wpbody-content > #wsf-layout-editor > #poststuff > .update-nag,'
			// Pods
			+ '#wpbody-content .pods-submittable-fields > .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content .pods-submittable-fields > .notice-error,'
			+ '#wpbody-content .pods-submittable-fields > .error:not(.hidden),'
			+ '#wpbody-content .pods-submittable-fields > .notice-info,'
			+ '#wpbody-content .pods-submittable-fields > .notice-information,'
			+ '#wpbody-content .pods-submittable-fields > #message,'
			+ '#wpbody-content .pods-submittable-fields > .notice-warning:not(.hidden),'
			+ '#wpbody-content .pods-submittable-fields > .notice-success,'
			+ '#wpbody-content .pods-submittable-fields > .notice-updated,'
			+ '#wpbody-content .pods-submittable-fields > .updated:not(.inline),'
			+ '#wpbody-content .pods-submittable-fields > .update-nag,'
			// Meta Box Lite
			+ '.mb-main > .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '.mb-main > .notice-error,'
			+ '.mb-main > .error:not(.hidden),'
			+ '.mb-main > .notice-info,'
			+ '.mb-main > .notice-information,'
			+ '.mb-main > #message,'
			+ '.mb-main > .notice-warning:not(.hidden),'
			+ '.mb-main > .notice-success,'
			+ '.mb-main > .notice-updated,'
			+ '.mb-main > .updated:not(.inline),'
			+ '.mb-main > .update-nag,'
			// Meta Box AIO
			+ '.mb-header__left > .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '.mb-header__left > .notice-error,'
			+ '.mb-header__left > .error:not(.hidden),'
			+ '.mb-header__left > .notice-info,'
			+ '.mb-header__left > .notice-information,'
			+ '.mb-header__left > #message,'
			+ '.mb-header__left > .notice-warning:not(.hidden),'
			+ '.mb-header__left > .notice-success,'
			+ '.mb-header__left > .notice-updated,'
			+ '.mb-header__left > .updated:not(.inline),'
			+ '.mb-header__left > .update-nag,'
			// Funnel Builder for WordPress by FunnelKit
			+ '#wpbody-content > .bwfan_header > .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > .bwfan_header > .notice-error,'
			+ '#wpbody-content > .bwfan_header > .error:not(.hidden),'
			+ '#wpbody-content > .bwfan_header > .notice-info,'
			+ '#wpbody-content > .bwfan_header > .notice-information,'
			+ '#wpbody-content > .bwfan_header > #message,'
			+ '#wpbody-content > .bwfan_header > .notice-warning:not(.hidden),'
			+ '#wpbody-content > .bwfan_header > .notice-success,'
			+ '#wpbody-content > .bwfan_header > .notice-updated,'
			+ '#wpbody-content > .bwfan_header > .updated:not(.inline),'
			+ '#wpbody-content > .bwfan_header > .update-nag,'
			+ '#wpbody-content > .notice:not(.otgs-notice,.wcml-notice),' // LearnDash, WPML WooCommerce Multilingual
			+ '#wpbody-content > .update-nag,' // LearnDash
			+ '#wpbody-content > .jp-connection-banner,' // Jetpack
			+ '#wpbody-content > .jitm-banner,' // Jetpack
			+ '#wpbody-content > .jetpack-jitm-message,' // Jetpack
			+ '#wpbody-content > .ngg_admin_notice,' // Nextgen Gallery
			+ '#wpbody-content > .imagify-welcome,' // Imagify
			+ '#wpbody-content #wordfenceAutoUpdateChoice,' // Wordfence
			+ '#wpbody-content #easy-updates-manager-dashnotice,' // Easy Updates Manager
			// GenerateBlocks
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice:not(.system-notice,.hidden),'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-error,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .error:not(.hidden),'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-info,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-information,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap #message,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-success,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .notice-updated,'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .updated:not(.inline),'
			+ '#wpbody-content > .wrap.gblocks-dashboard-wrap .update-nag,'
			// WPML
			+ '#wpbody-content > .otgs-notice,'
			// WooCommerce Stock Sync
			+ '#wpbody-content > .wrap > .ssgs-influencer-banner,'
			+ '#wpbody-content > .wrap > .ssgs-upgrade-banner,'
			+ '#wpbody-content > .wrap > .ssgs-rating-banner,'
			// Shortpixel
			+ '#wpbody-content > .shortpixel-notice,'
			// BdThemes Element Pack Pro
			+ '#wpbody-content > .wrap > .biggopti,'
			// Dokan
			+ '#wpbody-content > .wrap .dokan-dashboard .notice:not(.system-notice,.hidden,.wcml-notice),'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-error,'
			+ '#wpbody-content > .wrap .dokan-dashboard .error:not(.hidden),'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-info,'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-information,'
			+ '#wpbody-content > .wrap .dokan-dashboard #message,'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-warning:not(.hidden),'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-success,'
			+ '#wpbody-content > .wrap .dokan-dashboard .notice-updated,'
			+ '#wpbody-content > .wrap .dokan-dashboard .updated:not(.inline),'
			+ '#wpbody-content > .wrap .dokan-dashboard .update-nag'
			).not(':hidden').detach()
			.appendTo(noticesPanel)
			.show();

		}, 250);

		// Set up the side drawer that holds the hidden admin notices: https://stephanwagner.me/jBox

		var noticesModal = new jBox('Modal', {
			attach: '.asenha-admin-notices-menu',
			trigger: 'click', // or 'mouseenter'
			// content: 'Test'
			content: $('.asenha-admin-notices-drawer'),
			width: 1118, // pixels
			closeButton: 'box',
			addClass: 'admin-notices-modal',
			overlayClass: 'admin-notices-modal-overlay',
			target: '#wpwrap', // where to anchor the modal
			position: {
				x: 'right',
				y: 'top'
			},
			// fade: 1000,
			animation: {
				open: 'slide:bottom',
				close: 'slide:bottom'
			}
		});

		$(document).on('click', '.asenha-admin-notices-drawer', function() {
			setTimeout(
			  function() 
			  {
			    // Let's wait 200ms before we proceed. Give enough time for a notice div to be cleared from the DOM before recounting the notices
	   		noticesCount = $('.asenha-admin-notices-drawer > div').length;
	   		$('.asenha-admin-notices-counter').html(noticesCount); // insert count
			  }, 2000);
		});

   });

})( jQuery );