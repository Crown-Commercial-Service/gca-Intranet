(function( $ ) {
   'use strict';

   $(document).ready( function() {

      // Make page header sticky on scroll. Using https://github.com/AndrewHenderson/jSticky
      if (typeof $.fn.sticky === 'function') {
         $('#asenha-header').sticky({
            topSpacing: 0, // Space between element and top of the viewport (in pixels)
            zIndex: 100, // z-index
            stopper: '', // Id, class, or number value
            stickyClass: 'asenha-sticky' // Class applied to element when it's stuck. Class name or false.
         })       
      }
     
      // Clicking on header save button triggers click of the hidden form submit button
      $('.asenha-save-button').click( function(e) {

         e.preventDefault();
         
         $('.asenha-saving-changes').fadeIn();

         // Get current tab's URL hash and save it in cookie
         var hash = decodeURI(window.location.hash).substr(1); // get hash without the # character
         Cookies.set('asenha_tab', hash, { expires: 1 }); // expires in 1 day

         // Submit the settings form
         $('input[type="submit"]#asenha-submit').click();

      });

      // Search modules
      var searchInput = $('#module-search-input');
      
      $(searchInput).keyup(delay(function (e) {
         var searchVal = $(this).val();
         var filterItems = $('[data-search-filter]');

         if ( searchVal != '' ) {
            setTimeout(function() {
               $(searchInput).addClass('has-text-input');
               $('.modules-tab').hide();
               $('.search-tab').show();
               $('.asenha-fields.section-visible').addClass('originally-visible');
               $('.asenha-fields').removeClass('section-visible');
               $('.asenha-fields').removeClass('section-hidden');
               $('.asenha-fields').addClass('section-visible-for-search');
               filterItems.parents('.asenha-toggle').addClass('result-is-hidden');
               $('[data-search-filter][data-module-info*="' + searchVal.toLowerCase() + '"]').parents('.asenha-toggle').removeClass('result-is-hidden');
            }, 250 );
            refreshCodeMirror();
         } else {
            setTimeout(function() {
               searchInput.removeClass('has-text-input');
               filterItems.parents('.asenha-toggle').removeClass('result-is-hidden');
               clear_search();
               refreshCodeMirror();
            }, 250 );
         }
      }, 200));

      // Restore all results when the x button on search input field is clicked. 
      // The click triggers a 'search' event we're listening to below

      if ( searchInput.length > 0 ) {
         document.getElementById("module-search-input").addEventListener("search", function(event) {
            clear_search();
            refreshCodeMirror();
         });         
      }
      
      // Ref: https://stackoverflow.com/a/1909508
      function delay(fn, ms) {
         let timer = 0
         return function(...args) {
            clearTimeout(timer)
            timer = setTimeout(fn.bind(this, ...args), ms || 0)
         }
      }
            
      function clear_search() {
            searchInput.removeClass('has-text-input');
            $('[data-search-filter]').each( function() {
               $(this).parents('.asenha-toggle').removeClass('result-is-hidden');
               $('.modules-tab').show();
               $('.search-tab').hide();
               $('.asenha-fields').removeClass('section-visible-for-search');
               $('.asenha-fields').addClass('section-hidden');
               // Has no effect. Compensate with CSS .asenha-fields.section-visible.section-hidden { display: block; }
               // $('.asenha-fields.originally-visible').removeClass('section-hidden'); 
               $('.asenha-fields.originally-visible').addClass('section-visible');
               $('.asenha-fields').removeClass('originally-visible');
            });
      }

      // Show all / less toggler for field options | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('.asenha-field-with-options.field-show-more > .show-more').click(function(e) {

         e.preventDefault();

         var $this = $(this);
         $this.toggleClass('show-more');

         if ($this.hasClass('show-more')) {
            $this.next().removeClass('opened',0);
            $this.html(adminPageVars.expandText + ' &#9660;');
         } else {
            $this.next().addClass('opened',0);
            $this.html(adminPageVars.collapseText + ' &#9650;');
         }

      });
      
      // Email Delivery >> Send test email
      $('#send-test-email').click(function(e) {
         e.preventDefault();
         var emailTo = $('#test-email-to').val();
         if ( emailTo ) {
            $('#ajax-result').show();
            $('.sending-test-email').show();
            $('.test-email-result').hide();
            $('#test-email-success').hide();
            $('#test-email-failed').hide();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'send_test_email',
                  'email_to': emailTo,
                  'nonce': adminPageVars.sendTestEmailNonce
               },
               success:function(data) {
                  var data = data.slice(0,-1); // remove strange trailing zero in string returned by AJAX call
                  var response = JSON.parse(data);
                  if ( response.status == 'success' ) {
                     setTimeout( function() {
                        $('.sending-test-email').hide();
                        // $('.test-email-result').show();
                        $('#test-email-success').show();
                     }, 1500);
                  }
                  if ( response.status == 'failed' ) {
                     setTimeout( function() {
                        $('.sending-test-email').hide();
                        // $('.test-email-result').show();
                        $('#test-email-failed').show();
                     }, 1500);                     
                  }
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
                  setTimeout( function() {
                     $('.sending-test-email').hide();
                     $('.test-email-result').show();
                     $('#test-email-failed').show();
                  }, 1500);
               }
            });
         } else {
            alert( 'Please enter destination email address first.' );
         }
      });

      

      // Initialize data tables
      var table = $("#login-attempts-log").DataTable({
         pageLength: 10,
         order: [[2, 'desc']],
         columnDefs: [
            { targets: 3, orderable: false, searchable: false }
         ],
         language: {
            emptyTable: adminPageVars.dataTable.emptyTable,
            info: adminPageVars.dataTable.info,
            infoEmpty: adminPageVars.dataTable.infoEmpty,
            infoFiltered: adminPageVars.dataTable.infoFiltered,
            lengthMenu: adminPageVars.dataTable.lengthMenu,
            search: adminPageVars.dataTable.search,
            zeroRecords: adminPageVars.dataTable.zeroRecords,
            paginate: {
                first: adminPageVars.dataTable.paginate.first,
                last: adminPageVars.dataTable.paginate.last,
                next: adminPageVars.dataTable.paginate.next,
                previous: adminPageVars.dataTable.paginate.previous
            },
         }
      });

      // Toast notifications
      function asenhaShowToast(type, message, duration) {
         if ( ! message ) {
            return;
         }

         // Create toast container if it doesn't exist.
         var $container = $('#asenha-toast-container');
         if ( ! $container.length ) {
            $container = $('<div id="asenha-toast-container"></div>');
            $('body').append($container);
         }

         // Create toast element with icon.
         var iconMap = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
         };

         var toastType = type || 'info';
         var $toast = $('<div></div>').addClass('asenha-toast asenha-toast-' + toastType);
         var $icon = $('<span></span>').addClass('asenha-toast-icon').text(iconMap[toastType] || 'ℹ');
         var $message = $('<span></span>').addClass('asenha-toast-message').text(message);
         var $close = $('<button></button>')
            .addClass('asenha-toast-close')
            .attr('type', 'button')
            .attr('aria-label', 'Dismiss')
            .text('×');

         $toast.append($icon, $message, $close);
         $container.append($toast);

         // Trigger slide-in animation after a brief delay for CSS transition.
         setTimeout(function() {
            $toast.addClass('asenha-toast-visible');
         }, 10);

         // Auto dismiss after specified duration (default 5 seconds).
         var dismissDuration = duration || 5000;
         var dismissTimeout = setTimeout(function() {
            dismissToast($toast);
         }, dismissDuration);

         // Manual dismiss on close button click.
         $close.on('click', function() {
            clearTimeout(dismissTimeout);
            dismissToast($toast);
         });

         function dismissToast($el) {
            $el.removeClass('asenha-toast-visible');
            setTimeout(function() {
               $el.remove();
            }, 300);
         }
      }

      // Release lock for an IP address (Limit Login Attempts)
      $('body').on('click', '.asenha-release-login-lock', function(e) {
         e.preventDefault();

         var $btn = $(this);
         var ipAddress = $btn.data('ip-address');

         if ( ! ipAddress ) {
            return false;
         }

         $btn.addClass('disabled').attr('aria-disabled', 'true');

         $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
               'action': 'asenha_release_login_lock',
               'nonce': adminPageVars.nonce,
               'ip_address': ipAddress
            },
            success: function(response) {
               if ( response && response.success ) {
                  table.row( $btn.closest('tr') ).remove().draw(false);

                  var successTemplate = ( adminPageVars.limitLoginAttempts && adminPageVars.limitLoginAttempts.releaseLockSuccess )
                     ? adminPageVars.limitLoginAttempts.releaseLockSuccess
                     : '';
                  if ( successTemplate ) {
                     var successMessage = successTemplate.replace('%s', ipAddress);
                     asenhaShowToast('success', successMessage, 5000);
                  }
               } else {
                  $btn.removeClass('disabled').removeAttr('aria-disabled');
                  if ( adminPageVars.limitLoginAttempts && adminPageVars.limitLoginAttempts.releaseLockError ) {
                     asenhaShowToast('error', adminPageVars.limitLoginAttempts.releaseLockError, 5000);
                  }                  
               }
            },
            error: function() {
               $btn.removeClass('disabled').removeAttr('aria-disabled');
               if ( adminPageVars.limitLoginAttempts && adminPageVars.limitLoginAttempts.releaseLockError ) {
                  asenhaShowToast('error', adminPageVars.limitLoginAttempts.releaseLockError, 5000);
               }
            }
         });

         return false;
      });

      

      // Place fields into the "Content Management" tab
      
      $('.enable-duplication').appendTo('.fields-content-management > table > tbody');
      $('.duplication-redirect-destination').appendTo('.fields-content-management .enable-duplication .asenha-subfields');
      
      $('.content-order').appendTo('.fields-content-management > table > tbody');
      // $('.content-order-subfields-heading').appendTo('.fields-content-management .content-order .asenha-subfields');
      $('.content-order-for').appendTo('.fields-content-management .content-order .asenha-subfields');
      
      
      $('.media-files-visibility-control').appendTo('.fields-content-management > table > tbody');
      
      $('.enable-media-replacement').appendTo('.fields-content-management > table > tbody');
      $('.disable-media-replacement-cache-busting').appendTo('.fields-content-management .enable-media-replacement .asenha-subfields');
      $('.enable-svg-upload').appendTo('.fields-content-management > table > tbody');
      $('.enable-svg-upload-for').appendTo('.fields-content-management .enable-svg-upload .asenha-subfields');
      $('.enable-avif-upload').appendTo('.fields-content-management > table > tbody');
      $('.avif-support-status').appendTo('.fields-content-management .enable-avif-upload .asenha-subfields');
      
      $('.enable-external-permalinks').appendTo('.fields-content-management > table > tbody');
      
      $('.enable-external-permalinks-for').appendTo('.fields-content-management .enable-external-permalinks .asenha-subfields');
      $('.external-links-new-tab').appendTo('.fields-content-management > table > tbody');
      $('.custom-nav-menu-items-new-tab').appendTo('.fields-content-management > table > tbody');
      $('.enable-missed-schedule-posts-auto-publish').appendTo('.fields-content-management > table > tbody');

      // Place fields into "Admin Interface" tab
      $('.hide-modify-elements').appendTo('.fields-admin-interface > table > tbody');
      $('.hide-ab-wp-logo-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-site-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-customize-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-updates-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-comments-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-new-content-menu').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-ab-howdy').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      $('.hide-help-drawer').appendTo('.fields-admin-interface .hide-modify-elements .asenha-subfields');
      
      $('.hide-admin-notices').appendTo('.fields-admin-interface > table > tbody');
      
      $('.disable-dashboard-widgets').appendTo('.fields-admin-interface > table > tbody');
      $('.disable-welcome-panel-in-dashboard').appendTo('.fields-admin-interface .disable-dashboard-widgets .asenha-subfields');
      $('.disabled-dashboard-widgets').appendTo('.fields-admin-interface .disable-dashboard-widgets .asenha-subfields');
      $('.hide-admin-bar').appendTo('.fields-admin-interface > table > tbody');
      
      $('.hide-admin-bar-for').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      $('.hide-admin-bar-always-show-for-admins').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      
      $('.hide-admin-bar-description').appendTo('.fields-admin-interface .hide-admin-bar .asenha-subfields');
      
      $('.wider-admin-menu').appendTo('.fields-admin-interface > table > tbody');
      $('.admin-menu-width').appendTo('.fields-admin-interface .wider-admin-menu .asenha-subfields');
      $('.customize-admin-menu').appendTo('.fields-admin-interface > table > tbody');
      $('.admin-menu-organizer-sticky-collapse-menu').appendTo('.fields-admin-interface .customize-admin-menu .asenha-subfields');
      
      $('.show-custom-taxonomy-filters').appendTo('.fields-admin-interface > table > tbody');
      
      $('.enhance-list-tables').appendTo('.fields-admin-interface > table > tbody');
      $('.show-featured-image-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-excerpt-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-last-modified-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-id-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-file-size-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.show-id-in-action_row').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-date-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-comments-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.hide-post-tags-column').appendTo('.fields-admin-interface .enhance-list-tables .asenha-subfields');
      $('.various-admin-ui-enhancements').appendTo('.fields-admin-interface > table > tbody');
      $('.media-library-infinite-scrolling').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      $('.display-active-plugins-first').appendTo('.fields-admin-interface .various-admin-ui-enhancements .asenha-subfields');
      
      $('.custom-admin-footer-text').appendTo('.fields-admin-interface > table > tbody');
      $('.custom-admin-footer-left').appendTo('.fields-admin-interface .custom-admin-footer-text .asenha-subfields');
      reinitWpEditor('admin_site_enhancements--custom_admin_footer_left');
      $('.custom-admin-footer-right').appendTo('.fields-admin-interface .custom-admin-footer-text .asenha-subfields');
      reinitWpEditor('admin_site_enhancements--custom_admin_footer_right');

      // Place fields into "Log In | Log Out" tab
      $('.change-login-url').appendTo('.fields-login-logout > table > tbody');
      $('.custom-login-slug').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.custom-login-whitelist').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.default-login-redirect-slug').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.change-login-url-description').appendTo('.fields-login-logout .change-login-url .asenha-subfields');
      $('.login-id-type-restriction').appendTo('.fields-login-logout > table > tbody');
      $('.login-id-type').appendTo('.fields-login-logout .login-id-type-restriction .asenha-subfields');
      
      $('.site-identity-on-login').appendTo('.fields-login-logout > table > tbody');
      $('.enable-login-logout-menu').appendTo('.fields-login-logout > table > tbody');
      
      $('.enable-last-login-column').appendTo('.fields-login-logout > table > tbody');
      $('.registration-date-column').appendTo('.fields-login-logout > table > tbody');
      $('.redirect-after-login').appendTo('.fields-login-logout > table > tbody');
      
      $('.redirect-after-login-to-slug').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      $('.redirect-after-login-for').appendTo('.fields-login-logout .redirect-after-login .asenha-subfields');
      
      $('.redirect-after-logout').appendTo('.fields-login-logout > table > tbody');
      
      $('.redirect-after-logout-to-slug').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      $('.redirect-after-logout-for').appendTo('.fields-login-logout .redirect-after-logout .asenha-subfields');
      

      // Place fields into "Custom Code" tab
      
      $('.enable-custom-admin-css').appendTo('.fields-custom-code > table > tbody');
      $('.custom-admin-css').appendTo('.fields-custom-code .enable-custom-admin-css .asenha-subfields');
      $('.enable-custom-frontend-css').appendTo('.fields-custom-code > table > tbody');
      
      $('.custom-frontend-css').appendTo('.fields-custom-code .enable-custom-frontend-css .asenha-subfields');
      $('.insert-head-body-footer-code').appendTo('.fields-custom-code > table > tbody');
      $('.disable-code-unslash').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.head-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.head-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.body-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.body-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.footer-code-priority').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.footer-code').appendTo('.fields-custom-code .insert-head-body-footer-code .asenha-subfields');
      $('.enable-custom-body-class').appendTo('.fields-custom-code > table > tbody');
      $('.enable-custom-body-class-for').appendTo('.fields-custom-code .enable-custom-body-class .asenha-subfields');
      $('.manage-ads-appads-txt').appendTo('.fields-custom-code > table > tbody');
      $('.ads-txt-content').appendTo('.fields-custom-code .manage-ads-appads-txt .asenha-subfields');
      $('.app-ads-txt-content').appendTo('.fields-custom-code .manage-ads-appads-txt .asenha-subfields');
      $('.manage-robots-txt').appendTo('.fields-custom-code > table > tbody');
      $('.robots-txt-content').appendTo('.fields-custom-code .manage-robots-txt .asenha-subfields');

      // Place fields into the "Disable Components" tab
      $('.disable-gutenberg').appendTo('.fields-disable-components > table > tbody');
      
      $('.disable-gutenberg-for').appendTo('.fields-disable-components .disable-gutenberg .asenha-subfields');
      $('.disable-gutenberg-frontend-styles').appendTo('.fields-disable-components .disable-gutenberg .asenha-subfields');
      $('.disable-comments').appendTo('.fields-disable-components > table > tbody');
      
      $('.disable-comments-for').appendTo('.fields-disable-components .disable-comments .asenha-subfields');
      $('.disable-comments-all-cpts-description').appendTo('.fields-disable-components .disable-comments .asenha-subfields');
      $('.disable-rest-api').appendTo('.fields-disable-components > table > tbody');
      
      $('.disable-feeds').appendTo('.fields-disable-components > table > tbody');
      $('.disable-embeds').appendTo('.fields-disable-components > table > tbody');
      $('.disable-all-updates').appendTo('.fields-disable-components > table > tbody');
      $('.disable-author-archives').appendTo('.fields-disable-components > table > tbody');
      $('.disable-smaller-components').appendTo('.fields-disable-components > table > tbody');
      $('.disable-head-generator-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-feed-generator-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-resource-version-number').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-wlwmanifest-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-rsd-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-head-shortlink-tag').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-frontend-dashicons').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-emoji-support').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-jquery-migrate').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-block-widgets').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-lazy-load').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-application-passwords').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');
      $('.disable-plugin-theme-editor').appendTo('.fields-disable-components .disable-smaller-components .asenha-subfields');

      // Place fields into "Security" tab
      $('.limit-login-attempts').appendTo('.fields-security > table > tbody');
      $('.login-fails-allowed').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.login-lockout-maxcount').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      
      $('.limit-login-attempts-header-override').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.limit-login-attempts-header-override-description').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      $('.login-attempts-log-table').appendTo('.fields-security .limit-login-attempts .asenha-subfields');
      
      $('.obfuscate-author-slugs').appendTo('.fields-security > table > tbody');
      $('.obfuscate-email-address').appendTo('.fields-security > table > tbody');
      $('.obfuscate-email-address-description').appendTo('.fields-security .obfuscate-email-address .asenha-subfields');
      
      $('.disable-xmlrpc').appendTo('.fields-security > table > tbody');

      // Place fields into "Optimizations" tab
      $('.image-upload-control').appendTo('.fields-optimizations > table > tbody');
      $('.image-max-width').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      $('.image-max-height').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      
      $('.image-upload-control-description').appendTo('.fields-optimizations .image-upload-control .asenha-subfields');
      
      $('.enable-revisions-control').appendTo('.fields-optimizations > table > tbody');
      $('.revisions-max-number').appendTo('.fields-optimizations .enable-revisions-control .asenha-subfields');
      $('.enable-revisions-control-for').appendTo('.fields-optimizations .enable-revisions-control .asenha-subfields');
      $('.enable-heartbeat-control').appendTo('.fields-optimizations > table > tbody');
      $('.heartbeat-control-for-admin-pages').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-admin-pages').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-control-for-post-edit').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-post-edit').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-control-for-frontend').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');
      $('.heartbeat-interval-for-frontend').appendTo('.fields-optimizations .enable-heartbeat-control .asenha-subfields');

      // Place fields into "Utilities" tab
      $('.smtp-email-delivery').appendTo('.fields-utilities > table > tbody');
      $('.smtp-default-from-description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-default-from-name').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-default-from-email').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-force-from').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      
      $('.smtp--description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-host').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-port').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-security').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      
      $('.smtp-username').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-password').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-bypass-ssl-verification').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-debug').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      
      $('.smtp-send-test-email-description').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-send-test-email-to').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      $('.smtp-send-test-email-result').appendTo('.fields-utilities .smtp-email-delivery .asenha-subfields');
      
      $('.multiple-user-roles').appendTo('.fields-utilities > table > tbody');
      $('.image-sizes-panel').appendTo('.fields-utilities > table > tbody');
      $('.view-admin-as-role').appendTo('.fields-utilities > table > tbody');
      $('.view-admin-as-role-description').appendTo('.fields-utilities .view-admin-as-role .asenha-subfields');
      $('.enable-password-protection').appendTo('.fields-utilities > table > tbody');
      $('.password-protection-password').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      
      $('.password-protection-notes').appendTo('.fields-utilities .enable-password-protection .asenha-subfields');
      $('.maintenance-mode').appendTo('.fields-utilities > table > tbody');
      
      $('.maintenance-page-type-custom').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      
      $('.maintenance-page-heading').appendTo('.maintenance-page-type-custom');
      $('.maintenance-page-description').appendTo('.maintenance-page-type-custom');
      
      $('.maintenance-page-background').appendTo('.maintenance-page-type-custom');
      
      $('.maintenance-mode-description').appendTo('.fields-utilities .maintenance-mode .asenha-subfields');
      
      $('.redirect-404-to-homepage').appendTo('.fields-utilities > table > tbody');
      
      $('.display-system-summary').appendTo('.fields-utilities > table > tbody');
      $('.search-engine-visibility-status').appendTo('.fields-utilities > table > tbody');
      

      // Remove empty .form-table that originally holds the fields
      const formTableCount = $('.form-table').length;
      // $('.form-table')[formTableCount-1].remove();

      // Enable Custom Admin CSS => Initialize CodeMirror
      var adminCssTextarea = document.getElementById("admin_site_enhancements[custom_admin_css]");
      // if ( typeof CodeMirror != "undefined" ) {
      //    alert('CodeMirror is available');
      // }
      var adminCssEditor = CodeMirror.fromTextArea(adminCssTextarea, {
         mode: "css",
         lineNumbers: true,
         lineWrapping: true
      });

      adminCssEditor.setSize("100%",600);

      // Enable Custom Frontend CSS => Initialize CodeMirror
      var frontendCssTextarea = document.getElementById("admin_site_enhancements[custom_frontend_css]");
      var frontendCssEditor = CodeMirror.fromTextArea(frontendCssTextarea, {
         mode: "css",
         lineNumbers: true,
         lineWrapping: true
      });

      frontendCssEditor.setSize("100%",600);

      // Manage ads.txt and app-ads.txt=> Initialize CodeMirror
      var adsTxtTextarea = document.getElementById("admin_site_enhancements[ads_txt_content]");
      var adsTxtEditor = CodeMirror.fromTextArea(adsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      adsTxtEditor.setSize("100%",300);

      var appAdsTxtTextarea = document.getElementById("admin_site_enhancements[app_ads_txt_content]");
      var appAdsTxtEditor = CodeMirror.fromTextArea(appAdsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      appAdsTxtEditor.setSize("100%",300);

      // Manage robots.txt => Initialize CodeMirror
      var robotsTxtTextarea = document.getElementById("admin_site_enhancements[robots_txt_content]");
      var robotsTxtEditor = CodeMirror.fromTextArea(robotsTxtTextarea, {
         mode: "markdown",
         lineNumbers: true,
         lineWrapping: true
      });

      robotsTxtEditor.setSize("100%",400);

      // Insert <head>, <body> and <footer> code => Initialize CodeMirror
      var headCodeTextarea = document.getElementById("admin_site_enhancements[head_code]");
      var headCodeEditor = CodeMirror.fromTextArea(headCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      headCodeEditor.setSize("100%",300);

      var bodyCodeTextarea = document.getElementById("admin_site_enhancements[body_code]");
      var bodyCodeEditor = CodeMirror.fromTextArea(bodyCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      bodyCodeEditor.setSize("100%",300);

      var footerCodeTextarea = document.getElementById("admin_site_enhancements[footer_code]");
      var footerCodeEditor = CodeMirror.fromTextArea(footerCodeTextarea, {
         mode: "htmlmixed",
         lineNumbers: true,
         lineWrapping: true
      });
      footerCodeEditor.setSize("100%",300);

      

      function refreshCodeMirror() {
         
         adminCssEditor.refresh(); // Custom Admin CSS >> CodeMirror
         frontendCssEditor.refresh(); // Custom Fronend CSS >> CodeMirror
         adsTxtEditor.refresh(); // Manage ads.txt >> CodeMirror
         appAdsTxtEditor.refresh(); // Manage app-ads.txt >> CodeMirror
         headCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         bodyCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         footerCodeEditor.refresh(); // Insert <head>, <body> and <footer> code >> CodeMirror
         robotsTxtEditor.refresh(); // Manage robots.txt >> CodeMirror
                  
      }

      // Show and hide corresponding fields on tab clicks

      function tabSwitcher( tabSlug ) {
         $('.asenha-fields.fields-'+tabSlug).addClass('section-visible');
         $('.asenha-fields.fields-'+tabSlug).removeClass('section-hidden');
         $('.asenha-fields:not(.fields-'+tabSlug+')').removeClass('section-visible');
         $('.asenha-fields:not(.fields-'+tabSlug+')').addClass('section-hidden');
         window.location.hash = tabSlug;
         Cookies.set('asenha_tab', tabSlug, { expires: 1 }); // expires in 1 day
      }

      $('#tab-content-management + label').click( function() {
         tabSwitcher('content-management');
      });

      $('#tab-admin-interface + label').click( function() {
         tabSwitcher('admin-interface');
      });

      $('#tab-login-logout + label').click( function() {
         tabSwitcher('login-logout');
         refreshCodeMirror();
      });

      $('#tab-custom-code + label').click( function() {
         tabSwitcher('custom-code');
         refreshCodeMirror();
      });

      $('#tab-disable-components + label').click( function() {
         tabSwitcher('disable-components');
      });

      $('#tab-security + label').click( function() {
         tabSwitcher('security');
      });

      $('#tab-optimizations + label').click( function() {
         tabSwitcher('optimizations');
      });

      $('#tab-utilities + label').click( function() {
         tabSwitcher('utilities');
         refreshCodeMirror();
      });

      // Open tab set in 'asenha_tab' cookie set on saving changes. Defaults to content-management tab when cookie is empty
      var asenhaTabHash = Cookies.get('asenha_tab');

      if (typeof asenhaTabHash === 'undefined') {
         $('#tab-content-management + label').trigger('click');
      } else {
         $('#tab-' + asenhaTabHash + ' + label').trigger('click');
      }
      
      // Show or hide subfields on document ready and on toggle click

      function subfieldsToggler( fieldId, fieldClass, sortableId, codeMirrorInstances ) {

         if (document.getElementById('admin_site_enhancements['+fieldId+']')) {

            // Show/hide subfields on document ready, depending on if module is enabled or not
            if ( document.getElementById('admin_site_enhancements['+fieldId+']').checked ) {

               $('.'+fieldClass+' .asenha-subfields').show();
               if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                  $('.'+fieldClass+' .asenha-subfield-select-inner').show();
               }
               $('.asenha-toggle.'+fieldClass+' td .asenha-field-with-options').addClass('is-enabled');
               if ( codeMirrorInstances ) {
                  Object.keys(codeMirrorInstances).forEach(function(key) {
                     if ( codeMirrorInstances[key] ) {
                        codeMirrorInstances[key].refresh();
                     }
                  });
               }

            } else {

               $('.'+fieldClass+' .asenha-subfields').hide();
               if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                  $('.'+fieldClass+' .asenha-subfield-select-inner').hide();
               }

            }

            // Show/hide subfields on toggle click
            document.getElementById('admin_site_enhancements['+fieldId+']').addEventListener('click', event => {
               if (event.target.checked) {

                  $('.'+fieldClass+' .asenha-subfields').fadeIn();
                  if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                     $('.'+fieldClass+' .asenha-subfield-select-inner').show();
                  }
                  $('.'+fieldClass+' .asenha-field-with-options').toggleClass('is-enabled');
                  if (document.getElementById(sortableId)) {
                     // Initialize sortable elements: https://api.jqueryui.com/sortable/
                     $('#' + sortableId ).sortable();                     
                  }
                  if ( codeMirrorInstances ) {
                     Object.keys(codeMirrorInstances).forEach(function(key) {
                        if ( codeMirrorInstances[key] ) {
                           codeMirrorInstances[key].refresh();                        
                        }
                     });
                  }

               } else {

                  $('.'+fieldClass+' .asenha-subfields').hide();
                  if (document.querySelector('.'+fieldClass+' .asenha-subfield-select-inner')) {
                     $('.'+fieldClass+' .asenha-subfield-select-inner').hide();
                  }
                  $('.'+fieldClass+' .asenha-field-with-options').toggleClass('is-enabled');

               }
            });
            
         }
         
      }
      
       // Re-init wp_editor for snippet description. Required because the wp_editor was moved in the DOM after document ready.
       // Ref: https://stackoverflow.com/a/21519323
       // Ref: https://core.trac.wordpress.org/ticket/19173
      function reinitWpEditor(id) {
         tinymce.execCommand('mceRemoveEditor', true, id);
         var init = tinymce.extend( {}, tinyMCEPreInit.mceInit[ id ] );
         try { tinymce.init( init ); } catch(e){}
         $('textarea[id="' + id + '"]').closest('form').find('input[type="submit"]').click(function(){
          if( getUserSetting( 'editor' ) == 'tmce' ){
              var id = mce.find( 'textarea' ).attr( 'id' );
              tinymce.execCommand( 'mceRemoveEditor', false, id );
              tinymce.execCommand( 'mceAddEditor', false, id );
          }
          return true;
         });
      }

      
      subfieldsToggler( 'enable_duplication', 'enable-duplication' );
      subfieldsToggler( 'content_order', 'content-order' );
      
      subfieldsToggler( 'media_files_visibility_control', 'media-files-visibility-control' );
      
      subfieldsToggler( 'enable_media_replacement', 'enable-media-replacement' );
      subfieldsToggler( 'enable_svg_upload', 'enable-svg-upload' );
      subfieldsToggler( 'enable_avif_upload', 'enable-avif-upload' );
      
      subfieldsToggler( 'enable_external_permalinks', 'enable-external-permalinks' );
      
      subfieldsToggler( 'enhance_list_tables', 'enhance-list-tables' );
      subfieldsToggler( 'custom_admin_footer_text', 'custom-admin-footer-text' );
      subfieldsToggler( 'wider_admin_menu', 'wider-admin-menu' );
      subfieldsToggler( 'customize_admin_menu', 'customize-admin-menu', 'custom-admin-menu' );
      subfieldsToggler( 'disable_dashboard_widgets', 'disable-dashboard-widgets' );
      subfieldsToggler( 'various_admin_ui_enhancements', 'various-admin-ui-enhancements' );
      
      // Clean Up Admin Bar
      subfieldsToggler( 'hide_modify_elements', 'hide-modify-elements' );
      
      subfieldsToggler( 'hide_admin_bar', 'hide-admin-bar' );
      subfieldsToggler( 'change_login_url', 'change-login-url' );
      subfieldsToggler( 'login_id_type_restriction', 'login-id-type-restriction' );
      
      subfieldsToggler( 'redirect_after_login', 'redirect-after-login' );
      subfieldsToggler( 'redirect_after_logout', 'redirect-after-logout' );
      subfieldsToggler( 'enable_custom_admin_css', 'enable-custom-admin-css', '', {adminCssEditor} );
      subfieldsToggler( 'enable_custom_frontend_css', 'enable-custom-frontend-css', '', {frontendCssEditor} );
      subfieldsToggler( 'insert_head_body_footer_code', 'insert-head-body-footer-code', '', {headCodeEditor,bodyCodeEditor,footerCodeEditor} );
      subfieldsToggler( 'enable_custom_body_class', 'enable-custom-body-class' );
      subfieldsToggler( 'manage_ads_appads_txt', 'manage-ads-appads-txt', '', {adsTxtEditor,appAdsTxtEditor} );
      subfieldsToggler( 'manage_robots_txt', 'manage-robots-txt', '', {robotsTxtEditor} );
      
      subfieldsToggler( 'disable_gutenberg', 'disable-gutenberg' );
      subfieldsToggler( 'disable_comments', 'disable-comments' );
      
      subfieldsToggler( 'disable_smaller_components', 'disable-smaller-components' );
      subfieldsToggler( 'limit_login_attempts', 'limit-login-attempts' );
      
      subfieldsToggler( 'obfuscate_email_address', 'obfuscate-email-address' );
      subfieldsToggler( 'image_upload_control', 'image-upload-control' );
      subfieldsToggler( 'enable_revisions_control', 'enable-revisions-control' );
      subfieldsToggler( 'enable_heartbeat_control', 'enable-heartbeat-control' );
      

      // Enable Heartbeat Control => Check if "Modify interval" is chosen/clicked and show/hide the corresponding select field
      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_admin_pages]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').show();
      }

      // Two-Factor Authentication (2FA) => Show "Email code validity" only when Email codes is enabled.
      function toggleTwoFactorEmailTokenTtlVisibility() {
         var emailCodesEnabled = $( '#two_factor_available_providers_email' ).is( ':checked' );

         if ( emailCodesEnabled ) {
            $( '.two-factor-email-token-ttl' ).show();
         } else {
            $( '.two-factor-email-token-ttl' ).hide();
         }
      }

      toggleTwoFactorEmailTokenTtlVisibility();

      $( document ).on( 'change', '#two_factor_available_providers_email', toggleTwoFactorEmailTokenTtlVisibility );
      $( document ).on( 'change', 'input[name="admin_site_enhancements[two_factor_authentication]"]', toggleTwoFactorEmailTokenTtlVisibility );

      $('input[name="admin_site_enhancements[heartbeat_control_for_admin_pages]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-admin-pages .asenha-subfield-select-inner').hide();            
         }
      });

      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_post_edit]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').show();
      }

      $('input[name="admin_site_enhancements[heartbeat_control_for_post_edit]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-post-edit .asenha-subfield-select-inner').hide();            
         }
      });

      if ( $('input[name="admin_site_enhancements[heartbeat_control_for_frontend]"]:checked').val() == 'modify' ) {
         $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').show();
      }

      $('input[name="admin_site_enhancements[heartbeat_control_for_frontend]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'modify' ) {
            $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').show();
         } else {
            $('.heartbeat-interval-for-frontend .asenha-subfield-select-inner').hide();            
         }
      });

      subfieldsToggler( 'smtp_email_delivery', 'smtp-email-delivery' );

      
      
      // SMTP Email Delivery => Empty field value on click, so new password can be easily entered
      var oldSmtpPassValue = '';

      $('input[name="admin_site_enhancements[smtp_password]"]').focusin(function() {
         oldSmtpPassValue = $(this).val();
         $(this).val('');
      });

      $('input[name="admin_site_enhancements[smtp_password]"]').focusout(function() {
         if ( $(this).val() == '' ) {
            $(this).val(oldSmtpPassValue);
         }
      });

      subfieldsToggler( 'view_admin_as_role', 'view-admin-as-role' );
      subfieldsToggler( 'enable_password_protection', 'enable-password-protection' );

      // Enable Password protection => Empty field value on click, so new password can be easily entered
      var oldValue = '';
      $('input[name="admin_site_enhancements[password_protection_password]"]').focusin(function() {
         oldValue = $(this).val();
         $(this).val('');
      });

      $('input[name="admin_site_enhancements[password_protection_password]"]').focusout(function() {
         if ( $(this).val() == '' ) {
            $(this).val(oldValue);
         }
      });
      
      
         subfieldsToggler( 'maintenance_mode', 'maintenance-mode' );
      

      

      // Disable Gutenberg
      if ( $('input[name="admin_site_enhancements[disable_gutenberg_type]"]:checked').val() == 'all-post-types' ) {
         $('.asenha-checkbox-item.disable-gutenberg-for').hide();
         $('.disable-gutenberg-type').removeClass('asenha-th-border-bottom');
      } else {
         $('.asenha-checkbox-item.disable-gutenberg-for').show();
         $('.disable-gutenberg-type').addClass('asenha-th-border-bottom');
      }

      $('input[name="admin_site_enhancements[disable_gutenberg_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'all-post-types' ) {
            $('.asenha-checkbox-item.disable-gutenberg-for').hide();
            $('.disable-gutenberg-type').removeClass('asenha-th-border-bottom');
         } else {
            $('.asenha-checkbox-item.disable-gutenberg-for').show();
            $('.disable-gutenberg-type').addClass('asenha-th-border-bottom');
         }
      });

      // Disable Comments
      if ( $('input[name="admin_site_enhancements[disable_comments_type]"]:checked').val() == 'all-post-types' ) {
         $('.asenha-checkbox-item.disable-comments-for').hide();
         $('.disable-comments-type').removeClass('asenha-th-border-bottom');
      } else {
         $('.asenha-checkbox-item.disable-comments-for').show();
         $('.disable-comments-type').addClass('asenha-th-border-bottom');
      }

      $('input[name="admin_site_enhancements[disable_comments_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'all-post-types' ) {
            $('.asenha-checkbox-item.disable-comments-for').hide();
            $('.disable-comments-type').removeClass('asenha-th-border-bottom');
         } else {
            $('.asenha-checkbox-item.disable-comments-for').show();
            $('.disable-comments-type').addClass('asenha-th-border-bottom');
         }
      });

      

      // CAPTCHA Protection
      // CAPTCHA Type conditional show/hide
      $(document).on('change', 'select[name="admin_site_enhancements[captcha_protection_types]"]', function() {
         var selectValue = this.value;
         if ( 'altcha' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').show();
            $('.captcha-protection-recaptcha-wrapper').hide();
            $('.captcha-protection-turnstile-wrapper').hide();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
            // $('input#captcha_woo_locations_woo_checkout_form').show();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
         } else if ( 'recaptcha' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').hide();
            $('.captcha-protection-recaptcha-wrapper').show();
            $('.captcha-protection-turnstile-wrapper').hide();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
            // $('input#captcha_woo_locations_woo_checkout_form').show();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
         } else if ( 'turnstile' == selectValue ) {
            $('.captcha-protection-altcha-wrapper').hide();
            $('.captcha-protection-recaptcha-wrapper').hide();
            $('.captcha-protection-turnstile-wrapper').show();
            // $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', 'disabled');
            // $('input#captcha_woo_locations_woo_checkout_form').hide();
            // $('label[for="captcha_woo_locations_woo_checkout_form"]').hide();
         }
      });
      
      if ( 'altcha' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').show();
         $('.captcha-protection-recaptcha-wrapper').hide();
         $('.captcha-protection-turnstile-wrapper').hide();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
         $('input#captcha_woo_locations_woo_checkout_form').show();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
      } else if ( 'recaptcha' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').hide();
         $('.captcha-protection-recaptcha-wrapper').show();
         $('.captcha-protection-turnstile-wrapper').hide();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', '');
         $('input#captcha_woo_locations_woo_checkout_form').show();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').show();
      } else if ( 'turnstile' == $('select[name="admin_site_enhancements[captcha_protection_types]"]').val() ) {
         $('.captcha-protection-altcha-wrapper').hide();
         $('.captcha-protection-recaptcha-wrapper').hide();
         $('.captcha-protection-turnstile-wrapper').show();
         $('input#captcha_woo_locations_woo_checkout_form').prop('disabled', 'disabled');
         $('input#captcha_woo_locations_woo_checkout_form').hide();
         $('label[for="captcha_woo_locations_woo_checkout_form"]').hide();
      }

      // ALTCHA Floating UI conditional value selection
      $(document).on('change', 'select[name="admin_site_enhancements[altcha_widget]"]', function() {
         var selectValue = this.value;
         if ( 'checkbox' == selectValue ) {
            $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('');
         } else if ( 'invisible' == selectValue ) {
            $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('onsubmit');
         }
      });

      if ( 'checkbox' == $('select[name="admin_site_enhancements[altcha_widget]"]').val() ) {
         $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('');
      } else if ( 'invisible' == $('select[name="admin_site_enhancements[altcha_widget]"]').val() ) {
         $('select[name="admin_site_enhancements[altcha_auto_verification]"]').val('onsubmit');
      }

      // reCAPTCHA Type conditional show/hide
      $(document).on('change', 'select[name="admin_site_enhancements[recaptcha_widget]"]', function() {
         var selectValue = this.value;
         if ( 'v2_checkbox' == selectValue ) {
            $('.recaptcha-site-key-v2-checkbox').show();
            $('.recaptcha-secret-key-v2-checkbox').show();
            $('.recaptcha-site-key-v3-invisible').hide();
            $('.recaptcha-secret-key-v3-invisible').hide();
         } else if ( 'v3_invisible' == selectValue ) {
            $('.recaptcha-site-key-v2-checkbox').hide();
            $('.recaptcha-secret-key-v2-checkbox').hide();
            $('.recaptcha-site-key-v3-invisible').show();
            $('.recaptcha-secret-key-v3-invisible').show();
         }
      });

      if ( 'v2_checkbox' == $('select[name="admin_site_enhancements[recaptcha_widget]"]').val() ) {
         $('.recaptcha-site-key-v2-checkbox').show();
         $('.recaptcha-secret-key-v2-checkbox').show();
         $('.recaptcha-site-key-v3-invisible').hide();
         $('.recaptcha-secret-key-v3-invisible').hide();
      } else if ( 'v3_invisible' == $('select[name="admin_site_enhancements[recaptcha_widget]"]').val() ) {
         $('.recaptcha-site-key-v2-checkbox').hide();
         $('.recaptcha-secret-key-v2-checkbox').hide();
         $('.recaptcha-site-key-v3-invisible').show();
         $('.recaptcha-secret-key-v3-invisible').show();
      }

      // Email Address Obfuscator
      if ( $('input[name="admin_site_enhancements[obfuscate_email_address_in_content]"]').is(':checked') ) {
         $('.obfuscate-email-address-visitor-only').show();
      } else {
         $('.obfuscate-email-address-visitor-only').hide();
      }

      $('input[name="admin_site_enhancements[obfuscate_email_address_in_content]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.obfuscate-email-address-visitor-only').show();
         } else {
            $('.obfuscate-email-address-visitor-only').hide();
         }
      });

      // Image Upload Control
      if ( $('input[name="admin_site_enhancements[convert_to_webp]"]').is(':checked') ) {
         $('.convert-to-jpg-quality').hide();
         $('.convert-to-webp-quality').show();
      } else {
         $('.convert-to-jpg-quality').show();
         $('.convert-to-webp-quality').hide();
      }

      $('input[name="admin_site_enhancements[convert_to_webp]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.convert-to-jpg-quality').hide();
            $('.convert-to-webp-quality').show();
         } else {
            $('.convert-to-jpg-quality').show();
            $('.convert-to-webp-quality').hide();
         }
      });

      if ( $('input[name="admin_site_enhancements[disable_image_conversion]"]').is(':checked') ) {
         $('.image-conversion-wrapper').hide();
      } else {
         $('.image-conversion-wrapper').show();
      }

      $('input[name="admin_site_enhancements[disable_image_conversion]"]').click(function() {
         if ($(this).is(':checked')) {
            $('.image-conversion-wrapper').hide();
         } else {
            $('.image-conversion-wrapper').show();
         }
      });

      function field_value_initial_sync( originSelector, targetSelector ) {
         if ( $(originSelector).val() != '' ) {
            $(targetSelector).val($(originSelector).val());
         }
      }

      function field_value_live_sync( originSelector, targetSelector ) {
         $('.asenha-body').on('input', originSelector, function() {
            var originValue = $(this).val();
            $(targetSelector).val(originValue);
         });
      }
      
      function field_value_syncing( originSelector, targetSelector ) {
         field_value_initial_sync( originSelector, targetSelector );
         field_value_live_sync( originSelector, targetSelector );
      }

      // Form Builder site and secret keys syncing
      field_value_syncing('input[name="admin_site_enhancements[altcha_secret_key]"]', 'input[name="admin_site_enhancements[form_builder_altcha_secret_key]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_site_key_v2_checkbox]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_site_key_v2_checkbox]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_secret_key_v2_checkbox]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_secret_key_v2_checkbox]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_site_key_v3_invisible]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_site_key_v3_invisible]"]');
      field_value_syncing('input[name="admin_site_enhancements[recaptcha_secret_key_v3_invisible]"]', 'input[name="admin_site_enhancements[form_builder_recaptcha_secret_key_v3_invisible]"]');
      field_value_syncing('input[name="admin_site_enhancements[turnstile_site_key]"]', 'input[name="admin_site_enhancements[form_builder_turnstile_site_key]"]');
      field_value_syncing('input[name="admin_site_enhancements[turnstile_secret_key]"]', 'input[name="admin_site_enhancements[form_builder_turnstile_secret_key]"]');
      
      // Maintenance Mode => Use a customizable page vs Use an existing page      
      if ( $('input[name="admin_site_enhancements[maintenance_page_type]"]:checked').val() == 'custom' ) {
         $('.maintenance-page-type-custom').show();
         $('tr.maintenance-page-slug').hide();
      } else if ( $('input[name="admin_site_enhancements[maintenance_page_type]"]:checked').val() == 'existing' ) {
         $('.maintenance-page-type-custom').hide();
         $('tr.maintenance-page-slug').show();
      } else {
         $('.maintenance-page-type-custom').show();
         $('tr.maintenance-page-slug').hide();
      }

      $('input[name="admin_site_enhancements[maintenance_page_type]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'custom' ) {
         $('.maintenance-page-type-custom').show();
            $('tr.maintenance-page-slug').hide();
         } else if ( radioValue == 'existing' ) {
         $('.maintenance-page-type-custom').hide();
            $('tr.maintenance-page-slug').show();
         } else {
            $('.maintenance-page-type-custom').show();
            $('tr.maintenance-page-slug').hide();
         }
      });

      // Maintenance Mode => Check if 'Image' or 'Color' is chosen/clicked and show/hide the corresponding select field      
      if ( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'pattern' ) {
         $('tr.maintenance-page-background-pattern').show();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').hide();         
      } else if( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'image' ) {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').show();
         $('tr.maintenance-page-background-color').hide();
      } else if ( $('input[name="admin_site_enhancements[maintenance_page_background]"]:checked').val() == 'solid_color' ) {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').show();
      } else {
         $('tr.maintenance-page-background-pattern').hide();
         $('tr.maintenance-page-background-image').hide();
         $('tr.maintenance-page-background-color').hide();         
      }

      $('input[name="admin_site_enhancements[maintenance_page_background]"]').click(function() {
         var radioValue = $(this).attr('value');
         if ( radioValue == 'pattern' ) {
            $('tr.maintenance-page-background-pattern').show();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').hide();            
         } else if ( radioValue == 'image' ) {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').show();
            $('tr.maintenance-page-background-color').hide();
         } else if ( radioValue == 'solid_color' ) {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').show();
         } else {
            $('tr.maintenance-page-background-pattern').hide();
            $('tr.maintenance-page-background-image').hide();
            $('tr.maintenance-page-background-color').hide();         
         }
      });
      /*! </fs_premium_only> */
      
      // Content Toggler
      $('.asenha-body').on('click', '.asenha-content-toggler', function(e) {
         e.preventDefault();
         var targetSelector = $(this).data('target-selector');
         var showText = $(this).data('show-text');
         var hideText = $(this).data('hide-text');
         var expanded = $(this).attr('data-expanded');
         // $(targetSelector).toggle();
         if (expanded == 'no') {
            $(targetSelector).slideDown(200);
            $(this).html(hideText + ' <span>▲</span>');
            $(this).attr('data-expanded','yes');
         } else {
            $(targetSelector).slideUp(200);
            $(this).html(showText + ' <span>▼</span>');
            $(this).attr('data-expanded','no');
         }
      });
      
      // Media frame handler for image selection / upload fields
      // Reference: https://plugins.trac.wordpress.org/browser/bm-custom-login/trunk/bm-custom-login.php
      function media_frame_init( selector, button_selector ) {
         // media_frame_init( '#login-page-logo-image', '#login-page-logo-image-button' );
         var theSelector = $(selector);
         var button = $(button_selector);

         button.click(function (event) {
            event.preventDefault();

            // Configuration of the media frame new instance
            wp.media.frames.frame = wp.media({
               title: adminPageVars.mediaFrameTitle,
               multiple: false,
               library: {
                  type: 'image'
               },
               button: {
                  text: adminPageVars.mediaFrameButtonText
               }
            });

            // Function used for the image selection and media manager closing
            var media_set_image = function() {
               var selection = wp.media.frames.frame.state().get('selection');

               // Nothing is selected
               if (!selection) {
                  return;
               }

               // Iterate through selected elements
               selection.each(function(attachment) {
                  // console.log(attachment);
                  var rawUrl = attachment.attributes.url;
                  var url = rawUrl;
                  url = url.replace( adminPageVars.wpcontentUrl, '' );
                  theSelector.val(url);

                  if ( '#login-page-logo-image' == selector ) {
                     var attachmentId = $('.login-page-logo-image-attachment-id input');
                     var originalWidthInput = $('.login-page-logo-image-width-original input');
                     var originalHeightInput = $('.login-page-logo-image-height-original input');
                     var widthInput = $('.login-page-logo-image-width input');
                     var heightInput = $('.login-page-logo-image-height input');
                     attachmentId.val(attachment.attributes.id);
                     originalWidthInput.val(attachment.attributes.width);
                     originalHeightInput.val(attachment.attributes.height);
                     widthInput.val(attachment.attributes.width);
                     heightInput.val(attachment.attributes.height);                     
                  }

                  

                  if ( '#form-builder-email-header-image' == selector ) {
                     var attachmentId = $('.form-builder-email-header-image-attachment-id input');
                     attachmentId.val(attachment.attributes.id);
                  }
               });
            };

            wp.media.frames.frame.on('close', media_set_image);
            wp.media.frames.frame.on('select', media_set_image);
            wp.media.frames.frame.open();
         });
      }

      

      // Form Builder - Empty out stored/hidden attachment ID when "Email header image" field is emptied
      var emailHeaderImage = $('#form-builder-email-header-image');
      var emailHeaderImageAttachmentId = $('.form-builder-email-header-image-attachment-id input');
      
      $(emailHeaderImage).keyup(delay(function (e) {
         if ($(this).val().length === 0) {
            emailHeaderImageAttachmentId.val("");
         }
      }, 200));

      // =============== Image Ratio Calculator / Preservation for Login Page Customizer >> Logo Image =================

      // Code modified from: https://codepen.io/tobiasdev/pen/XNjxdZ by Tobias Bogliolo

      var initialWidth, initialHeight, newWidth, newHeight, aspectRatio;

      //Get new values:
      function getValues(){
         initialWidth = $(".login-page-logo-image-width-original input").val();
         initialHeight = $(".login-page-logo-image-height-original input").val();
         newWidth = $(".login-page-logo-image-width input").val();
         newHeight = $(".login-page-logo-image-height input").val();
      };

      //Aspect ratio:
      function getAspectRatio(){
         // Formula: "Aspect Ratio = Width / Height".
         return aspectRatio = initialWidth/initialHeight;
      };

      //Get new height:
      $(".login-page-logo-image-width input").on("change keyup", function(){
         // Refresh data.
         getValues();
         getAspectRatio();
         // New height = new width / (original width / original height).
         newHeight = Math.round(newWidth/aspectRatio);
         // Output:
         $(".login-page-logo-image-height input").val(newHeight);
      });

      //Get new width:
      $(".login-page-logo-image-height input").on("change keyup", function(){
         // Refresh data.
         getValues();
         getAspectRatio();
         // New width = (original width / original height) * new height.
         newWidth = Math.round(newHeight*aspectRatio);
         // Output:
         $(".login-page-logo-image-width input").val(newWidth);
      });

      //Reset:
      $(".login-page-logo-image-width-original input, .login-page-logo-image-height-original input").on("change keyup", function(){
         // Output:
         $(".login-page-logo-image-width input").val("");
         $(".login-page-logo-image-height input").val("");
      });
            
      // =============== ASE PRO =================

      if ( asenhaStats.isYearEndPromoPeriod ) {

         // Promo nudge
         if ( asenhaStats.hidePromoNudge ) {
            $('.asenha-promo-nudge').hide();
            $('#bottom-upgrade-nudge').show();
         } else {
            $('.asenha-promo-nudge').show();
            $('#bottom-upgrade-nudge').hide();
         }
         
         $('#dismiss-promo-nudge').click(function(e) {
            e.preventDefault();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'dismiss_promo_nudge',
                  'nonce': adminPageVars.nonce
               },
               success:function(data) {
                  $('.asenha-promo-nudge').hide();
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
               }
            });
         });
         
      } else {

         // Upgrade nudge to Pro
         if ( asenhaStats.hideUpgradeNudge ) {
            $('.asenha-upgrade-nudge').hide();
            $('#bottom-upgrade-nudge').show();
         } else {
            $('.asenha-upgrade-nudge').show();
            $('#bottom-upgrade-nudge').hide();
         }

         $('#dismiss-upgrade-nudge').click(function(e) {
            e.preventDefault();
            $.ajax({
               url: ajaxurl,
               data: {
                  'action':'dismiss_upgrade_nudge',
                  'nonce': adminPageVars.nonce
               },
               success:function(data) {
                  $('.asenha-upgrade-nudge').hide();
                  // $('#bottom-upgrade-nudge').show();
               },
               error:function(errorThrown) {
                  console.log(errorThrown);
               }
            });
         });

      }
      
      // =============== SPONSORSHIP =================

      // Stats on saving changes from asenha_admin_scripts() wp_localize_script() is availble in the 'asenhaStats' object-----
      // console.log( asenhaStats );
      // alert(JSON.stringify(asenhaStats));
      if ( asenhaStats.showSupportNudge ) {
         $('.asenha-support-nudge').show();
      } else {
         $('.asenha-support-nudge').hide();
      }

      $('#have-shared,#have-reviewed').click(function(e) {
         e.preventDefault();
         // $.ajax({
         //    url: 'https://bowo.io/asenha-sp-ndg',
         //    method: 'GET',
         //    dataType: 'jsonp',
         //    crossDomain: true
         // });
         $.ajax({
            url: ajaxurl,
            data: {
               'action':'have_supported',
               'nonce': adminPageVars.nonce
            },
            success:function(data) {
               $('.asenha-support-nudge').hide();
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });
      
      $('#support-nudge-dismiss').click(function(e) {
         e.preventDefault();
         // $.ajax({
         //    url: 'https://bowo.io/asenha-sp-ndg',
         //    method: 'GET',
         //    dataType: 'jsonp',
         //    crossDomain: true
         // });
         $.ajax({
            url: ajaxurl,
            data: {
               'action':'dismiss_support_nudge',
               'nonce': adminPageVars.nonce
            },
            success:function(data) {
               $('.asenha-support-nudge').hide();
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });

      // Expand support notice | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('.asenha-support-nudge.nudge-show-more > .show-more').click(function(e) {

         e.preventDefault();

         var $this = $(this);
         $this.toggleClass('show-more');
         $this.hide();

         if ($this.hasClass('show-more')) {
            $this.next().removeClass('opened',0);
         } else {
            $this.next().addClass('opened',0);
         }

      });

      // Collapse support notice | Modified from https://codepen.io/symonsays/pen/rzgEgY
      $('#support-nudge-show-less').click(function(e) {

         e.preventDefault();

         $('.nudge-wrapper-show-more').removeClass('opened',0);
         $('#support-nudge-show-moreless').addClass('show-more');
         $('#support-nudge-show-moreless').show();

      });

      

      // Modal for sponsoring plugin dev and maintenance: https://stephanwagner.me/jBox

      // var sponsorModal = new jBox('Modal', {
      //    attach: '#plugin-sponsor',
      //    trigger: 'click', // or 'mouseenter'
      //    // content: 'Test'
      //    content: $('#asenha-sponsor'),
      //    width: 740, // pixels
      //    closeButton: 'box',
      //    addClass: 'plugin-sponsor-modal',
      //    overlayClass: 'plugin-sponsor-modal-overlay',
      //    target: '#wpwrap', // where to anchor the modal
      //    position: {
      //       x: 'center',
      //       y: 'top'
      //    },
      //    // fade: 1000,
      //    animation: {
      //       open: 'slide:top',
      //       close: 'slide:top'
      //    }
      // });
      
      // $('#plugin-sponsor').click( function() {
      //    $.ajax({
      //       url: 'https://bowo.io/asenha-sp-btn',
      //       method: 'GET',
      //       dataType: 'jsonp',
      //       crossDomain: true
      //       // success: function(response) {
      //       //    console.log(response);
      //       // }
      //    });
      // });

   }); // END OF $(document).ready()

})( jQuery );