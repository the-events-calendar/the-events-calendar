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
	 * Updates the preview text based on the closest tag name input.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} element The tag input element.
	 */
	obj.updatePreviewText = (element) => {
		if (!element) return;

		const $tagInput = $(element);
		const $container = $tagInput.closest('form');
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

		$container.find(obj.selectors.preview).css({
													   'border-left': `5px solid ${primaryColor}`,
													   'background-color': backgroundColor,
												   });

		$container.find(obj.selectors.previewText).css({
														   'color': fontColor,
													   });
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
	 * Handles changes from the WordPress color picker.
	 *
	 * @since TBD
	 */
	obj.colorPickerChange = function () {
		obj.updateClosestPreview($(this));
	};

	/**
	 * Initializes the WordPress Color Picker on visible inputs.
	 *
	 * @since TBD
	 */
	obj.initColorPicker = () => {
		$(obj.selectors.colorInput).filter(':visible').wpColorPicker({
																		 change: obj.colorPickerChange,
																		 clear: obj.colorPickerChange,
																	 });

		$(obj.selectors.colorInput).each(function () {
			obj.updateClosestPreview($(this));
		});

		$(obj.selectors.tagName).each(function () {
			obj.updatePreviewText(this);
		});
	};

	/**
	 * Handles Quick Edit interactions and reinitializes the color picker.
	 *
	 * @since TBD
	 */
	obj.reInitColorPickerOnQuickEdit = () => {
		$document.on('click', obj.selectors.quickEditButton, function () {
			obj.cleanupColorPickers();

			const $quickEditRow = $(obj.selectors.quickEditRow);
			if (!$quickEditRow.length) return;

			const $parentTr = $(this).closest('tr');
			const $colorPreview = $parentTr.find(obj.selectors.tableColorPreview);

			const colors = {
				primary: $colorPreview.data('primary') || '',
				secondary: $colorPreview.data('secondary') || '',
				text: $colorPreview.data('text') || '',
			};

			setTimeout(() => {
				['primary', 'secondary', 'text'].forEach(colorType => {
					const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
					if (!$input.length) return;

					$input.val(colors[colorType]).wpColorPicker({
																	change: obj.colorPickerChange,
																	clear: obj.colorPickerChange,
																});
					obj.updateClosestPreview($input);
				});

				const $tagInput = $quickEditRow.find(obj.selectors.tagName);
				if ($tagInput.length) {
					obj.updatePreviewText($tagInput[0]);
				}
			}, 25);
		});
	};

	/**
	 * Cleans up WP Color Pickers in Quick Edit.
	 *
	 * @since TBD
	 */
	obj.cleanupColorPickers = () => {
		$(obj.selectors.quickEditRow).find(obj.selectors.colorInput).each(function () {
			const $input = $(this);
			const $wrapper = $input.closest('.wp-picker-container');

			if ($wrapper.length) {
				const $clone = $input.clone().removeClass('wp-color-picker').removeAttr('style');
				$wrapper.before($clone);
				$wrapper.remove();
			}
		});
	};

	/**
	 * Handles Quick Edit close events.
	 *
	 * @since TBD
	 *
	 * @param {Event} event The event object.
	 */
	obj.handleQuickEditClose = (event) => {
		obj.cleanupColorPickers();
		event.stopImmediatePropagation();
	};

	/**
	 * Handles Quick Edit AJAX completion events.
	 *
	 * @since TBD
	 *
	 * @param {Event} event The event object.
	 * @param {XMLHttpRequest} xhr The XMLHttpRequest object.
	 * @param {Object} settings The settings object.
	 */
	obj.handleQuickEditAjaxComplete = (event, xhr, settings) => {
		if (settings.data?.includes("action=inline-save-tax")) {
			obj.cleanupColorPickers();
		}
	};

	/**
	 * Closes the color picker when clicking outside.
	 *
	 * @since TBD
	 */
	obj.closeColorPicker = () => {
		$document.on('click', (event) => {
			if (!$(event.target).closest(`${obj.selectors.colorInput}, .wp-picker-container, .iris-picker`).length) {
				$(obj.selectors.colorInput).closest('.wp-picker-container').find('.iris-picker').fadeOut();
			}
		});
	};

	/**
	 * Initializes all event listeners and components.
	 *
	 * @since TBD
	 */
	obj.ready = () => {
		obj.initColorPicker();
		obj.closeColorPicker();
		obj.reInitColorPickerOnQuickEdit();
		obj.monitorInputChange();

		$document.on('input change', obj.selectors.tagName, (event) => {
			obj.updatePreviewText(event.target);
		});

		if (obj.isAddPage) {
			$(obj.selectors.form).on('submit', obj.resetForm);
		}
	};

	$document.ready(obj.ready);

})(jQuery, tribe.events.admin.categoryColors);
