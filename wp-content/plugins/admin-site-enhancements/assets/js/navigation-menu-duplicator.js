/**
 * Navigation Menu Duplicator — append Duplicate Menu button on nav-menus.php.
 *
 * @package Admin and Site Enhancements
 */
( function ( $ ) {
	'use strict';

	$( function () {
		if ( typeof asenhaNavigationMenuDuplicator === 'undefined' ) {
			return;
		}

		var $form = $( '#update-nav-menu' );

		if ( ! $form.length ) {
			return;
		}

		var $publishingAction = $form.find( '#nav-menu-footer .publishing-action' );

		if ( ! $publishingAction.length ) {
			return;
		}

		var $spinner = $( '<div>', {
			class: 'asenha-duplicate-menu-spinner spinner',
			css: { display: 'none' },
		} );

		var $button = $( '<a>', {
			class: 'asenha-duplicate-menu-button button button-large',
			href: asenhaNavigationMenuDuplicator.duplicateUrl,
			text: asenhaNavigationMenuDuplicator.duplicateLabel,
		} );

		$button.on( 'click', function () {
			$spinner.addClass( 'is-active' ).show();
		} );

		$publishingAction.append( $spinner ).append( $button );
	} );
}( jQuery ) );
