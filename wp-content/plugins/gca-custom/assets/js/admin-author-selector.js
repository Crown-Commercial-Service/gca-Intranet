/* global gcaAuthorSelector */
( function ( $ ) {
	'use strict';

	/**
	 * Builds a jQuery element for a user option showing their avatar on the
	 * left and display name on the right.
	 *
	 * Used for both the dropdown list (templateResult) and the selected-value
	 * display (templateSelection).
	 *
	 * @param {Object} user  Select2 data object { id, text, img }
	 * @returns {string|jQuery}
	 */
	function formatUser( user ) {
		// Placeholder / loading states have no id.
		if ( ! user.id ) {
			return user.text;
		}

		var $el = $( '<span class="gca-user-option">' );

		if ( user.img ) {
			$el.append(
				$( '<img>', {
					src:    user.img,
					alt:    '',
					'class': 'gca-user-option__avatar',
					width:  24,
					height: 24,
				} )
			);
		}

		// Text node avoids any accidental HTML injection.
		$el.append( document.createTextNode( ' ' + user.text ) );

		return $el;
	}

	$( document ).ready( function () {
		var $select = $( '#gca-author-select' );

		if ( ! $select.length || typeof $.fn.select2 === 'undefined' ) {
			return;
		}

		$select.select2( {
			// All users are pre-loaded; no AJAX needed.
			data: gcaAuthorSelector.users,

			templateResult:    formatUser,
			templateSelection: formatUser,

			// Allow searching without a minimum number of characters.
			minimumInputLength: 0,

			allowClear:  true,
			placeholder: 'Search for a user',

			// templateResult / templateSelection return jQuery objects, so
			// Select2 must not escape them as strings.
			escapeMarkup: function ( m ) {
				return m;
			},
		} );

		// Re-apply the current author so Select2 shows the pre-selected value.
		if ( gcaAuthorSelector.currentAuthorId ) {
			$select.val( gcaAuthorSelector.currentAuthorId ).trigger( 'change.select2' );
		}
	} );
} )( jQuery );
