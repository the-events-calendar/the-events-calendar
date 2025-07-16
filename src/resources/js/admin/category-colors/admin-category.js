/**
 * Category Colors Admin JavaScript.
 *
 * Handles the color picker functionality for event categories in the WordPress admin.
 * This includes:
 * - Color picker initialization and management.
 * - Live preview updates.
 * - Quick edit integration.
 * - Color picker visibility control.
 *
 * @since 6.14.0
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};
tribe.events.admin.categoryColors = {};

/**
 * Initializes the script.
 *
 * @since 6.14.0
 *
 * @param {jQuery} $   jQuery instance.
 * @param {Object} obj The category colors object.
 */
(($, obj) => {
	'use strict';

	// === DOM Selectors ===
	const $document = $(document);
	const selectors = obj.selectors = {
		// Color input fields.
		colorInput: '.tec-events-category-colors__input.wp-color-picker',
		primaryColor: '[name="tec_events_category-color[primary]"]',
		backgroundColor: '[name="tec_events_category-color[secondary]"]',
		fontColor: '[name="tec_events_category-color[text]"]',
		// Preview element.
		previewText: '.tec-events-category-colors__preview-text',
		tableColorPreview: '.column-category_color .tec-events-taxonomy-table__category-color-preview',
		// Form elements.
		tagName: 'input[name="tag-name"], input[name="name"]',
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: $('#addtag').length ? '#addtag' : '#edittag',
		hideFromLegendField: '[name="tec_events_category-color[hide_from_legend]"]',
		// Quick edit elements.
		quickEditButton: '.editinline',
		quickEditRow: '.inline-edit-row',
		quickEditSave: '.inline-edit-save .save',
		quickEditCancel: '.inline-edit-save .cancel',
		// Color picker elements.
		colorContainer: '.tec-events-category-colors__container',
		wpPickerContainer: '.wp-picker-container',
		irisPicker: '.iris-picker',
		colorResult: '.wp-color-result',
		initializedClass: 'wp-color-picker-initialized'
	};

	// === Helper Functions ===

	/**
	 * Checks if a color picker is already initialized.
	 *
	 * @param {jQuery} $input The input element to check.
	 * @returns {boolean} Whether the picker is initialized.
	 */
	const isColorPickerInitialized = $input => $input.hasClass(selectors.initializedClass);

	/**
	 * Gets all color inputs within a scope.
	 *
	 * @param {jQuery} $scope The scope to search within.
	 * @returns {jQuery} Collection of color inputs.
	 */
	const getColorInputs = $scope => $scope.find(selectors.colorInput);

	/**
	 * Debounces a function call.
	 *
	 * @param {Function} fn The function to debounce.
	 * @param {number} delay The delay in milliseconds.
	 * @returns {Function} Debounced function.
	 */
	const debounce = (fn, delay) => {
		let timer = null;
		return function(...args) {
			clearTimeout(timer);
			timer = setTimeout(() => fn.apply(this, args), delay);
		};
	};

	// === Preview Update Functions ===

	/**
	 * Updates the preview text immediately.
	 *
	 * @param {HTMLElement} element The input element.
	 */
	const updatePreviewTextImmediate = element => {
		if (!element) return;
		const $tagInput = $(element);
		const $container = $tagInput.closest('.tec-events-category-colors__wrap, form, .inline-edit-row');
		const $previewText = $container.find(selectors.previewText);
		const defaultText = $previewText.data('default-text') || '';
		const tagValue = $tagInput.val().trim();
		$previewText.text(tagValue.length ? tagValue : defaultText);
	};

	// Debounced version of preview text update.
	const updatePreviewText = debounce(updatePreviewTextImmediate, 100);

	/**
	 * Updates the color preview for an input by applying preview styles.
	 *
	 * @param {jQuery} $input The color input element.
	 */
	const updateClosestPreview = $input => {
		if (!$input || $input.prop('disabled') || $input.prop('readonly')) return;
		const $container = $input.closest(selectors.colorContainer);
		const primaryColor = $container.find(selectors.primaryColor).val() || 'transparent';
		const backgroundColor = $container.find(selectors.backgroundColor).val() || 'transparent';
		const fontColor = $container.find(selectors.fontColor).val() || 'inherit';
		// Update preview styles.
		$container.find(selectors.previewText).css({
			'border-left': `5px solid ${primaryColor}`,
			'background-color': backgroundColor,
		});
		$container.find(selectors.previewText).css({ color: fontColor });
	};

	// === Color Picker Management ===

	/**
	 * Sets up a color picker on an input.
	 *
	 * @param {jQuery} $input The input element to initialize.
	 */
	const setupColorPicker = $input => {
		if ($input.prop('disabled') || $input.prop('readonly') || $input.hasClass(selectors.initializedClass)) {
			return;
		}
		// Initialize the color picker.
		$input.wpColorPicker({
			change: function () { updateClosestPreview($input); },
			clear: function () { updateClosestPreview($input); },
		});
		// Set initial color.
		$input.iris('color', $input.val());
	};

	// === Event Handlers ===

	/**
	 * Sets up all event handlers.
	 */
	const bindEvents = () => {
		// Live preview updates.
		$document.on('input', selectors.colorInput, function() {
			if ($(this).prop('disabled') || $(this).prop('readonly')) return;
			updateClosestPreview($(this));
		});

		// Tag name preview updates.
		$document.on('input change', selectors.tagName, function(e) {
			if ($(e.target).prop('disabled') || $(e.target).prop('readonly')) return;
			updatePreviewText(e.target);
		});

		// Quick edit initialization.
		$document.on('click', selectors.quickEditButton, function () {
			const $parentTr = $(this).closest('tr');
			const $preview = $parentTr.find(selectors.tableColorPreview);
			const colorValues = {
				primary: $preview.data('primary') || '',
				secondary: $preview.data('secondary') || '',
				text: $preview.data('text') || '',
			};

			// Initialize quick edit row after a short delay to ensure DOM is ready.
			setTimeout(() => {
				const $row = $(selectors.quickEditRow + ':visible');

				// Initialize each color input.
				['primary', 'secondary', 'text'].forEach(type => {
					const $input = $row.find(`[name="tec_events_category-color[${type}]"]`);
					if ($input.length) {
						$input.val(colorValues[type]).attr('value', colorValues[type]);
						// Initialize picker if needed.
						if (!$input.hasClass(selectors.initializedClass)) {
							setupColorPicker($input);
						}
						// Update Iris UI.
						if ($input.iris) {
							$input.iris('color', colorValues[type]);
						}
						// Update preview using helper.
						updateClosestPreview($input);
					}
				});

				// Update other fields.
				$row.find(selectors.priorityField).val($preview.data('priority') || '');
				$row.find(selectors.hideFromLegendField).prop('checked', !!$preview.data('hidden'));

				// Update tag name preview.
				const $tagInput = $row.find(selectors.tagName);
				if ($tagInput.length && !$tagInput.prop('disabled') && !$tagInput.prop('readonly')) {
					updatePreviewText($tagInput[0]);
				}
			}, 10);
		});

		// Clean up on quick edit cancel.
		$document.on('click', selectors.quickEditCancel, function() {
			const $quickEditRow = $(this).closest(selectors.quickEditRow);
			destroyColorPickers($quickEditRow);
		});
	};

	// === Initialization Methods ===

	/**
	 * Initializes color pickers in a scope.
	 *
	 * @param {jQuery} $scope The scope to initialize within.
	 */
	const initColorPicker = $scope => {
		getColorInputs($scope).filter(':visible').each(function() {
			setupColorPicker($(this));
		});
	};

	/**
	 * Initializes previews in a scope.
	 *
	 * @param {jQuery} $scope The scope to initialize within.
	 */
	const initializePreviews = $scope => {
		$scope.find(selectors.tagName).each(function() {
			if ($(this).prop('disabled') || $(this).prop('readonly')) return;
			updatePreviewTextImmediate(this);
		});
		getColorInputs($scope).each(function() {
			const $input = $(this);
			if ($input.prop('disabled') || $input.prop('readonly')) return;
			updateClosestPreview($input);
		});
	};

	/**
     * Main initialization function.
     */
    const ready = () => {
        // Delay the first init slightly to allow DOM and Iris to fully hook in.
        setTimeout(() => {
            const $body = $('body');
            initColorPicker($body);
            initializePreviews($body);
        }, 50);

        bindEvents();
    };

	// Initialize on document ready.
	$document.ready(ready);

	// === Quick Edit Integration ===

	/**
	 * Destroys color pickers in a scope by cloning and replacing inputs.
	 *
	 * WordPress does not support native destruction of color pickers, so we clone and replace the input as a workaround.
	 *
	 * @param {jQuery} $scope The scope to clean up.
	 */
	const destroyColorPickers = $scope => {
		$scope.find(selectors.colorInput).each(function () {
			const $input = $(this);
			if ($input.hasClass(selectors.initializedClass)) {
				// Clone and replace input to remove color picker instance (WP has no destroy method).
				const $clone = $input.clone();
				$input.closest(selectors.wpPickerContainer).replaceWith($clone);
			}
		});
	};

	// Override inline edit functionality.
	if (typeof inlineEditTax !== 'undefined') {
		const originalOpen = inlineEditTax.open;
		inlineEditTax.open = function(id) {
			// Clean up existing quick edit.
			destroyColorPickers(jQuery('#inline-edit'));

			// Call original open.
			originalOpen.apply(this, arguments);

			// Get the quick edit row reference once.
			const $quickEditRow = jQuery(selectors.quickEditRow);

			// Remove old quick edit rows.
			const $allQuickEditRows = jQuery('.inline-edit-row');
			const $currentQuickEditRow = $quickEditRow;
			$allQuickEditRows.not($currentQuickEditRow).each(function() {
				destroyColorPickers($(this));
				$(this).remove();
			});

			// Get the current row's data.
			const $parentTr = jQuery(`#tag-${id}`);
			const $preview = $parentTr.find(selectors.tableColorPreview);
			const colorValues = {
				primary: $preview.data('primary') || '',
				secondary: $preview.data('secondary') || '',
				text: $preview.data('text') || '',
			};

			// Initialize color inputs.
			['primary', 'secondary', 'text'].forEach(colorType => {
				const $oldInput = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
				const color = colorValues[colorType] || '';

				if ($oldInput.length && !$oldInput.prop('disabled') && !$oldInput.prop('readonly')) {
					// Replace with fresh input.
					const $newInput = $oldInput.clone().val(color);
					$oldInput.closest(selectors.wpPickerContainer).replaceWith($newInput);

					setupColorPicker($newInput);
					$newInput.iris('color', color);

					// Explicitly clear the picker if color is empty.
					if (!color) {
						$newInput.wpColorPicker('clear');
					}

					// Update swatch.
					const $swatch = $newInput.siblings(selectors.colorResult);
					$swatch.css('background-color', color || 'transparent');

					// Update preview using helper.
					requestAnimationFrame(() => updateClosestPreview($newInput));
				}
			});

			// Initialize other fields.
			$quickEditRow.find(selectors.priorityField).val($preview.data('priority') || '');
			$quickEditRow.find(selectors.hideFromLegendField).prop('checked', !!$preview.data('hidden'));

			// Update tag name preview.
			const $tagInput = $quickEditRow.find(selectors.tagName);
			if ($tagInput.length && !$tagInput.prop('disabled') && !$tagInput.prop('readonly')) {
				updatePreviewText($tagInput[0]);
			}
		};
	}

})(jQuery, tribe.events.admin.categoryColors);
