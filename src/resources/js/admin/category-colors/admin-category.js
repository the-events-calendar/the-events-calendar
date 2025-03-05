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
		colorInput: '.tec-events-category-colors__grid input[type="text"].wp-color-picker',
		preview: '.tec-events-category-colors__preview-box span',
		previewText: '.tec-events-category-colors__preview-box-text',
		tagName: 'input[name="tag-name"], input[name="name"]', // Handles both add and edit
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: obj.isAddPage ? '#addtag' : '#edittag', // Only select the correct form
		quickEditButton: '.editinline', // Quick Edit button
		quickEditRow: '.inline-edit-row', // The Quick Edit row
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

	obj.monitorInputChange = function () {
		$(document).on('input', obj.selectors.colorInput, function () {
			obj.updateClosestPreview($(this)); // Pass the changed input to update its closest preview
		});
	};

	obj.colorPickerChange = function () {
			obj.updateClosestPreview($(this)); // Pass the changed input to update its closest preview
	};

	obj.updateClosestPreview = function ($input) {
		const $container = $input.closest('.tec-events-category-colors__container'); // Find the closest color container

		const primaryColor = $container.find('[name="tec_events_category-color[primary]"]').val() || 'transparent';
		const backgroundColor = $container.find('[name="tec_events_category-color[secondary]"]').val() || 'transparent';
		const fontColor = $container.find('[name="tec_events_category-color[text]"]').val() || 'inherit';


		// Apply styles dynamically to the closest preview
		$container.find('.tec-events-category-colors__preview-box span').css({
																			'border-left': `5px solid ${primaryColor}`,
																			'background-color': backgroundColor,
																		});

		$container.find('.tec-events-category-colors__preview-box-text').css({
																				 'color': fontColor,
																			 });
	};

	/**
	 * Initializes the WordPress Color Picker on the category color inputs.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.initColorPicker = function () {
		$( obj.selectors.colorInput ).filter(':visible').wpColorPicker( {
														 change: obj.colorPickerChange, // Update on color change
														 clear: obj.colorPickerChange,  // Update when cleared
													 } );
	};

	/**
	 * Reinitializes the color picker when Quick Edit is clicked.
	 *
	 * @since @TBD
	 *
	 * @return {void}
	 */
	obj.reInitColorPickerOnQuickEdit = function () {
		$( document ).on( 'click', obj.selectors.quickEditButton, function () {
			console.log( 'Quick Edit clicked, waiting for row to render...' );

			// Wait for the Quick Edit row to become visible, then reinitialize color pickers
			setTimeout( function () {
				obj.initColorPicker();
			}, 50 ); // Short delay to ensure Quick Edit row is fully rendered
		});
	};

	/**
	 * Closes color picker when clicking outside or after selecting a color.
	 *
	 * @since @TBD
	 */
	obj.closeColorPicker = function () {
		$document.on(
			'click',
			function ( event ) {
				// Check if the click target is outside our category color picker inputs
				if ( ! $( event.target )
					.closest( obj.selectors.colorInput + ', .wp-picker-container, .iris-picker' ).length ) {
					// Only fade out pickers within our scoped category color inputs
					$( obj.selectors.colorInput )
						.closest( '.wp-picker-container' )
						.find( '.iris-picker' )
						.fadeOut();
				}
			}
		);
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
		obj.updatePreviewText(); // Ensure preview text is set on page load
		obj.closeColorPicker();
		obj.reInitColorPickerOnQuickEdit();
		obj.monitorInputChange();

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
