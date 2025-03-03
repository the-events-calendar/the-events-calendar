/**
 * Makes sure we have all the required levels on the Tribe Object.
 *
 * @since @TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};

/**
 * Configures the Category Colors Object in the Global Tribe variable.
 *
 * @since @TBD
 *
 * @type {PlainObject}
 */
tribe.events.admin.categoryColors = {};

/**
 * Initializes the script in a Strict environment.
 *
 * @since @TBD
 *
 * @param {PlainObject} $   jQuery
 * @param {PlainObject} obj tribe.events.admin.categoryColors
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';

	const $document = $( document );

	/**
	 * Detects which page we are on.
	 *
	 * @since @TBD
	 *
	 * @type {boolean}
	 */
	obj.isAddPage = $( '#addtag' ).length > 0;
	obj.isEditPage = $( '#edittag' ).length > 0;

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since @TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		colorInput: '.tec-category-colors__input[type="text"]',
		preview: '.tec-category-colors__preview span',
		previewText: '.tec-category-colors__preview-text',
		tagName: 'input[name="tag-name"], input[name="name"]', // Handles both add and edit
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: obj.isAddPage ? '#addtag' : '#edittag', // Only select the correct form
	};

	/**
	 * Updates the preview styling based on selected colors.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.updatePreview = function () {
		const primaryColor = $( '#tec-events-category-primary' ).val() || 'transparent';
		const backgroundColor = $( '#tec-events-category-secondary' ).val() || 'transparent';
		const fontColor = $( '#tec-events-category-text' ).val() || 'inherit';

		// Apply styles dynamically
		$( obj.selectors.preview ).css( {
											'border-left': `5px solid ${ primaryColor }`,
											'background-color': backgroundColor,
										} );

		$( obj.selectors.previewText ).css( {
												'color': fontColor,
											} );
	};

	/**
	 * Updates the preview text based on the tag name input.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.updatePreviewText = function () {
		const $tagInput = $( obj.selectors.tagName ).first(); // Ensure we only get the first available input
		const $previewText = $( obj.selectors.previewText );
		const defaultText = $previewText.data( 'default-text' ) || 'Empty';
		const tagValue = $tagInput.val().trim();

		// Update preview text
		$previewText.text( tagValue.length ? tagValue : defaultText );
	};

	/**
	 * Resets the form fields and preview on form submission (ONLY for Add Page).
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.resetForm = function () {
		// Only reset form if on the Add Page
		if ( ! obj.isAddPage ) {
			return;
		}

		// Reset all color fields properly
		$( obj.selectors.colorInput ).each( function () {
			const $input = $( this );
			const $container = $input.closest( '.wp-picker-container' );

			// Reset the input value
			$input.val( '' ).change();

			// Manually reset the Iris picker
			$input.wpColorPicker(
				'color',
				false
			);

			// Reset WP Color Picker button styles
			$container.find( '.wp-color-result' ).css( {
														   'background-color': '',
														   'border-color': '',
													   } );
		} );

		// Reset priority field to 0
		$( obj.selectors.priorityField ).val( 0 );

		// Reset preview text to default
		const $previewText = $( obj.selectors.previewText );
		const defaultText = $previewText.data( 'default-text' ) || 'Example';
		$previewText.text( defaultText );

		// Reset preview styles
		obj.updatePreview();
	};

	/**
	 * Initializes the WordPress Color Picker on the category color inputs.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.initColorPicker = function () {
		$( obj.selectors.colorInput ).wpColorPicker( {
														 change: obj.updatePreview, // Update on color change
														 clear: obj.updatePreview,  // Update when cleared
													 } );
	};

	/**
	 * Handles the initialization when the document is ready.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		obj.initColorPicker();
		obj.updatePreview(); // Ensure preview is set on page load
		obj.updatePreviewText(); // Ensure preview text is set on page load

		// Attach event listener to the tag-name input field
		$( obj.selectors.tagName )
			.on(
				'input',
				obj.updatePreviewText
			);

		// Reset form fields on form submission (ONLY for Add Page)
		if ( obj.isAddPage ) {
			$( obj.selectors.form )
				.on(
					'submit',
					obj.resetForm
				);
		}
	};

	// Configure on document ready.
	$document.ready( obj.ready );

} )(
	jQuery,
	tribe.events.admin.categoryColors
);
