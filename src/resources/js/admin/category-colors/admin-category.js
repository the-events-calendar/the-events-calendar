/**
 * Ensures we have all required levels on the Tribe Object.
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};

/**
 * Configures the Category Colors Object in the Global Tribe variable.
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events.admin.categoryColors = {};

/**
 * Initializes the script in a strict environment.
 *
 * @since TBD
 *
 * @param {PlainObject} $   jQuery.
 * @param {PlainObject} obj tribe.events.admin.categoryColors.
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';

	const $document = $( document );

	/**
	 * Detects which page we are on.
	 *
	 * @since TBD
	 *
	 * @type {boolean}
	 */
	obj.isAddPage = $( '#addtag' ).length > 0;
	obj.isEditPage = $( '#edittag' ).length > 0;

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		colorInput: '.tec-events-category-colors__grid input[type="text"].wp-color-picker',
		preview: '.tec-events-category-colors__preview-box span',
		previewText: '.tec-events-category-colors__preview-box-text',
		tagName: 'input[name="tag-name"], input[name="name"]',
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: obj.isAddPage ? '#addtag' : '#edittag',
		quickEditButton: '.editinline',
		quickEditRow: '.inline-edit-row',
		colorContainer: '.tec-events-category-colors__container',
		primaryColor: '[name="tec_events_category-color[primary]"]',
		backgroundColor: '[name="tec_events_category-color[secondary]"]',
		fontColor: '[name="tec_events_category-color[text]"]',
		tableColorPreview: '.column-category_color .tec-events-taxonomy-table__category-color-preview',
	};

	/**
	 * Updates the preview text based on the tag name input.
	 *
	 * @since TBD
	 *
	 * @param {Event} event The input event.
	 *
	 * @return {void}
	 */
	obj.updatePreviewText = function ( event ) {
		const $tagInput = $( obj.selectors.tagName ).first();
		const $previewText = $( obj.selectors.previewText );
		const defaultText = $previewText.data( 'default-text' ) || 'Empty';
		const tagValue = $tagInput.val().trim();

		// Update preview text.
		$previewText.text( tagValue.length ? tagValue : defaultText );
	};

	/**
	 * Updates the closest preview based on the changed input.
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $input The input field being modified.
	 *
	 * @return {void}
	 */
	obj.updateClosestPreview = function ( $input ) {
		const $container = $input.closest( obj.selectors.colorContainer );

		const primaryColor = $container.find( obj.selectors.primaryColor ).val() || 'transparent';
		const backgroundColor = $container.find( obj.selectors.backgroundColor ).val() || 'transparent';
		const fontColor = $container.find( obj.selectors.fontColor ).val() || 'inherit';

		$container.find( obj.selectors.preview ).css({
														 'border-left': `5px solid ${primaryColor}`,
														 'background-color': backgroundColor,
													 });

		$container.find( obj.selectors.previewText ).css({
															 'color': fontColor,
														 });
	};

	/**
	 * Monitors input changes in color fields and updates the preview.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.monitorInputChange = function () {
		let colorPickerTimer;
		$( document ).on( 'input', obj.selectors.colorInput, function () {
			const $input = $( this );

			obj.updateClosestPreview( $input );

			clearTimeout( colorPickerTimer )
			colorPickerTimer = setTimeout( function () {
				const newColor = $input.val().trim();

				// Ensure it's a valid hex color before applying.
				if ( /^#([0-9A-F]{3}){1,2}$/i.test( newColor ) ) {
					$input.iris( 'color', newColor );
				}
			}, 500 );
		});
	};

	/**
	 * Handles changes from the WordPress color picker.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.colorPickerChange = function () {
			obj.updateClosestPreview( $( this ) );
	};

	/**
	 * Initializes the WordPress Color Picker on visible category color inputs.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.initColorPicker = function () {
		$( obj.selectors.colorInput ).filter( ':visible' ).wpColorPicker({
																			 change: obj.colorPickerChange,
																			 clear: obj.colorPickerChange,
																		 });
	};

	/**
	 * Reinitializes the color picker when Quick Edit is clicked.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.reInitColorPickerOnQuickEdit = function () {
		$(document).on('click', obj.selectors.quickEditButton, function () {
			const $quickEditRow = $('.inline-edit-row');
			if (!$quickEditRow.length) {
				return;
			}

			const $parentTr = $(this).closest('tr');
			const $colorPreview = $parentTr.find( obj.selectors.tableColorPreview );

			const colors = {
				primary: $colorPreview.data('primary') || '',
				secondary: $colorPreview.data('secondary') || '',
				text: $colorPreview.data('text') || ''
			};

			setTimeout(() => {
				['primary', 'secondary', 'text'].forEach(colorType => {
					const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
					if (!$input.length) return;

					$input.val(colors[colorType]).wpColorPicker({
																	change: obj.colorPickerChange,
																	clear: obj.colorPickerChange
																});
					obj.updateClosestPreview( $input );
				});

			}, 25);
		});
	};

	/**
	 * Initializes event listeners for Quick Edit interactions.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.initQuickEditHandlers = function () {
		// Handle clicks on Save and Cancel in Quick Edit
		$(document).on('click', '.inline-edit-save .save, .inline-edit-save .cancel', obj.handleQuickEditClose);

		// Handle Quick Edit AJAX completion
		$(document).ajaxComplete(obj.handleQuickEditAjaxComplete);
	};

	/**
	 * Handles the closing of Quick Edit via Save or Cancel buttons.
	 *
	 * @since TBD
	 *
	 * @param {Event} event The event object.
	 * @return {void}
	 */
	obj.handleQuickEditClose = function (event) {
		obj.cleanupColorPickers();

		// Prevent potential conflicts with other scripts
		event.stopImmediatePropagation();
	};

	/**
	 * Handles AJAX completion for Quick Edit save.
	 *
	 * @since TBD
	 *
	 * @param {Event} event The event object.
	 * @param {XMLHttpRequest} xhr The XMLHttpRequest object.
	 * @param {Object} settings The settings object for the AJAX request.
	 * @return {void}
	 */
	obj.handleQuickEditAjaxComplete = function (event, xhr, settings) {
		if (settings.data && settings.data.includes("action=inline-save-tax")) {
			obj.cleanupColorPickers();
		}
	};

	/**
	 * Cleans up and resets WP Color Pickers in Quick Edit.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.cleanupColorPickers = function () {
		const $quickEditRow = $('.inline-edit-row');

		$quickEditRow.find(obj.selectors.colorInput).each(function () {
			const $input = $(this);
			const $wrapper = $input.closest('.wp-picker-container');

			if ($wrapper.length) {
				// Clone a fresh input
				const $clone = $input.clone().removeClass('wp-color-picker').removeAttr('style');

				// Replace the old input with a clean version
				$wrapper.before($clone);
				$wrapper.remove();
			}
		});
	};

	/**
	 * Closes the color picker when clicking outside or after selecting a color.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.closeColorPicker = function () {
		$document.on( 'click', function ( event ) {
			if ( ! $( event.target ).closest( obj.selectors.colorInput + ', .wp-picker-container, .iris-picker' ).length ) {
				$( obj.selectors.colorInput ).closest( '.wp-picker-container' ).find( '.iris-picker' ).fadeOut();
			}
		});
	};

	/**
	 * Handles initialization when the document is ready.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		obj.initColorPicker();
		obj.updatePreviewText();
		obj.closeColorPicker();
		obj.reInitColorPickerOnQuickEdit();
		obj.initQuickEditHandlers();
		obj.monitorInputChange();

		$( obj.selectors.tagName ).on( 'input', obj.updatePreviewText );

		if ( obj.isAddPage ) {
			$( obj.selectors.form ).on( 'submit', obj.resetForm );
		}
	};

	$document.ready( obj.ready );

})(
	jQuery,
	tribe.events.admin.categoryColors
);
