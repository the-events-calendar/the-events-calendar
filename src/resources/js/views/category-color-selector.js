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
			'.tribe-events-pro-week-mobile-events__event'
		],
		filteredHide: 'tec-category-filtered-hide',
	};

	// =============
	// State
	// =============
	let selectedCategories = new Set();
	let ajaxHooked = false;
	let observer = null;

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
		event.stopPropagation();
		const picker = event.currentTarget;
		const dropdown = qs( SELECTORS.dropdown );
		if ( !dropdown ) return;
		if ( isDropdownOpen( dropdown ) ) {
			closeDropdown( picker, dropdown );
		} else {
			openDropdown( picker, dropdown );
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
	 * Handles closing the dropdown only if the click is outside the dropdown and picker.
	 * @since TBD
	 * @param {Event} event
	 * @return {void}
	 */
	const handleDropdownClose = event => {
		const picker = qs( SELECTORS.picker );
		const dropdown = qs( SELECTORS.dropdown );
		if ( !picker || !dropdown ) return;
		const target = event.target;
		if (
			target === picker || picker.contains( target ) ||
			target === dropdown || dropdown.contains( target )
		) {
			return;
		}
		closeDropdown( picker, dropdown );
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
		persistSelectedCategories();
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
		persistSelectedCategories();
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
	// Filter Persistence Helpers
	// =====================

	/**
	 * Persist selected categories in sessionStorage.
	 * @since TBD
	 */
	const persistSelectedCategories = () => {
		try {
			sessionStorage.setItem( 'tec_category_color_selected', JSON.stringify( [ ...selectedCategories ] ) );
		} catch ( e ) {}
	};

	/**
	 * Restore selected categories from sessionStorage.
	 * @since TBD
	 */
	const restoreSelectedCategories = () => {
		try {
			const stored = sessionStorage.getItem( 'tec_category_color_selected' );
			if ( stored ) {
				selectedCategories = new Set( JSON.parse( stored ) );
			}
		} catch ( e ) {}
	};

	/**
	 * Re-check checkboxes and reapply filter classes after AJAX or DOM update.
	 * @since TBD
	 */
	const reapplyFilters = () => {
		// Re-check checkboxes
		qsa( SELECTORS.checkbox ).forEach( checkbox => {
			const cat = checkbox.dataset.category;
			checkbox.checked = selectedCategories.has( cat );
		} );
		updateEventVisibility();
	};

	// =====================
	// AJAX Monitoring
	// =====================

	/**
	 * Hooks into XMLHttpRequest to detect AJAX completion.
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
						ensureBindings();
						restoreSelectedCategories();
						reapplyFilters();
					}
				} );
			}
			return originalOpen.apply( this, [ method, url, ...args ] );
		};
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
			cleanupBindings();
			if ( !isBound( picker ) ) {
				bindEvents();
				picker.setAttribute( SELECTORS.dataBound, 'true' );
			}
		} );
	};

	/**
	 * Removes old event bindings to prevent duplicate listeners.
	 * @since TBD
	 * @return {void}
	 */
	const cleanupBindings = () => {
		const picker = qs( SELECTORS.picker );
		if ( !picker || !isBound( picker ) ) return;
		unbindEvents();
		picker.removeAttribute( SELECTORS.dataBound );
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
		const picker = qs( SELECTORS.picker );
		const closeButton = qs( SELECTORS.dropdownClose );
		const resetButton = qs( SELECTORS.resetButton );
		if ( picker ) {
			picker.addEventListener( 'click', toggleDropdown );
		}
		document.addEventListener( 'click', handleDropdownClose );
		// Event delegation for checkboxes
		const grid = qs( '.tec-events-category-color-filter__dropdown' );
		if ( grid ) {
			grid.addEventListener( 'change', handleCheckboxChange );
		}
		if ( closeButton ) {
			closeButton.addEventListener( 'click', event => {
				event.stopPropagation();
				const picker = qs( SELECTORS.picker );
				const dropdown = qs( SELECTORS.dropdown );
				if ( picker && dropdown ) closeDropdown( picker, dropdown );
			} );
		}
		if ( resetButton ) {
			resetButton.addEventListener( 'click', resetSelection );
		}
		// MutationObserver to clean up bindings if DOM changes
		if ( !observer ) {
			observer = new MutationObserver( cleanupBindings );
			observer.observe( document.body, { childList: true, subtree: true } );
		}
		window.addEventListener( 'beforeunload', cleanupBindings );
	};

	/**
	 * Unbinds events for the category color picker.
	 * @since TBD
	 * @return {void}
	 */
	const unbindEvents = () => {
		const picker = qs( SELECTORS.picker );
		const closeButton = qs( SELECTORS.dropdownClose );
		const resetButton = qs( SELECTORS.resetButton );
		const grid = qs( '.tec-events-category-color-filter__dropdown' );
		if ( picker ) {
			picker.removeEventListener( 'click', toggleDropdown );
		}
		document.removeEventListener( 'click', handleDropdownClose );
		if ( grid ) {
			grid.removeEventListener( 'change', handleCheckboxChange );
		}
		if ( closeButton ) {
			closeButton.removeEventListener( 'click', closeDropdown );
		}
		if ( resetButton ) {
			resetButton.removeEventListener( 'click', resetSelection );
		}
		if ( observer ) {
			observer.disconnect();
			observer = null;
		}
		window.removeEventListener( 'beforeunload', cleanupBindings );
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
		restoreSelectedCategories();
		reapplyFilters();
		bindEvents();
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
