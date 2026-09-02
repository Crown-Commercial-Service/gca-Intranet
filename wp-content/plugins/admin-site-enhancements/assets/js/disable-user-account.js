( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.asenha-user-account-toggle', function ( e ) {
		e.preventDefault();

		var $link = $( this );
		var userId = parseInt( $link.data( 'user-id' ), 10 );
		var setDisabled = parseInt( $link.attr( 'data-set-disabled' ), 10 ) === 1;

		if ( ! userId || typeof asenhaDisableUserAccount === 'undefined' ) {
			return;
		}

		$link.css( 'pointer-events', 'none' );

		$.post( asenhaDisableUserAccount.ajaxUrl, {
			action: 'asenha_user_account_toggle_disabled',
			nonce: asenhaDisableUserAccount.nonce,
			user_id: userId,
			set_disabled: setDisabled ? 1 : 0
		} )
			.done( function ( response ) {
				if ( ! response || ! response.success || ! response.data ) {
					return;
				}
				var $row = $( '#user-' + userId );
				$row.find( '.column-asenha_user_account_status' ).html(
					response.data.status_html
				);
				$link.text( response.data.action_label );
				$link.attr(
					'data-set-disabled',
					response.data.next_set_disabled
				);
			} )
			.always( function () {
				$link.css( 'pointer-events', '' );
			} );
	} );
} )( jQuery );
