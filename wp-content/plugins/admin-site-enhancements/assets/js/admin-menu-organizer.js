(function( $ ) {
   'use strict';

   $(document).ready( function() {

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
            data: menu_data,
            success:function(data) {
               $('.asenha-saving-changes').hide();
               $('.asenha-changes-saved').fadeIn(400).delay(2500).fadeOut(400);
            },
            error:function(errorThrown) {
               console.log(errorThrown);
            }
         });
      });

      

   }); // END OF $(document).ready()

})( jQuery );