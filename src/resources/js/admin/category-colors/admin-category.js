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

	// === Iris Picker Observer State ===
	let irisObserverInitialized = false;
	let irisObserver = null;

	// === Debounce Helper ===
	const debounce = (fn, delay) => {
		let timer = null;
		return function(...args) {
			clearTimeout(timer);
			timer = setTimeout(() => fn.apply(this, args), delay);
		};
	};

	// === UI Rendering ===
	const updatePreviewTextImmediate = element => {
		if (!element) return;
		const $tagInput = $(element);
		const $container = $tagInput.closest('.tec-events-category-colors__wrap, form, .inline-edit-row');
		const $previewText = $container.find(selectors.previewText);
		const defaultText = $previewText.data('default-text') || '';
		const tagValue = $tagInput.val().trim();
		$previewText.text(tagValue.length ? tagValue : defaultText);
	};
	const updatePreviewText = debounce(updatePreviewTextImmediate, 100);

	const updateClosestPreview = $input => {
		if (!$input || $input.prop('disabled') || $input.prop('readonly')) return;
		requestAnimationFrame(() => {
			const $container = $input.closest(selectors.colorContainer);
			const primaryColor = $container.find(selectors.primaryColor).val() || 'transparent';
			const backgroundColor = $container.find(selectors.backgroundColor).val() || 'transparent';
			const fontColor = $container.find(selectors.fontColor).val() || 'inherit';
			$container.find(selectors.preview).css({
				'border-left': `5px solid ${primaryColor}`,
				'background-color': backgroundColor,
			});
			$container.find(selectors.previewText).css({ color: fontColor });
		});
	};

	// === DRY: Centralized Color Picker Setup ===
	const setupColorPicker = $input => {
		if ($input.prop('disabled') || $input.prop('readonly')) return;
		if (isColorPickerInitialized($input)) return;
		$input.wpColorPicker({
			change: function() { updateClosestPreview($input); },
			clear: function() { updateClosestPreview($input); }
		});
	};

	// === Initialization Methods ===
	const initColorPicker = $scope => {
		getColorInputs($scope).filter(':visible').each(function() {
			setupColorPicker($(this));
		});
	};

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

	// === Quick Edit Rehydration ===
	const reInitQuickEditColorPickers = $quickEditRow => {
		['primary', 'secondary', 'text'].forEach(colorType => {
			const $input = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
			if (!$input.length) return;
			setupColorPicker($input);
			// Always update preview after setting value
			updateClosestPreview($input);
		});
		// Hide all pickers except the currently focused one (scoped to this row)
		const $focusedPicker = $quickEditRow.find(selectors.irisPicker+':visible');
		$quickEditRow.find(selectors.irisPicker+':visible').not($focusedPicker).hide();
	};

	// === Event Bindings ===
	const bindEvents = () => {
		// Live update preview on color input
		$document.on('input', selectors.colorInput, function() {
			if ($(this).prop('disabled') || $(this).prop('readonly')) return;
			updateClosestPreview($(this));
		});
		// Debounced update of preview text on tag name input
		$document.on('input change', selectors.tagName, function(e) {
			if ($(e.target).prop('disabled') || $(e.target).prop('readonly')) return;
			updatePreviewText(e.target);
		});
		// Ensure only one picker is open at a time when clicking swatch or input
		$document.on('mousedown', '.wp-color-result, .tec-events-category-colors__input.wp-color-picker', function (e) {
			const $container = $(this).closest('.wp-picker-container');
			$('.iris-picker:visible').not($container.find('.iris-picker')).hide();
		});
		// Quick Edit: re-initialize color pickers and update fields
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
				if ($input.length && !$input.prop('disabled') && !$input.prop('readonly')) {
					$input.val(colors[colorType]);
					// Always update preview after setting value
					updateClosestPreview($input);
				}
			});
			reInitQuickEditColorPickers($quickEditRow);
			observeIrisPickers($quickEditRow); // Ensure new pickers in this row are observed
			$quickEditRow.find(selectors.priorityField).val(data.priority);
			$quickEditRow.find(selectors.hideFromLegendField).prop('checked', !!data.hide_from_legend);
			const $tagInput = $quickEditRow.find(selectors.tagName);
			if ($tagInput.length && !$tagInput.prop('disabled') && !$tagInput.prop('readonly')) updatePreviewText($tagInput[0]);
		});
		// Hide all pickers when clicking outside any picker/input/swatch
		$document.on('mousedown', function(e) {
			const $target = $(e.target);
			const isInsidePicker = $target.closest('.iris-picker, .wp-color-result, .tec-events-category-colors__input.wp-color-picker, .wp-picker-container').length > 0;
			if (!isInsidePicker) {
				$('.iris-picker:visible').hide();
			}
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
	const observeIrisPickers = ($scope = $('body')) => {
		if (irisObserverInitialized) return;
		irisObserverInitialized = true;
		irisObserver = new MutationObserver(() => {
			// Only target iris pickers within TEC color picker containers
			const $allVisible = $scope.find('.tec-events-category-colors__wrap .iris-picker:visible, .inline-edit-row .iris-picker:visible');
			if ($allVisible.length > 1) {
				$allVisible.slice(0, -1).hide();
			}
		});
		// Only observe iris pickers within TEC containers
		$scope.find('.tec-events-category-colors__wrap .iris-picker, .inline-edit-row .iris-picker').each(function() {
			if (!$(this).data('tec-observed')) {
				irisObserver.observe(this, { attributes: true, attributeFilter: ['style'] });
				$(this).data('tec-observed', true);
			}
		});
	};

})(jQuery, tribe.events.admin.categoryColors);
