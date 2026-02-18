(function( $ ) {
   'use strict';

   /**
    * Get an element by ID without using CSS selector parsing.
    *
    * This avoids jQuery selector syntax errors when IDs contain percent-encoded
    * sequences (e.g. %e3%83...) which can happen for multibyte site languages.
    *
    * @param {string} id Element ID (without '#').
    * @return {Object} jQuery object (empty set when not found).
    */
   function asenhaGetById( id ) {
      var el = document.getElementById( id );
      return el ? $( el ) : $();
   }

   $(document).ready( function() {

      // ----- Menu Ordering -----

      // Initialize sortable elements for parent menu items: https://api.jqueryui.com/sortable/
      $('#custom-admin-menu').sortable({
         items: '> li',
         opacity: 0.6,
         placeholder: 'sortable-placeholder',
         tolerance: 'pointer',
         revert: 250
      });

      // Get the default/current menu order
      let menuOrder = $('#custom-admin-menu').sortable("toArray").toString();

      // Set hidden input value for saving in options
      document.getElementById('custom_menu_order').value = menuOrder;

      // Save custom order into a comma-separated string, triggerred after each drag and drop of menu item
      // https://api.jqueryui.com/sortable/#event-update
      // https://api.jqueryui.com/sortable/#method-toArray
      $('#custom-admin-menu').on( 'sortupdate', function( event, ui) {

         // Get the updated menu order
         let menuOrder = $('#custom-admin-menu').sortable("toArray").toString();

         // Set hidden input value for saving in options
         document.getElementById('custom_menu_order').value = menuOrder;

      });

      

      // ----- Parent Menu Item Hiding -----

      // Prepare constant to store IDs of menu items that will be hidden
      if ( document.getElementById('custom_menu_hidden') != null ) {
         var hiddenMenuItems = document.getElementById('custom_menu_hidden').value.split(","); // array
      } else {
         var hiddenMenuItems = []; // array
      }


      // Detect which menu items are being checked. Ref: https://stackoverflow.com/a/3871602
      Array.from(document.getElementsByClassName('parent-menu-hide-checkbox')).forEach(function(item,index,array) {

         item.addEventListener('click', event => {

            if (event.target.checked) {

               // Add ID of menu item to array
               hiddenMenuItems.push(event.target.dataset.menuItemId);
               
            } else {

               // Remove ID of menu item from array
               const start = hiddenMenuItems.indexOf(event.target.dataset.menuItemId);
               const deleteCount = 1;
               hiddenMenuItems.splice(start, deleteCount);

            }

            // Set hidden input value
            document.getElementById('custom_menu_hidden').value = hiddenMenuItems;

         });

      });

      // Clicking on header save button
      $('#amo-save-changes').click( function(e) {

         e.preventDefault();

         // Prepare variable to store ID-Title pairs of menu items
         var customMenuTitles = []; // empty array

         // Initialize other variables
         var menuItemId = '';
         var customTitle = '';

         // Save default/custom title values. Ref: https://stackoverflow.com/a/3871602
         Array.from(document.getElementsByClassName('menu-item-custom-title')).forEach(function(item,index,array) {

            menuItemId = item.dataset.menuItemId;
            customTitle = item.value;
            customMenuTitles.push(menuItemId + '__' + customTitle);            

         });

         // Set hidden input value
         document.getElementById('custom_menu_titles').value = customMenuTitles;

      });

   }); // End of $(document).ready()

   

})( jQuery );