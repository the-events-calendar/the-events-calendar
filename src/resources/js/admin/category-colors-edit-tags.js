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

	obj.currentTaxonomy = null;

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		colorPickerInput: '.tec-category-color-picker-input',
		quickEditButton: 'button.editinline',
		primaryColorInput: '.tec-category-color-primary-input',
		backgroundColorInput: '.tec-category-color-background-input',
		textColorInput: '.tec-category-color-text-input',
		nameInput: '.ptitle',
		previewContainer: '.tec-category-colors-quick-edit-preview',
	};

	obj.colorFields = null;

	obj.previewColors = {
		primary: null,
		background: null,
		text: null,
	};

	obj.closeColorPicker = function() {
		const id = inlineEditTax.getId( obj.currentTaxonomy );
		$( '#edit-' + id + ' ' + obj.selectors.colorPickerInput )
			.wpColorPicker.close();
	};

	obj.updateColor = function( event, ui ) {
		const $input = $( event.target );
		const color = ui.color.toString();
		console.log( 'color', color );
		console.log( 'input', $input );
		obj.closeColorPicker();
	};

	obj.updatePreview = function() {
		const id = inlineEditTax.getId( obj.currentTaxonomy );
		const nameValue = $( '#edit-' + id ).find( obj.selectors.nameInput ).val();
		const primaryColorValue = $( '#edit-' + id ).find( obj.selectors.primaryColorInput ).val();
		const bgColorValue = $( '#edit-' + id ).find( obj.selectors.backgroundColorInput ).val();
		const textColorValue = $( '#edit-' + id ).find( obj.selectors.textColorInput ).val();

		$( '#edit-' + id ).find( obj.selectors.previewContainer );
	};

	obj.colorPickerOptions = {
		hide: true,
		change: obj.updateColor,
		pallettes: false,
	};

	obj.setEventHandlers = function() {
		const id = inlineEditTax.getId( obj.currentTaxonomy );
		$( '#edit-' + id ).find( obj.selectors.colorPickerInput ).on( 'change', obj.updatePreview );
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
			obj.currentTaxonomy = this;
			obj.initColorPicker();
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
	obj.initColorPicker = function() {
		const id = inlineEditTax.getId( obj.currentTaxonomy );
		$( '#edit-' + id + ' ' + obj.selectors.colorPickerInput )
			.wpColorPicker( obj.colorPickerOptions );
	};

	$( obj.init );

} )( jQuery, window.document, tribe.events.category_colors.edit_tags );
