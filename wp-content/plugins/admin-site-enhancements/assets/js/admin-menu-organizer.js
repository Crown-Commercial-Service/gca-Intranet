(function( $ ) {
   'use strict';

   $(document).ready( function() {
      var strings = ( typeof amoPageVars !== 'undefined' && amoPageVars.strings ) ? amoPageVars.strings : {};

      // Save changes
      $('#amo-save-changes').click(function(e) {
         e.preventDefault();
         $('.asenha-saving-changes').fadeIn();
         
         var menu_data = {
            'action':'save_admin_menu',
            'nonce': amoPageVars.saveMenuNonce,            
            'custom_menu_order': document.getElementById('custom_menu_order').value,
            'custom_menu_titles': document.getElementById('custom_menu_titles').value,
            'custom_menu_hidden': document.getElementById('custom_menu_hidden').value
         }

         
         
         $.ajax({
            type: "post",
            url: ajaxurl,
            dataType: 'json',
            data: menu_data,
            success:function(response) {
               $('.asenha-saving-changes').hide();

               if ( ! response || response.success !== true ) {
                  if ( strings.saveChangesError ) {
                     window.alert( strings.saveChangesError );
                  }
                  return;
               }

               

               $('.asenha-changes-saved').fadeIn(400).delay(2500).fadeOut(400);
            },
            error:function(jqXHR) {
               var message = strings.saveChangesError || '';

               $('.asenha-saving-changes').hide();

               if ( jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
                  message = jqXHR.responseJSON.data.message;
               }

               if ( message ) {
                  window.alert( message );
               }
               console.log(jqXHR);
            }
         });
      });

      

   }); // END OF $(document).ready()

})( jQuery );