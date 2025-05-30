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

	// === DOM Selectors ===
	const $document = $(document);
	const selectors = obj.selectors = {
		colorInput: '.tec-events-category-colors__input.wp-color-picker',
		preview: '.tec-events-category-colors__preview-text',
		previewText: '.tec-events-category-colors__preview-text',
		tagName: 'input[name="tag-name"], input[name="name"]',
		priorityField: 'input[name="tec_events_category-color[priority]"]',
		form: $('#addtag').length ? '#addtag' : '#edittag',
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

	// === Helpers (ordered above usage) ===
	const isColorPickerInitialized = $input => $input.hasClass('wp-color-picker-initialized');
	const getColorInputs = $scope => $scope.find(selectors.colorInput);

	// === UI Rendering ===
	const updatePreviewText = element => {
		if (!element) return;
		const $tagInput = $(element);
		const $container = $tagInput.closest('form, .inline-edit-row');
		const $previewText = $container.find(selectors.previewText);
		const defaultText = $previewText.data('default-text') || '';
		const tagValue = $tagInput.val().trim();
		$previewText.text(tagValue.length ? tagValue : defaultText);
	};

	const updateClosestPreview = $input => {
		const $container = $input.closest(selectors.colorContainer);
		const primaryColor = $container.find(selectors.primaryColor).val() || 'transparent';
		const backgroundColor = $container.find(selectors.backgroundColor).val() || 'transparent';
		const fontColor = $container.find(selectors.fontColor).val() || 'inherit';
		$container.find(selectors.preview).css({
			'border-left': `5px solid ${primaryColor}`,
			'background-color': backgroundColor,
		});
		$container.find(selectors.previewText).css({ color: fontColor });
	};

	// === Initialization Methods ===
	const initColorPicker = $scope => {
		getColorInputs($scope).filter(':visible').each(function() {
			const $input = $(this);
			if (isColorPickerInitialized($input)) return;
			$input.wpColorPicker({
				change: function() { updateClosestPreview($input); },
				clear: function() { updateClosestPreview($input); }
			});
		});
	};

	const initializePreviews = $scope => {
		$scope.find(selectors.tagName).each(function() { updatePreviewText(this); });
		getColorInputs($scope).each(function() { updateClosestPreview($(this)); });
	};

	// === Quick Edit Rehydration ===
	const reInitQuickEditColorPickers = $quickEditRow => {
		['primary', 'secondary', 'text'].forEach(colorType => {
			const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
			if (!$input.length) return;
			if (!isColorPickerInitialized($input)) {
				$input.wpColorPicker({
					change: function() { updateClosestPreview($input); },
					clear: function() { updateClosestPreview($input); }
				});
				if (window.__TEC_DEV_MODE__) {
					console.debug('[TEC] Initialized color picker (Quick Edit):', $input.attr('name'));
				}
			}
			// Always update preview after setting value
			updateClosestPreview($input);
		});
		// Hide all pickers except the currently focused one
		const $focusedPicker = $quickEditRow.find(selectors.irisPicker+':visible');
		$quickEditRow.find(selectors.irisPicker+':visible').not($focusedPicker).hide();
	};

	// === Event Bindings ===
	const bindEvents = () => {
		$document.on('input', selectors.colorInput, function() { updateClosestPreview($(this)); });
		$document.on('input change', selectors.tagName, e => { updatePreviewText(e.target); });
		$document.on('click', '.wp-color-result, .tec-events-category-colors__input.wp-color-picker', function (e) {
			const $container = $(this).closest('.wp-picker-container');
			$('.iris-picker:visible').not($container.find('.iris-picker')).hide();
		});
		$document.on('click', selectors.quickEditButton, function() {
			const $quickEditRow = $(selectors.quickEditRow);
			const $parentTr = $(this).closest('tr');
			const $colorPreview = $parentTr.find(selectors.tableColorPreview);
			const colors = {
				primary: $colorPreview.data('primary') || '',
				secondary: $colorPreview.data('secondary') || '',
				text: $colorPreview.data('text') || '',
			};
			const data = {
				priority: $colorPreview.data('priority') || '',
				hide_from_legend: $colorPreview.data('hidden') || '',
			};
			['primary', 'secondary', 'text'].forEach(colorType => {
				const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
				if ($input.length) {
					$input.val(colors[colorType]);
					// Always update preview after setting value
					updateClosestPreview($input);
				}
			});
			reInitQuickEditColorPickers($quickEditRow);
			observeIrisPickers();
			$quickEditRow.find(selectors.priorityField).val(data.priority);
			$quickEditRow.find(selectors.hideFromLegendField).prop('checked', !!data.hide_from_legend);
			const $tagInput = $quickEditRow.find(selectors.tagName);
			if ($tagInput.length) updatePreviewText($tagInput[0]);
		});
	};

	// === Initialization ===
	const ready = () => {
		const $body = $('body');
		initColorPicker($body);
		initializePreviews($body);
		observeIrisPickers();
		bindEvents();
	};

	$document.ready(ready);

	// === Mutation Observer for Iris Pickers ===
	const observeIrisPickers = () => {
		const observer = new MutationObserver(() => {
			const $allVisible = $('.iris-picker:visible');
			if ($allVisible.length > 1) {
				// Only keep the last opened visible
				$allVisible.slice(0, -1).hide();
			}
		});
		$('.iris-picker').each(function() {
			// Prevent double-observing
			if (!$(this).data('tec-observed')) {
				observer.observe(this, { attributes: true, attributeFilter: ['style'] });
				$(this).data('tec-observed', true);
			}
		});
	};

})(jQuery, tribe.events.admin.categoryColors);
