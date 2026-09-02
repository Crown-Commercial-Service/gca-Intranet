(function( $ ) {
   'use strict';

   $(document).ready( function() {

      // Replace roles dropdown with checkboxes in user editing / creation screens
      var rolesRow = $( '.asenha-roles-temporary-container tr' );
      var roleSelectRow = $( 'select#role' ).closest( 'tr' );

      if ( roleSelectRow.length ) {
         // user-edit.php (other user) or user-new.php — replace core role dropdown row
         roleSelectRow.replaceWith( rolesRow );
      } else if ( $( '.user-user-login-wrap' ).length ) {
         // profile.php — no role dropdown; insert into Name section after Username row
         $( '.user-user-login-wrap' ).after( rolesRow );
      }

      $( '.asenha-roles-temporary-container' ).remove();

   });

})( jQuery );
