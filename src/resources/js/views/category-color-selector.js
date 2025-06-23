/**
 * Category Color Picker UI logic for The Events Calendar.
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.categoryColors = tribe.events.categoryColors || {};

/**
 * Category Color Picker module.
 *
 * @since TBD
 * @type {PlainObject}
 */
tribe.events.categoryColors.categoryPicker = ( function() {
	'use strict';

	// =====================
	// Constants & Selectors
	// =====================
	const SELECTORS = {
		picker: '.tec-events-category-color-filter',
		dropdown: '.tec-events-category-color-filter__dropdown',
		checkbox: '.tec-events-category-color-filter__checkbox',
		dropdownIcon: '.tec-events-category-color-filter__dropdown-icon',
		dropdownVisible: 'tec-events-category-color-filter__dropdown--visible',
		resetButton: '.tec-events-category-color-filter__reset',
		pickerOpen: 'tec-events-category-color-filter--open',
		pickerAlignRight: 'tec-events-category-color-filter--align-right',
		dropdownClose: '.tec-events-category-color-filter__dropdown-close',
		dataBound: 'data-bound',
		events: [
			'.tribe-events-calendar-list__event',
			'.tribe-events-calendar-day__event',
			'.tribe-events-calendar-month__calendar-event',
			'.tribe-events-pro-summary__event',
			'.tribe-events-pro-photo__event',
			'.tribe-events-pro-week-grid__event',
			'.tribe-events-pro-week-grid__multiday-event',
			'.tribe-events-calendar-month__multiday-event',
			'.tribe-events-calendar-month-mobile-events__mobile-event',
			'.tribe-events-pro-week-mobile-events__event',
			'.tribe-events-pro-map__event-card-wrapper',
		],
		filteredHide: 'tec-category-filtered-hide',
		colorCircle: 'tec-events-category-color-filter__color-circle',
		colorCircleDefault: 'tec-events-category-color-filter__color-circle--default',
		colorCircleDefaultN: n => `tec-events-category-color-filter__color-circle--default-${n}`,
		pickerContainer: '.tec-events-category-color-filter',
	};

	// =============
	// State
	// =============
	/**
	 * Set of selected category slugs.
	 * Maintains checkbox state during AJAX navigation.
	 */
	let selectedCategories = new Set();
	let ajaxHooked = false;
	let observer = null;
	let eventsBound = false;

	/**
	 * Default number of color bubbles to show in the legend.
	 *
	 * @since TBD
	 * @type {number}
	 */
	const DEFAULT_BUBBLE_COUNT = 5;

	// =============
	// Utilities
	// =============

	/**
	 * Returns the first element matching the selector.
	 * @since TBD
	 * @param {string} selector
	 * @return {HTMLElement|null}
	 */
	const qs = selector => document.querySelector( selector );

	/**
	 * Returns all elements matching the selector.
	 * @since TBD
	 * @param {string} selector
	 * @return {NodeListOf<HTMLElement>}
	 */
	const qsa = selector => document.querySelectorAll( selector );

	/**
	 * Returns all event elements.
	 * @since TBD
	 * @return {NodeListOf<HTMLElement>}
	 */
	const getEventElements = () => qsa( SELECTORS.events.join( ', ' ) );

	// =====================
	// Dropdown Handling
	// =====================

	/**
	 * Toggles the dropdown visibility. If open, close it; if closed, open and adjust position.
	 * @since TBD
	 * @param {Event} event
	 * @return {void}
	 */
	const toggleDropdown = event => {
		// Prevent toggling if the click is inside the dropdown (e.g., on a checkbox)
		const dropdown = qs(SELECTORS.dropdown);
		if (dropdown && dropdown.contains(event.target)) {
			return;
		}
		event.stopPropagation();
		const picker = event.currentTarget;
		if (!dropdown) return;
		if (isDropdownOpen(dropdown)) {
			closeDropdown(picker, dropdown);
		} else {
			openDropdown(picker, dropdown);
		}
	};

	/**
	 * Opens the dropdown and adjusts its position.
	 * @since TBD
	 * @param {HTMLElement} picker
	 * @param {HTMLElement} dropdown
	 * @return {void}
	 */
	const openDropdown = ( picker, dropdown ) => {
		dropdown.classList.add( SELECTORS.dropdownVisible );
		picker.classList.add( SELECTORS.pickerOpen );
		adjustDropdownPosition( picker, dropdown );
	};

	/**
	 * Closes the dropdown.
	 * @since TBD
	 * @param {HTMLElement} picker
	 * @param {HTMLElement} dropdown
	 * @return {void}
	 */
	const closeDropdown = ( picker, dropdown ) => {
		dropdown.classList.remove( SELECTORS.dropdownVisible );
		picker.classList.remove( SELECTORS.pickerOpen );
	};

	/**
	 * Checks if the dropdown is open.
	 * @since TBD
	 * @param {HTMLElement} dropdown
	 * @return {boolean}
	 */
	const isDropdownOpen = dropdown => dropdown.classList.contains( SELECTORS.dropdownVisible );

	/**
	 * Handles closing the dropdown only if the click is outside the picker container.
	 * @since TBD
	 * @param {Event} event
	 * @return {void}
	 */
	const handleDropdownClose = event => {
		const clickedInsideAnyPicker = event.target.closest(SELECTORS.pickerContainer);
		if (clickedInsideAnyPicker) {
			return;
		}
		// Close *all* dropdowns
		qsa(SELECTORS.picker).forEach(picker => {
			const dropdown = picker.querySelector(SELECTORS.dropdown);
			if (dropdown && isDropdownOpen(dropdown)) {
				closeDropdown(picker, dropdown);
			}
		});
	};

	/**
	 * Adjusts dropdown position to prevent overflow.
	 * @since TBD
	 * @param {HTMLElement} picker
	 * @param {HTMLElement} dropdown
	 * @return {void}
	 */
	const adjustDropdownPosition = ( picker, dropdown ) => {
		const rect = dropdown.getBoundingClientRect();
		const isOffScreen = rect.right > window.innerWidth;
		picker.classList.toggle( SELECTORS.pickerAlignRight, isOffScreen );
	};

	// =====================
	// Checkbox Handling & Filter Persistence
	// =====================

	/**
	 * Renders the selected category color legend bubbles.
	 * @since TBD
	 */
	const renderLegend = () => {
		const legendContainer = document.getElementById('tec-category-color-legend');
		if (!legendContainer) return;
		legendContainer.innerHTML = '';

		// If categories are selected, show only those (up to 5)
		if (selectedCategories.size > 0) {
			const selected = Array.from(selectedCategories).slice(0, DEFAULT_BUBBLE_COUNT);
			
			selected.forEach(slug => {
				const span = document.createElement('span');
				span.classList.add(SELECTORS.colorCircle, `tribe_events_cat-${slug}`);
				legendContainer.appendChild(span);
			});
		} else {
			// Get the first 5 checkboxes from the dropdown
			const checkboxes = qsa(SELECTORS.checkbox);
			const firstFiveCheckboxes = Array.from(checkboxes).slice(0, DEFAULT_BUBBLE_COUNT);

			// Render category bubbles from checkboxes
			firstFiveCheckboxes.forEach(checkbox => {
				const categorySlug = checkbox.dataset.category;
				if (categorySlug) {
					const span = document.createElement('span');
					span.classList.add(SELECTORS.colorCircle, `tribe_events_cat-${categorySlug}`);
					legendContainer.appendChild(span);
				}
			});
		}
	};

	/**
	 * Resets all checkboxes and clears selected categories.
	 * @since TBD
	 * @return {void}
	 */
	const resetSelection = () => {
		qsa( SELECTORS.checkbox ).forEach( checkbox => {
			checkbox.checked = false;
		} );
		selectedCategories.clear();
		updateEventVisibility();
		renderLegend();
	};

	/**
	 * Handles checkbox value changes and updates event visibility.
	 * @since TBD
	 * @param {Event} event
	 * @return {void}
	 */
	const handleCheckboxChange = event => {
		const checkbox = event.target.closest( SELECTORS.checkbox );
		if ( !checkbox ) return;
		const categorySlug = checkbox.dataset.category;
		if ( checkbox.checked ) {
			selectedCategories.add( categorySlug );
		} else {
			selectedCategories.delete( categorySlug );
		}
		updateEventVisibility();
		renderLegend();
	};

	/**
	 * Updates event visibility based on selected categories.
	 * @since TBD
	 * @return {void}
	 */
	const updateEventVisibility = () => {
		const selectedArray = [ ...selectedCategories ];
		getEventElements().forEach( eventEl => {
			const hasMatch = eventHasMatchingCategory( eventEl, selectedArray );
			eventEl.classList.toggle( SELECTORS.filteredHide, selectedArray.length > 0 && !hasMatch );
		} );
	};

	/**
	 * Checks if an event matches any selected categories.
	 * @since TBD
	 * @param {HTMLElement} eventEl
	 * @param {Array} selectedCategoriesArr
	 * @return {boolean}
	 */
	const eventHasMatchingCategory = ( eventEl, selectedCategoriesArr ) => {
		const eventCategories = [ ...eventEl.classList ].filter( cls => cls.startsWith( 'tribe_events_cat-' ) );
		return selectedCategoriesArr.some( cat => eventCategories.includes( `tribe_events_cat-${ cat }` ) );
	};

	// =====================
	// AJAX Monitoring
	// =====================

	/**
	 * Hooks into XMLHttpRequest to detect AJAX completion.
	 * Ensures category selections persist across AJAX navigation.
	 * @since TBD
	 * @return {void}
	 */
	const monitorTECAjax = () => {
		if ( ajaxHooked ) return;
		ajaxHooked = true;
		const originalOpen = XMLHttpRequest.prototype.open;
		XMLHttpRequest.prototype.open = function( method, url, ...args ) {
			if ( url.includes( '/wp-json/tribe/views/v2/html' ) ) {
				this.addEventListener( 'load', function() {
					if ( this.readyState === 4 && this.status === 200 ) {
						try {
							// Ensure DOM is ready before re-applying filters
							setTimeout( () => {
								ensureBindings();
								reapplyFilters();
								renderLegend();
							}, 50 );
						} catch ( error ) {
							// Attempt recovery
							ensureBindings();
						}
					}
				} );
			}
			return originalOpen.apply( this, [ method, url, ...args ] );
		};
	};

	/**
	 * Re-check checkboxes and reapply filter classes after AJAX or DOM update.
	 * @since TBD
	 */
	const reapplyFilters = () => {
		// Re-check checkboxes
		qsa( SELECTORS.checkbox ).forEach( checkbox => {
			const cat = checkbox.dataset.category;
			if ( cat ) {
				checkbox.checked = selectedCategories.has( cat );
			}
		} );
		updateEventVisibility();
		renderLegend();
	};

	// =====================
	// Event Binding
	// =====================

	/**
	 * Ensures event bindings persist after AJAX updates.
	 * @since TBD
	 * @param {number} retryCount
	 * @return {void}
	 */
	const ensureBindings = ( retryCount = 0 ) => {
		if ( retryCount > 5 ) return;
		requestAnimationFrame( () => {
			const picker = qs( SELECTORS.picker );
			if ( !picker ) {
				setTimeout( () => ensureBindings( retryCount + 1 ), 50 );
				return;
			}
			if ( !eventsBound ) {
				bindEvents();
			}
			picker.setAttribute( SELECTORS.dataBound, 'true' );
		} );
	};

	/**
	 * Checks if the picker has already been bound.
	 * @since TBD
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	const isBound = element => element.hasAttribute( SELECTORS.dataBound );

	/**
	 * Binds events for the category color picker.
	 * @since TBD
	 * @return {void}
	 */
	const bindEvents = () => {
		if (eventsBound) return; // Prevent duplicate bindings
		eventsBound = true;

		const picker = qs(SELECTORS.picker);
		const closeButton = qs(SELECTORS.dropdownClose);
		const resetButton = qs(SELECTORS.resetButton);
		const grid = qs(SELECTORS.dropdown);

		if (picker) {
			picker.addEventListener('click', toggleDropdown);
		}
		if (grid) {
			grid.addEventListener('change', handleCheckboxChange);
		}
		if (closeButton) {
			closeButton.addEventListener('click', event => {
				event.stopPropagation();
				const picker = qs(SELECTORS.picker);
				const dropdown = qs(SELECTORS.dropdown);
				if (picker && dropdown) closeDropdown(picker, dropdown);
			});
		}
		if (resetButton) {
			resetButton.addEventListener('click', resetSelection);
		}

		document.addEventListener('click', handleDropdownClose);
		window.addEventListener('beforeunload', () => {
			eventsBound = false;
		});
	};

	/**
	 * Unbinds events for the category color picker.
	 * @since TBD
	 * @return {void}
	 */
	const unbindEvents = () => {
		// No-op, we don't need to remove listeners due to early return
		eventsBound = false;
	};

	// =====================
	// Initialization
	// =====================

	/**
	 * Initializes the category color picker.
	 * @since TBD
	 * @return {void}
	 */
	const init = () => {
		monitorTECAjax();
		bindEvents();
		renderLegend();
	};

	document.addEventListener( 'DOMContentLoaded', init );

	// =============
	// Export (public API only)
	// =============

	return {
		init,
		ensureBindings,
	};
} )();
