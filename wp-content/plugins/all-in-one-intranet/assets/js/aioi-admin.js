jQuery( document ).ready( function() {

	// Dependent fields in multisite.
	let clickfn = function() {
		jQuery( '#input_aioi_ms_requiremember' ).prop( 'disabled', ! jQuery( '#input_aioi_privatesite' ).is( ':checked' ) );
	};

	jQuery( '#input_aioi_privatesite' ).on( 'click', clickfn );

	clickfn();
} );
