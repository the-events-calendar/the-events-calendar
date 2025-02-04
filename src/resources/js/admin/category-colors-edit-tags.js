/* global inlineEditTax */
/**
 * Makes sure we have all the required levels on the Tribe Object.
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.category_colors = tribe.events.category_colors || {};
tribe.events.category_colors.edit_tags = {};

/**
 * Initializes the color picker for the edit tags page.
 *
 * @since TBD
 *
 * @param {PlainObject} $   jQuery
 * @param {PlainObject} obj tribe.settings.fields.color
 */
( function( $, doc, obj ) {
	'use strict';

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		colorPickerInput: '.tec-events-category-color-picker',
		quickEditButton: 'button.editinline',
	};

	/**
	 * Handles the initialization of color fields when Document is ready.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.init = function () {
		$( doc ).on( 'click', obj.selectors.quickEditButton, function () {
			const id = inlineEditTax.getId( this );
			const $colorFields = $( '#edit-' + id ).find( obj.selectors.colorPickerInput );
			obj.initColorPicker( $colorFields );
		} );
	};

	/**
	 * Initializes the color picker for the edit tags page.
	 *
	 * @since TBD
	 *
	 * @param {PlainObject} $el The jQuery elements to initialize the color picker on.
	 *
	 * @return {void}
	 */
	obj.initColorPicker = function( $el ) {
		$el.wpColorPicker( {
			hide: true,
		} );
	};

	$( obj.init );

} )( jQuery, window.document, tribe.events.category_colors.edit_tags );
