/**
 * Ensures the required levels exist in the Tribe Object
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.categoryColors = tribe.events.categoryColors || {};

/**
 * Configures Category Color Picker Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events.categoryColors.picker = (function() {
	'use strict';

	const obj = {};

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		picker: '.tec-category-color-picker'
		, dropdown: '.tec-category-color-picker__dropdown'
		, checkbox: '.tec-category-color-picker__checkbox'
		, dropdownIcon: '.tec-category-color-picker__dropdown-icon'
		, dropdownVisible: 'tec-category-color-picker__dropdown--visible'
		, resetButton: '.tec-category-color-picker__reset'
		, pickerOpen: 'tec-category-color-picker--open'
		, pickerAlignRight: 'tec-category-color-picker--align-right'
		, dropdownClose: '.tec-category-color-picker__dropdown-close'
		, events: [
			'.tribe-events-calendar-list__event'
			, '.tribe-events-calendar-day__event'
			, '.tribe-events-calendar-month__calendar-event'
			, '.tribe-events-pro-summary__event'
			, '.tribe-events-pro-photo__event'
			, '.tribe-events-pro-week-grid__event'
			, '.tribe-events-pro-week-grid__multiday-event'
			, '.tribe-events-calendar-month__multiday-event'
		]
		, filteredHide: 'tec-category-filtered-hide'
		, };

	/**
	 * Toggles the dropdown visibility
	 *
	 * @since TBD
	 *
	 * @param {Event} event - The click event
	 *
	 * @return {void}
	 */
	obj.toggleDropdown = event => {
		event.stopPropagation();

		const picker = event.currentTarget;
		const dropdown = document.querySelector(obj.selectors.dropdown);

		if (!dropdown || obj.isDropdownOpen(dropdown)) {
			return;
		}

		// Open dropdown.
		dropdown.classList.add(obj.selectors.dropdownVisible);
		picker.classList.add(obj.selectors.pickerOpen);

		// Adjust positioning.
		obj.adjustDropdownPosition(
			picker
			, dropdown
		);
	};

	/**
	 * Checks if the dropdown is already open.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} dropdown - The dropdown element
	 *
	 * @return {boolean}
	 */
	obj.isDropdownOpen = dropdown => dropdown.classList.contains(obj.selectors.dropdownVisible);

	/**
	 * Closes the dropdown when the close button is clicked
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.handleDropdownClose = () => {
		const picker = document.querySelector(obj.selectors.picker);
		const dropdown = document.querySelector(obj.selectors.dropdown);


		if (!picker || !dropdown) {
			return;
		}

		setTimeout(() => {
			dropdown.classList.remove(obj.selectors.dropdownVisible);
			picker.classList.remove(obj.selectors.pickerOpen);

		}, 100);
	};


	/**
	 * Adjusts dropdown position to prevent overflow.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} picker - The dropdown's parent container
	 * @param {HTMLElement} dropdown - The dropdown element
	 *
	 * @return {void}
	 */
	obj.adjustDropdownPosition = (picker, dropdown) => {
		const {
			right
		} = dropdown.getBoundingClientRect();
		const isOffScreen = right > window.innerWidth;

		picker.classList.toggle(
			obj.selectors.pickerAlignRight
			, isOffScreen
		);
	};


	/**
	 * Handles checkbox value changes and updates event visibility.
	 *
	 * @since TBD
	 *
	 * @param {Event|null} event - The change event, or null when resetting.
	 *
	 * @return {void}
	 */
	obj.handleCheckboxChange = event => {
		// If event is null (reset case), clear all selected categories.
		if (!event || !event.target) {
			obj.selectedCategories = obj.selectedCategories ?? new Set();
			obj.selectedCategories.clear();
		} else {
			const categorySlug = event.target.dataset.category;
			obj.selectedCategories = obj.selectedCategories ?? new Set();

			// Update the Set based on checkbox state.
			event.target.checked
				? obj.selectedCategories.add(categorySlug)
				: obj.selectedCategories.delete(categorySlug);
		}

		// Convert Set to an array for easier iteration.
		const selectedCategoriesArray = [...obj.selectedCategories];
		const events = document.querySelectorAll(obj.selectors.events.join(', '));

		events.forEach(event => {
			const hasMatch = obj.eventHasMatchingCategory(event, selectedCategoriesArray);

			// Apply filtering classes.
			event.classList.toggle(obj.selectors.filteredHide, selectedCategoriesArray.length > 0 && !hasMatch);
		});
	};

	/**
	 * Handles reset button click event.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.handleResetButtonClick = () => {
		const checkboxes = document.querySelectorAll(obj.selectors.checkbox);

		// Uncheck all checkboxes.
		checkboxes.forEach(checkbox => {
			checkbox.checked = false;
		});

		// Call handleCheckboxChange with no event to reset visibility.
		obj.handleCheckboxChange(null);
	};


	/**
	 * Checks if an event matches any selected categories.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} event - The event element.
	 * @param {Array} selectedCategories - Array of selected category slugs.
	 *
	 * @return {boolean} - True if the event matches a selected category.
	 */
	obj.eventHasMatchingCategory = (event, selectedCategories) => {
		const eventCategories = [...event.classList].filter(cls => cls.startsWith('tribe_events_cat-'));
		return selectedCategories.some(cat => eventCategories.includes(`tribe_events_cat-${ cat }`));
	};

	/**
	 * Hooks into XMLHttpRequest to detect AJAX completion.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.monitorTECAjax = function() {
		if (obj.ajaxHooked) {
			return;
		}
		obj.ajaxHooked = true;

		const originalOpen = XMLHttpRequest.prototype.open;

		XMLHttpRequest.prototype.open = function(method, url, ...args) {
			// Check if this is a TEC-related AJAX request.
			if (url.includes('/wp-json/tribe/views/v2/html')) {
				this.addEventListener(
					'load'
					, function() {
						if (this.readyState === 4 && this.status === 200) {
							obj.ensureBindings();
						}
					}
				);
			}

			// Call the original open method.
			return originalOpen.apply(
				this
				, [method, url, ...args]
			);
		};
	};


	/**
	 * Ensures event bindings persist after AJAX updates.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ensureBindings = function(retryCount = 0) {
		// Limit retries to avoid infinite loops.
		if (retryCount > 5) {
			return;
		}

		requestAnimationFrame(() => {
			const picker = document.querySelector(obj.selectors.picker);

			if (!picker) {
				setTimeout(
					() => obj.ensureBindings(retryCount + 1)
					, 50
				);
				return;
			}

			obj.cleanupBindings(); // Cleanup first to avoid duplicates.

			if (!picker.hasAttribute('data-bound')) {
				obj.bindEvents();
				// Prevent duplicate bindings.
				picker.setAttribute(
					'data-bound'
					, 'true'
				);
			}
		});
	};


	/**
	 * Removes old event bindings to prevent duplicate listeners.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.cleanupBindings = () => {
		const picker = document.querySelector(obj.selectors.picker);

		if (!picker || !obj.isBound(picker)) {
			return;
		}

		obj.unbindEvents();
		picker.removeAttribute(obj.selectors.dataBound);
	};

	/**
	 * Checks if the picker has already been bound.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement} element - The picker element.
	 *
	 * @return {boolean} - True if the picker is already bound.
	 */
	obj.isBound = element => element.hasAttribute(obj.selectors.dataBound);

	/**
	 * Binds events for the category color picker.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.bindEvents = () => {
		const picker = document.querySelector(obj.selectors.picker);
		const closeButton = document.querySelector(obj.selectors.dropdownClose);
		const resetButton = document.querySelector( obj.selectors.resetButton );

		obj.addEventListeners(
			picker
			, [{
				event: 'click'
				, handler: obj.toggleDropdown
			}]
		);

		obj.addEventListeners(
			document
			, [{
				event: 'click'
				, handler: obj.handleDropdownClose
			}]
		);

		document.addEventListener('change', event => {
			if (event.target.matches(obj.selectors.checkbox)) {
				obj.handleCheckboxChange(event);
			}
		});

		if (closeButton) {
			closeButton.addEventListener('click', obj.handleDropdownClose);
		}

		if (resetButton) {
			resetButton.addEventListener('click', obj.handleResetButtonClick);
		}
	};

	/**
	 * Adds multiple event listeners to an element.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement|Document} element - The target element.
	 * @param {Array} events - An array of event-handler pairs.
	 *
	 * @return {void}
	 */
	obj.addEventListeners = (element, events) => {
		if (!element) {
			return;
		}

		events.forEach(({
						   event
						   , handler
					   }) =>
						   element.addEventListener(
							   event
							   , handler
						   )
		);
	};

	/**
	 * Unbinds events for the category color picker.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.unbindEvents = () => {
		const picker = document.querySelector(obj.selectors.picker);
		const checkboxes = document.querySelectorAll(obj.selectors.checkbox);
		const closeButton = document.querySelector(obj.selectors.dropdownClose);

		obj.removeEventListeners(
			picker
			, [{
				event: 'click'
				, handler: obj.toggleDropdown
			}]
		);

		obj.removeEventListeners(
			document
			, [{
				event: 'click'
				, handler: obj.handleOutsideClick
			}]
		);

		checkboxes.forEach(checkbox =>
							   obj.removeEventListeners(
								   checkbox
								   , [{
									   event: 'change'
									   , handler: obj.handleCheckboxChange
								   }]
							   )
		);

		if (closeButton) {
			closeButton.removeEventListener('click', obj.handleDropdownClose);
		}
	};

	/**
	 * Removes multiple event listeners from an element.
	 *
	 * @since TBD
	 *
	 * @param {HTMLElement|Document} element - The target element.
	 * @param {Array} events - An array of event-handler pairs.
	 *
	 * @return {void}
	 */
	obj.removeEventListeners = (element, events) => {
		if (!element) {
			return;
		}

		events.forEach(({
						   event
						   , handler
					   }) =>
						   element.removeEventListener(
							   event
							   , handler
						   )
		);
	};

	/**
	 * Initializes the category color picker
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.init = function() {
		obj.monitorTECAjax();
		obj.bindEvents();
	};

	/**
	 * Handles initialization when the document is ready
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	document.addEventListener(
		'DOMContentLoaded'
		, obj.init
	);

	return obj;
})();
