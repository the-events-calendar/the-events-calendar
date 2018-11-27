jQuery( document ).ready( function( $ ) {
	updateMapsFields();

	// toggle view of the google maps size fields
	$( '.google-embed-size input' ).change( updateMapsFields );

	// toggle view of the google maps size fields
	function updateMapsFields() {
		if ( $( '.google-embed-size input' ).attr( "checked" ) ) {
			$( '.google-embed-field' ).slideDown();
		}
		else {
			$( '.google-embed-field' ).slideUp();
		}
	}

} );

( function( $, data ) {
	"use strict";
	var $document = $( document );

	/**
	 * Check hidden field when Unchecked when the base field is checked first
	 */
	$document.ready( function() {
		// Verify that all WP variables exists
		if ( -1 !== [ typeof pagenow, typeof typenow, typeof adminpage ].indexOf( 'undefined' ) ) {
			return false;
		}

		var $container = $( '#tribe-field-toggle_blocks_editor' );
		var $hiddenContainer = $( '#tribe-field-toggle_blocks_editor_hidden_field' );
		var $field = $container.find( '#tribe-blocks-editor-toggle-field' );
		var $hiddenField = $hiddenContainer.find( '#tribe-blocks-editor-toggle-hidden-field' );

		var isFieldChecked = $field.is( ':checked' );
		var isHiddenFieldChecked = $hiddenField.is( ':checked' );

		// Once this field is check we bail forever
		if ( isHiddenFieldChecked ) {
			return;
		}

		// Only check the hidden field when we change the Field was checked
		if ( isFieldChecked ) {
			$field.one( 'change', function() {
				// Check the hidden field
				$hiddenField.prop( 'checked', true );
			} );
		}

	} );
}( jQuery, {} ) );
