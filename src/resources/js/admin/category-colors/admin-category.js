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
		hideFromLegendField: '[name="tec_events_category-color[hide_from_legend]"]'
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
		console.log('[TEC][setupColorPicker] Before: name=', $input.attr('name'), 'val=', $input.val(), 'attr value=', $input[0].getAttribute('value'), 'hasClass:', $input.hasClass('wp-color-picker-initialized'));
		if ($input.prop('disabled') || $input.prop('readonly') || $input.hasClass('wp-color-picker-initialized')) {
			console.log('[TEC][setupColorPicker] Skipping initialization for', $input.attr('name'));
			return;
		}
		$input.wpColorPicker({
			change: function () { updateClosestPreview($input); },
			clear: function () { updateClosestPreview($input); }
		});
		console.log('[TEC][setupColorPicker] After wpColorPicker: name=', $input.attr('name'), 'val=', $input.val(), 'attr value=', $input[0].getAttribute('value'), 'hasClass:', $input.hasClass('wp-color-picker-initialized'));
		$input.iris('color', $input.val());
		console.log('[TEC][setupColorPicker] After iris: name=', $input.attr('name'), 'val=', $input.val(), 'attr value=', $input[0].getAttribute('value'));
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

	// === Helper to reset and re-initialize a color input ===
	function resetColorInput($row, type, color) {
		console.log(`[TEC][resetColorInput] Called for type=${type}, color=${color}`);
		const $input = $row.find(`[name="tec_events_category-color[${type}]"]`);
		if (!$input.length || $input.prop('disabled') || $input.prop('readonly')) {
			console.log(`[TEC][resetColorInput] Skipping input for type ${type}: not found, disabled, or readonly.`);
			return;
		}
		console.log(`[TEC][resetColorInput] Input before: name=${$input.attr('name')}, val=${$input.val()}, attr value=${$input[0].getAttribute('value')}`);
		const $newInput = $input.clone().val(color).attr('value', color);
		console.log(`[TEC][resetColorInput] New input: name=${$newInput.attr('name')}, val=${$newInput.val()}, attr value=${$newInput[0].getAttribute('value')}`);
		$input.closest(selectors.wpPickerContainer).replaceWith($newInput);
		console.log(`[TEC][resetColorInput] Replaced input for type ${type}.`);
		setupColorPicker($newInput);
		console.log(`[TEC][resetColorInput] After setupColorPicker: val=${$newInput.val()}, attr value=${$newInput[0].getAttribute('value')}, hasClass=${$newInput.hasClass('wp-color-picker-initialized')}`);
		$newInput.iris('color', color);
		console.log(`[TEC][resetColorInput] After iris('color'): val=${$newInput.val()}, attr value=${$newInput[0].getAttribute('value')}`);
		$newInput.siblings('.wp-color-result').css('background-color', color || 'transparent');
		requestAnimationFrame(() => {
			console.log(`[TEC][resetColorInput] requestAnimationFrame updateClosestPreview for type ${type}`);
			updateClosestPreview($newInput);
		});
	}

	// === Helper to reset all color inputs in a row ===
	function refreshQuickEditColors($row, colorData) {
		['primary', 'secondary', 'text'].forEach(type => {
			resetColorInput($row, type, colorData[type] || '');
		});
	}

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
			const $allInputs = $(selectors.colorInput);
			const $currentInput = $(this).is(selectors.colorInput)
				? $(this)
				: $(this).closest('.wp-picker-container').find(selectors.colorInput);

			$allInputs.not($currentInput).each(function() {
				if ($(this).hasClass('wp-color-picker-initialized')) {
					$(this).wpColorPicker('close');
				}
			});
			// Let WordPress handle opening the current one
		});
		// Quick Edit: re-initialize color pickers and update fields
		$document.on('click', selectors.quickEditButton, function () {
			const $parentTr = $(this).closest('tr');
			console.log('[TEC][QuickEdit] Clicked. parentTr:', $parentTr.get(0));
			const $preview = $parentTr.find(selectors.tableColorPreview);
			console.log('[TEC][QuickEdit] Preview data:', {
				primary: $preview.data('primary'),
				secondary: $preview.data('secondary'),
				text: $preview.data('text'),
				priority: $preview.data('priority'),
				hidden: $preview.data('hidden')
			});
			const colors = {
				primary: $preview.data('primary') || '',
				secondary: $preview.data('secondary') || '',
				text: $preview.data('text') || ''
			};
			console.log('[TEC][QuickEdit] colors object:', colors);
			setTimeout(() => {
				const $row = $(selectors.quickEditRow + ':visible');
				console.log('[TEC][QuickEdit] setTimeout: quickEditRow:', $row.get(0));
				['primary', 'secondary', 'text'].forEach(type => {
					const $input = $row.find(`[name="tec_events_category-color[${type}]"]`);
					if ($input.length) {
						$input.val(colors[type]).attr('value', colors[type]);
						console.log(`[TEC][QuickEdit] Set input for type ${type}: val=${$input.val()}, attr value=${$input[0].getAttribute('value')}`);
						console.log('[TEC][QuickEdit] Input DOM:', $input[0].outerHTML);
						// Update preview for this input
						const $container = $input.closest(selectors.colorContainer);
						const primaryColor = $container.find(selectors.primaryColor).val() || 'transparent';
						const backgroundColor = $container.find(selectors.backgroundColor).val() || 'transparent';
						const fontColor = $container.find(selectors.fontColor).val() || 'inherit';
						$container.find(selectors.preview).css({
							'border-left': `5px solid ${primaryColor}`,
							'background-color': backgroundColor,
						});
						$container.find(selectors.previewText).css({ color: fontColor });
						console.log(`[TEC][QuickEdit] Updated preview for type ${type}: border-left=${primaryColor}, background=${backgroundColor}, color=${fontColor}`);
					}
				});
				$row.find(selectors.priorityField).val($preview.data('priority') || '');
				$row.find(selectors.hideFromLegendField).prop('checked', !!$preview.data('hidden'));
				const $tagInput = $row.find(selectors.tagName);
				if ($tagInput.length && !$tagInput.prop('disabled') && !$tagInput.prop('readonly')) {
					const defaultText = $row.find(selectors.previewText).data('default-text') || '';
					const value = $tagInput.val().trim();
					console.log('[TEC][QuickEdit] Setting preview text:', value || defaultText);
					$row.find(selectors.previewText).text(value || defaultText);
				}
			}, 10);
		});
		// Hide all pickers when clicking outside any picker/input/swatch
		$document.on('mousedown', function(e) {
			const $target = $(e.target);
			const isInsidePicker = $target.closest('.iris-picker, .wp-color-result, .tec-events-category-colors__input.wp-color-picker, .wp-picker-container').length > 0;
			if (!isInsidePicker) {
				$('.iris-picker:visible').hide();
			}
		});
		// On Quick Edit cancel, destroy color pickers in the row
		$document.on('click', selectors.quickEditCancel, function() {
			const $quickEditRow = $(this).closest(selectors.quickEditRow);
			destroyColorPickers($quickEditRow);
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

	// === Destroy Color Pickers in a Scope ===
	const destroyColorPickers = $scope => {
		console.log('[TEC][destroyColorPickers] Called for scope:', $scope.get(0));
		$scope.find(selectors.colorInput).each(function () {
			const $input = $(this);
			console.log('[TEC][destroyColorPickers] Input:', $input.attr('name'), 'value:', $input.val(), 'attr value:', $input[0].getAttribute('value'), 'hasClass:', $input.hasClass('wp-color-picker-initialized'));
			if ($input.hasClass('wp-color-picker-initialized')) {
				const $clone = $input.clone();
				console.log('[TEC][destroyColorPickers] Replacing input with clone:', $clone.attr('name'), 'clone value:', $clone.val(), 'clone attr value:', $clone[0].getAttribute('value'));
				$input.closest(selectors.wpPickerContainer).replaceWith($clone);
				console.log('[TEC][destroyColorPickers] Replacement done.');
			}
		});
	};

	if (typeof inlineEditTax !== 'undefined') {
		const originalOpen = inlineEditTax.open;
		inlineEditTax.open = function(id) {
			// Always clean up the hidden Quick Edit template before opening a new row
			destroyColorPickers(jQuery('#inline-edit'));
			// Call the original open method
			originalOpen.apply(this, arguments);

			// Remove any old Quick Edit rows except the current one
			const $allQuickEditRows = jQuery('.inline-edit-row');
			const $currentQuickEditRow = jQuery(selectors.quickEditRow);
			$allQuickEditRows.not($currentQuickEditRow).each(function() {
				destroyColorPickers($(this));
				$(this).remove();
			});

			// Now the Quick Edit row is in the DOM and ready
			const $quickEditRow = $currentQuickEditRow;
			// Re-initialize color pickers and previews
			['primary', 'secondary', 'text'].forEach(colorType => {
				console.log('[TEC] Re-initializing color picker for', colorType);
				const $quickEditRow = $(selectors.quickEditRow); // Always fetch current row
				const $oldInput = $quickEditRow.find(`[name="tec_events_category-color[${colorType}]"]`);
				const color = colors[colorType] || ''; // ← Ensure empty color is respected

				if ($oldInput.length && !$oldInput.prop('disabled') && !$oldInput.prop('readonly')) {
					console.log('[TEC] Forcing input reset for', colorType, $oldInput.get(0));

					// Replace input with a fresh clone to fully clear Iris state
					const $newInput = $oldInput.clone().val(color);
					$oldInput.closest('.wp-picker-container').replaceWith($newInput);

					setupColorPicker($newInput);
					$newInput.iris('color', color);

					// Force swatch manually (reset to transparent if empty)
					const $swatch = $newInput.siblings('.wp-color-result');
					$swatch.css('background-color', color || 'transparent');

					requestAnimationFrame(() => updateClosestPreview($newInput));
				}
			});

			reInitQuickEditColorPickers($quickEditRow);
			observeIrisPickers($quickEditRow);
			$quickEditRow.find(selectors.priorityField).val($quickEditRow.data('priority') || '');
			$quickEditRow.find(selectors.hideFromLegendField).prop('checked', !!$quickEditRow.data('hidden'));
			const $tagInput = $quickEditRow.find(selectors.tagName);
			if ($tagInput.length && !$tagInput.prop('disabled') && !$tagInput.prop('readonly')) updatePreviewText($tagInput[0]);
		};
	}

})(jQuery, tribe.events.admin.categoryColors);
