(function( $ ) {
	'use strict';
	$(document).ready( function() {
   	
		// Move "Manage Columns" button to the right of "Filter" button on list tables
		$('#content-order-button').appendTo('.tablenav.top .alignleft.actions:not(.bulkactions)');
      
	}); // END OF $(document).ready()
})( jQuery );
