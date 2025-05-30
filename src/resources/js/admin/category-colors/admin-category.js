/**
 * Ensures the Tribe object has the required levels.
 *
 * @since TBD
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};
tribe.events.admin.categoryColors = {};

/**
 * Initializes the script.
 *
 * @since TBD
 *
 * @param {PlainObject} $   jQuery instance.
 * @param {PlainObject} obj The category colors object.
 */
(($, obj) => {
	'use strict';

	const $document = $(document);
	let activePicker = null;
	let isOpening = false;

	/**
	 * Determines if the current page is the add or edit category page.
	 *
	 * @since TBD
	 */
	obj.isAddPage = Boolean($('#addtag').length);
	obj.isEditPage = Boolean($('#edittag').length);

	/**
	 * Selectors for targeting elements.
	 *
	 * @since TBD
	 */
	obj.selectors = {
		colorInput: '.tec-events-category-colors__input.wp-color-picker',
		preview: '.tec-events-category-colors__preview-text',
		previewText: '.tec-events-category-colors__preview-text',
		tagName: 'input[name="tag-name"], input[name="name"]',
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: obj.isAddPage ? '#addtag' : '#edittag',
		quickEditButton: '.editinline',
		quickEditRow: '.inline-edit-row',
		quickEditSave: '.inline-edit-save .save',
		quickEditCancel: '.inline-edit-save .cancel',
		colorContainer: '.tec-events-category-colors__container',
		primaryColor: '[name="tec_events_category-color[primary]"]',
		backgroundColor: '[name="tec_events_category-color[secondary]"]',
		fontColor: '[name="tec_events_category-color[text]"]',
		tableColorPreview: '.column-category_color .tec-events-taxonomy-table__category-color-preview',
		wpPickerContainer: '.wp-picker-container',
		irisPicker: '.iris-picker',
		hideFromLegendField: '[name="tec_events_category-color[hide_from_legend]"]',
		inlineAdminCssID: 'tec-events-category-colors-admin-style-inline-css'
	};

	/**
	 * Updates the preview text based on the closest tag name input.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} element The tag input element.
	 */
	obj.updatePreviewText = (element) => {
		if (!element) return;

		const $tagInput = $(element);
		const $container = $tagInput.closest('form, .inline-edit-row');
		const $previewText = $container.find(obj.selectors.previewText);
		const defaultText = $previewText.data('default-text') || '';
		const tagValue = $tagInput.val().trim();

		$previewText.text(tagValue.length ? tagValue : defaultText);
	};

	/**
	 * Updates the closest preview based on the input values.
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $input The input field being modified.
	 */
	obj.updateClosestPreview = ($input) => {
		const $container = $input.closest(obj.selectors.colorContainer);

		const primaryColor = $container.find(obj.selectors.primaryColor).val() || 'transparent';
		const backgroundColor = $container.find(obj.selectors.backgroundColor).val() || 'transparent';
		const fontColor = $container.find(obj.selectors.fontColor).val() || 'inherit';

		$container.find( obj.selectors.preview ).css( {
												'border-left': `5px solid ${ primaryColor }`,
												'background-color': backgroundColor,
												} );

		$container.find(obj.selectors.previewText).css({ 'color': fontColor });
	};

	/**
	 * Handles color picker changes.
	 *
	 * @since TBD
	 */
	obj.colorPickerChange = function () {
		obj.updateClosestPreview($(this));
	};

	/**
	 * Monitors color input changes and updates the preview.
	 *
	 * @since TBD
	 */
	obj.monitorInputChange = () => {
		let colorPickerTimer;

		$document.on('input', obj.selectors.colorInput, function () {
			const $input = $(this);
			obj.updateClosestPreview($input);

			clearTimeout(colorPickerTimer);
			colorPickerTimer = setTimeout(() => {
				const newColor = $input.val().trim();

				if (/^#([0-9A-F]{3}){1,2}$/i.test(newColor)) {
					$input.iris('color', newColor);
				}
			}, 200);
		});
	};

	/**
	 * Closes any open color picker.
	 *
	 * @since TBD
	 */
	obj.closeActivePicker = () => {
		if (activePicker) {
			activePicker.iris('hide');
			activePicker = null;
		}
	};

	/**
	 * Initializes the WordPress Color Picker on visible inputs.
	 *
	 * @since TBD
	 */
	obj.initColorPicker = () => {
		$(obj.selectors.colorInput).filter(':visible').each(function() {
			const $input = $(this);
			
			// Initialize wpColorPicker with custom options
			$input.wpColorPicker({
				change: function(event, ui) {
					obj.colorPickerChange.call(this);
					activePicker = $input;
				},
				clear: function() {
					obj.colorPickerChange.call(this);
					activePicker = null;
				}
			});

			// Add click handler to close other pickers
			$input.on('click', function(e) {
				e.stopPropagation();
				if (activePicker && activePicker[0] !== this) {
					obj.closeActivePicker();
				}
				activePicker = $input;
			});
		});

		obj.initializePreviews();
	};

	/**
	 * Initializes preview text and color previews on page load.
	 *
	 * @since TBD
	 */
	obj.initializePreviews = () => {
		$(obj.selectors.tagName).each(function () {
			obj.updatePreviewText(this);
		});

		$(obj.selectors.colorInput).each(function () {
			obj.updateClosestPreview($(this));
		});
	};

	/**
	 * Cleans up and resets WP Color Pickers in Quick Edit.
	 *
	 * @since TBD
	 */
	obj.cleanupColorPickers = () => {
		$(obj.selectors.quickEditRow).find(obj.selectors.colorInput).each(function () {
			const $input = $(this);
			const $wrapper = $input.closest(obj.selectors.wpPickerContainer);

			if ($wrapper.length) {
				const $clone = $input.clone().removeClass('wp-color-picker').removeAttr('style');
				$wrapper.before($clone);
				$wrapper.remove();
			}
		});
	};

	/**
	 * Handles Quick Edit interactions and reinitializes the color picker.
	 *
	 * @since TBD
	 */
	obj.reInitColorPickerOnQuickEdit = () => {
		$document.on('click', obj.selectors.quickEditButton, function() {
			obj.cleanupColorPickers();
			obj.closeActivePicker();

			const $quickEditRow = $(obj.selectors.quickEditRow);
			if (!$quickEditRow.length) return;

			const $parentTr = $(this).closest('tr');
			const $colorPreview = $parentTr.find(obj.selectors.tableColorPreview);

			const colors = {
				primary: $colorPreview.data('primary') || '',
				secondary: $colorPreview.data('secondary') || '',
				text: $colorPreview.data('text') || '',
			};

			const data = {
				priority: $colorPreview.data('priority') || '',
				hide_from_legend: $colorPreview.data('hidden') || '',
			};

			// Initialize color pickers immediately without setTimeout
			['primary', 'secondary', 'text'].forEach(colorType => {
				const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
				if (!$input.length) return;

				$input.val(colors[colorType]);
				// Only initialize if not already initialized
				if (!$input.hasClass('wp-color-picker-initialized')) {
					$input.wpColorPicker({
						change: obj.colorPickerChange,
						clear: obj.colorPickerChange
					});
				}
				obj.updateClosestPreview($input);
			});

			// After initializing, forcibly close all pickers in the row
			$quickEditRow.find('.iris-picker').hide().removeClass('iris-visible');
			activePicker = null;

			// Populate other fields
			const $priorityInput = $quickEditRow.find(obj.selectors.priorityField);
			if ($priorityInput.length) {
				$priorityInput.val(data.priority);
			}

			const $hideLegendCheckbox = $quickEditRow.find(obj.selectors.hideFromLegendField);
			if ($hideLegendCheckbox.length) {
				$hideLegendCheckbox.prop('checked', !!data.hide_from_legend);
			}

			const $tagInput = $quickEditRow.find(obj.selectors.tagName);
			if ($tagInput.length) {
				obj.updatePreviewText($tagInput[0]);
			}
		});
	};

	/**
	 * Initializes event listeners for Quick Edit interactions.
	 *
	 * @since TBD
	 */
	obj.initQuickEditHandlers = () => {
		$document.on(
			'click',
			obj.selectors.quickEditSave + ', ' +
				obj.selectors.quickEditCancel
			,
			obj.cleanupColorPickers
		);
		$document.ajaxComplete(obj.handleQuickEditAjaxComplete);
	};

	/**
	 * Updates the inline styles for a specific category.
	 *
	 * @since TBD
	 *
	 * @param {string} categoryClass The category class (e.g. tribe_events_cat-category-1).
	 * @param {Object} colors The color values to update.
	 */
	obj.updateInlineStyles = (categoryClass, colors) => {
		// Find the inline style element by its ID
		const styleElement = document.getElementById( obj.selectors.inlineAdminCssID );
		if (!styleElement) {
			return;
		}

		// Get the current CSS content and trim any whitespace.
		let css = styleElement.textContent.trim();

		// Create the new CSS rule with consistent formatting.
		const newRule = `${categoryClass}{` +
			`--tec-color-category-primary:${colors.primary || 'inherit'};` +
			`--tec-color-category-secondary:${colors.secondary || 'inherit'};` +
			`--tec-color-category-text:${colors.text || 'inherit'}}`;

		// Check if the category rule already exists.
		const categoryRegex = new RegExp(`${categoryClass}\\s*{[^}]*}`, 'g');
		const existingRule = css.match(categoryRegex);

		if (existingRule) {
			// Replace existing rule.
			css = css.replace(categoryRegex, newRule);
		} else {
			// Add new rule without extra whitespace.
			css += newRule;
		}

		// Update the style element.
		styleElement.textContent = css;
	};

	/**
	 * Checks if a WordPress AJAX response was successful.
	 *
	 * @since TBD
	 *
	 * @param {XMLHttpRequest} xhr The AJAX response object.
	 * @return {boolean} True if the response was successful, false otherwise.
	 */
	obj.isAjaxSuccess = (xhr) => {
		try {
			// For inline-save-tax, a 200 status with HTML response means success.
			if (xhr.status === 200 && xhr.responseText.includes('<tr id="tag-')) {
				return true;
			}

			// Try to parse as JSON first.
			const contentType = xhr.getResponseHeader('content-type');
			if (contentType && contentType.includes('application/json')) {
				const response = JSON.parse(xhr.responseText);
				return response.success === true || response.success === 1;
			}

			// Fall back to XML parsing.
			const responseXML = xhr.responseXML;
			if (!responseXML) {
				return false;
			}

			const wpError = responseXML.querySelector('wp_error');
			if (wpError) {
				return false;
			}

			// For inline-save-tax, we need to check for success in the response.
			const successEl = responseXML.querySelector('success');
			const success = successEl ? successEl.textContent : null;

			return success === '1' || success === 'true';
		} catch (error) {
			return false;
		}
	};

	/**
	 * Handles Quick Edit AJAX completion.
	 *
	 * @since TBD
	 */
	obj.handleQuickEditAjaxComplete = (event, xhr, settings) => {
		try {
			// Only proceed if this is a taxonomy inline save.
			if (!settings.data || !settings.data.includes("action=inline-save-tax")) {
				return;
			}

			// Check if the AJAX request was successful.
			if (!obj.isAjaxSuccess(xhr)) {
				return;
			}

			// Clean up any existing color pickers.
			obj.cleanupColorPickers();

			// Safely create a temporary div for parsing the response.
			const tempDiv = document.createElement('div');
			if (!xhr.responseText) {
				return;
			}
			tempDiv.innerHTML = xhr.responseText;

			// Find the color preview span in the response.
			const colorPreview = tempDiv.querySelector( obj.selectors.tableColorPreview );
			if (!colorPreview) {
				return;
			}

			// Safely get the category class.
			const categoryClass = colorPreview.className
				.split(' ')
				.find(cls => cls && cls.startsWith('tribe_events_cat-'));

			if (!categoryClass) {
				return;
			}

			// Safely get color values with fallbacks.
			const colors = {
				primary: colorPreview.getAttribute('data-primary') || 'inherit',
				secondary: colorPreview.getAttribute('data-secondary') || 'inherit',
				text: colorPreview.getAttribute('data-text') || 'inherit',
			};

			// Validate color values are valid hex colors or 'inherit'.
			const isValidHex = (color) => /^#([0-9A-F]{3}){1,2}$/i.test(color);
			const sanitizedColors = {
				primary: isValidHex(colors.primary) ? colors.primary : 'inherit',
				secondary: isValidHex(colors.secondary) ? colors.secondary : 'inherit',
				text: isValidHex(colors.text) ? colors.text : 'inherit',
			};

			obj.updateInlineStyles(categoryClass, sanitizedColors);

		} catch (error) {
			// Silently handle any errors.
		}
	};

	/**
	 * Resets the form fields to their default state.
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $form The form element to reset.
	 */
	obj.resetForm = ($form) => {
		if (!obj.isAddPage || !$form || !$form.length) {
			return;
		}

		// Reset all color fields within the form.
		$form.find(obj.selectors.colorInput).each(function () {
			const $input = $(this);
			const $container = $input.closest(obj.selectors.wpPickerContainer);

			// Reset input value.
			$input.val('').change();

			// Manually reset the Iris picker.
			$input.wpColorPicker('color', false);

			// Reset WP Color Picker button styles.
			$container.find('.wp-color-result').css({
									'background-color': '',
									'border-color': '',
									});
			$container.find('.iris-picker').hide();
		});

		// Reset priority field to 0.
		$form.find(obj.selectors.priorityField).val(0);

		// Reset preview text to default.
		const $previewText = $form.find(obj.selectors.previewText);
		const defaultText = $previewText.data('default-text') || 'Example';
		$previewText.text(defaultText);
	};

	/**
	 * Initializes AJAX listeners.
	 *
	 * @since TBD
	 */
	obj.initAjaxListeners = () => {
		$document.ajaxComplete(function (event, xhr, settings) {
			if (settings.data && settings.data.includes("action=add-tag")) {
				const $form = $(obj.selectors.form);

				if (!$form.length) {
					return;
				}

				if (obj.isAjaxSuccess(xhr)) {
					obj.resetForm($form);
				}
			}
		});
	};

	/**
	 * Initializes event listeners.
	 *
	 * @since TBD
	 */
	obj.initEventListeners = () => {
		$document.on('input change', obj.selectors.tagName, (event) => {
			obj.updatePreviewText(event.target);
		});
	};

	/**
	 * Closes the color picker when clicking outside of it.
	 *
	 * @since TBD
	 */
	obj.closeColorPicker = () => {
		$document.on('click', (event) => {
			const $target = $(event.target);
			const isPickerElement = $target.closest(
				`${obj.selectors.colorInput}, 
				${obj.selectors.wpPickerContainer}, 
				${obj.selectors.irisPicker}`
			).length > 0;

			if (!isPickerElement) {
				obj.closeActivePicker();
			}
		});
	};

	/**
	 * Handles initialization when the document is ready.
	 *
	 * @since TBD
	 */
	obj.ready = () => {
		obj.initColorPicker();
		obj.reInitColorPickerOnQuickEdit();
		obj.monitorInputChange();
		obj.initEventListeners();
		obj.initAjaxListeners();
		obj.initQuickEditHandlers();
		obj.closeColorPicker();
	};

	$document.ready(obj.ready);

	// Only keep the single, minimal .wp-color-result click handler that closes all other pickers before letting WordPress open the clicked one
	$(document).on('click', '.wp-color-result', function (e) {
		const $currentInput = $(this).siblings('input.wp-color-picker');
		// Close all other pickers except the one for this input
		$('.iris-picker:visible').not(
			$currentInput.closest('.wp-picker-container').find('.iris-picker')
		).hide();
		// Let WordPress handle opening the picker for this input
	});

})(jQuery, tribe.events.admin.categoryColors);
