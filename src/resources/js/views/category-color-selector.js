/**
 * Category Color Picker UI logic for The Events Calendar.
 *
 * @since TBD
 *
 * @type {Object}
 */
tribe.events = tribe.events || {};
tribe.events.categoryColors = tribe.events.categoryColors || {};

/**
 * Category Color Picker module.
 *
 * @since TBD
 * @type {Object}
 */
tribe.events.categoryColors.categoryPicker = ( function () {
	'use strict';

	// =====================
	// Constants & Selectors
	// =====================
	const SELECTORS = {
		picker: '.tec-events-category-color-filter',
		dropdown: '.tec-events-category-color-filter__dropdown',
		dropdownLabel: '.tec-events-category-color-filter__dropdown-item label',
		checkbox: '.tec-events-category-color-filter__checkbox',
		dropdownIcon: '.tec-events-category-color-filter__dropdown-icon',
		dropdownVisible: 'tec-events-category-color-filter__dropdown--visible',
		resetButton: '.tec-events-category-color-filter__reset',
		pickerOpen: 'tec-events-category-color-filter--open',
		pickerAlignRight: 'tec-events-category-color-filter--align-right',
		dropdownClose: '.tec-events-category-color-filter__dropdown-close',
		dataBound: 'data-bound',
		childParentPairs: [
			{
				child: '.tribe-events-calendar-list__event',
				parent: '.tribe-events-calendar-list__event-row',
			},
			{
				child: '.tribe-events-calendar-day__event',
				parent: '.tribe-events-calendar-day__event',
			},
			{
				child: '.tribe-events-calendar-month__calendar-event',
				parent: '.tribe-events-calendar-month__calendar-event',
			},
			{
				child: '.tribe-events-pro-summary__event',
				parent: '.tribe-events-pro-summary__event',
			},
			{
				child: '.tribe-events-pro-photo__event',
				parent: '.tribe-events-pro-photo__event',
			},
			{
				child: '.tribe-events-pro-week-grid__event',
				parent: '.tribe-events-pro-week-grid__event',
			},
			{
				child: '.tribe-events-pro-week-grid__multiday-event',
				parent: '.tribe-events-pro-week-grid__multiday-event-wrapper',
			},
			{
				child: '.tribe-events-calendar-month__multiday-event',
				parent: '.tribe-events-calendar-month__multiday-event',
			},
			{
				child: '.tribe-events-calendar-month-mobile-events__mobile-event',
				parent: '.tribe-events-calendar-month-mobile-events__mobile-event-row',
			},
			{
				child: '.tribe-events-pro-week-mobile-events__event',
				parent: '.tribe-events-pro-week-mobile-events__event-row',
			},
			{
				child: '.tribe-events-pro-map__event-card-wrapper',
				parent: '.tribe-events-pro-map__event-card-row',
			},
		],
		filteredHide: 'tec-category-filtered-hide',
		colorCircle: 'tec-events-category-color-filter__color-circle',
		colorCircleDefault: 'tec-events-category-color-filter__color-circle--default',
		colorCircleDefaultN: ( n ) => `tec-events-category-color-filter__color-circle--default-${ n }`,
		pickerContainer: '.tec-events-category-color-filter',
	};

	// =============
	// State
	// =============
	/**
	 * Set of selected category slugs.
	 * Maintains checkbox state during AJAX navigation.
	 */
	const selectedCategories = new Set();
	let ajaxHooked = false;
	let observer = null;

	const DEFAULT_BUBBLE_COUNT = 5;

	// =============
	// Utilities
	// =============

	/**
	 * Returns the first element matching the selector.
	 * @since TBD
	 * @param {string} selector The CSS selector to query.
	 * @return {HTMLElement|null} The first matching element or null.
	 */
	const qs = ( selector ) => document.querySelector( selector );

	/**
	 * Returns all elements matching the selector.
	 * @since TBD
	 * @param {string} selector The CSS selector to query.
	 * @return {NodeList} All matching elements.
	 */
	const qsa = ( selector ) => document.querySelectorAll( selector );

	/**
	 * Returns all event parent elements matching the parent selectors in childParentPairs.
	 * @since TBD
	 * @return {HTMLElement[]} Array of parent elements.
	 */
	const getEventParentElements = () =>
		SELECTORS.childParentPairs.flatMap( ( pair ) => [ ...document.querySelectorAll( pair.parent ) ] );

	// =====================
	// Dropdown Handling
	// =====================

	/**
	 * Toggles the dropdown visibility. If open, close it; if closed, open and adjust position.
	 * @since TBD
	 * @param {Event} event The click event.
	 * @return {void}
	 */
	const toggleDropdown = ( event ) => {
		// Prevent toggling if the click is inside the dropdown (e.g., on a checkbox)
		const dropdown = qs( SELECTORS.dropdown );
		if ( dropdown && dropdown.contains( event.target ) ) {
			return;
		}
		event.stopPropagation();
		const picker = event.currentTarget;
		if ( ! dropdown ) {
			return;
		}
		if ( isDropdownOpen( dropdown ) ) {
			closeDropdown( picker, dropdown );
		} else {
			openDropdown( picker, dropdown );
		}
	};

	/**
	 * Opens the dropdown and adjusts its position.
	 * @since TBD
	 * @param {HTMLElement} picker   The picker element.
	 * @param {HTMLElement} dropdown The dropdown element.
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
	 * @param {HTMLElement} picker   The picker element.
	 * @param {HTMLElement} dropdown The dropdown element.
	 * @return {void}
	 */
	const closeDropdown = ( picker, dropdown ) => {
		dropdown.classList.remove( SELECTORS.dropdownVisible );
		picker.classList.remove( SELECTORS.pickerOpen );
	};

	/**
	 * Checks if the dropdown is open.
	 * @since TBD
	 * @param {HTMLElement} dropdown The dropdown element to check.
	 * @return {boolean} True if dropdown is open, false otherwise.
	 */
	const isDropdownOpen = ( dropdown ) => dropdown.classList.contains( SELECTORS.dropdownVisible );

	/**
	 * Handles closing the dropdown only if the click is outside the picker container.
	 * @since TBD
	 * @param {Event} event The click event.
	 * @return {void}
	 */
	const handleDropdownClose = ( event ) => {
		const clickedInsideAnyPicker = event.target.closest( SELECTORS.pickerContainer );
		if ( clickedInsideAnyPicker ) {
			return;
		}
		// Close *all* dropdowns
		qsa( SELECTORS.picker ).forEach( ( picker ) => {
			const dropdown = picker.querySelector( SELECTORS.dropdown );
			if ( dropdown && isDropdownOpen( dropdown ) ) {
				closeDropdown( picker, dropdown );
			}
		} );
	};

	/**
	 * Adjusts dropdown position to prevent overflow and ensures it stays within the viewport.
	 * Anchors to the left or right of the picker depending on screen position, and retries once if needed.
	 *
	 * @since TBD
	 * @param {HTMLElement} picker        The picker element that triggers the dropdown.
	 * @param {HTMLElement} dropdown      The dropdown element to position.
	 * @param {boolean}     [retry=false] Whether this is a retry attempt.
	 * @return {void}
	 */
	const adjustDropdownPosition = ( picker, dropdown, retry = false ) => {
		if ( ! dropdown.isConnected || ! dropdown.offsetParent ) {
			return;
		}

		// Ensure picker is positioned relative for absolute dropdown anchoring
		if ( window.getComputedStyle( picker ).position === 'static' ) {
			picker.style.position = 'relative';
		}

		// Reset dropdown styles
		Object.assign( dropdown.style, {
			left: '',
			right: '',
			top: '',
			position: 'absolute',
		} );

		const { left } = picker.getBoundingClientRect();
		const viewWidth = window.innerWidth;
		const verticalOffset = picker.offsetHeight;

		// Anchor based on proximity to screen edge
		if ( left > viewWidth / 2 ) {
			dropdown.style.right = '0px';
		} else {
			dropdown.style.left = '0px';
		}

		dropdown.style.top = `${ verticalOffset }px`;

		// Prevent vertical overflow
		const dropdownBottom = dropdown.getBoundingClientRect().bottom;
		const innerPadding = -8;
		const maxBottom = window.innerHeight - innerPadding;
		if ( dropdownBottom > maxBottom ) {
			const adjustment = dropdownBottom - maxBottom;
			const newTop = verticalOffset - adjustment;
			dropdown.style.top = `${ newTop }px`;
		}

		// Retry once if not visible (e.g. due to transition or layout shift)
		if ( ! retry && ! isFullyVisible( dropdown ) ) {
			window.requestAnimationFrame( () => adjustDropdownPosition( picker, dropdown, true ) );
		}
	};

	/**
	 * Checks if an element is fully visible within the viewport, considering padding.
	 *
	 * @since TBD
	 * @param {HTMLElement} el The element to check.
	 * @return {boolean} True if fully visible, false otherwise.
	 */
	const isFullyVisible = ( el ) => {
		const rect = el.getBoundingClientRect();
		const pad = 8;
		return (
			rect.top >= pad &&
			rect.left >= pad &&
			rect.bottom <= window.innerHeight - pad &&
			rect.right <= window.innerWidth - pad
		);
	};

	// =====================
	// Checkbox Handling & Filter Persistence
	// =====================

	/**
	 * Renders the selected category color legend bubbles.
	 * @since TBD
	 * @return {void}
	 */
	const renderLegend = () => {
		const legendContainer = document.getElementById( 'tec-category-color-legend' );
		if ( ! legendContainer ) {
			return;
		}
		legendContainer.innerHTML = '';

		// If categories are selected, show only those (up to 5)
		if ( selectedCategories.size > 0 ) {
			const selected = Array.from( selectedCategories ).slice( 0, DEFAULT_BUBBLE_COUNT );

			selected.forEach( ( slug ) => {
				const span = document.createElement( 'span' );
				span.classList.add( SELECTORS.colorCircle, `tribe_events_cat-${ slug }` );
				legendContainer.appendChild( span );
			} );
		} else {
			// Get the first 5 checkboxes from the dropdown
			const labels = qsa( SELECTORS.dropdownLabel );
			const firstFiveLabels = Array.from( labels ).slice( 0, DEFAULT_BUBBLE_COUNT );

			// Render category bubbles from labels.
			firstFiveLabels.forEach( ( checkbox ) => {
				const categorySlug = checkbox.dataset.category;
				if ( categorySlug ) {
					const span = document.createElement( 'span' );
					span.classList.add( SELECTORS.colorCircle, `tribe_events_cat-${ categorySlug }` );
					legendContainer.appendChild( span );
				}
			} );
		}
	};

	/**
	 * Resets all checkboxes and clears selected categories.
	 * @since TBD
	 * @return {void}
	 */
	const resetSelection = () => {
		qsa( SELECTORS.checkbox ).forEach( ( checkbox ) => {
			checkbox.checked = false;
		} );
		selectedCategories.clear();
		updateEventVisibility();
		renderLegend();
	};

	/**
	 * Handles checkbox value changes and updates event visibility.
	 * @since TBD
	 * @param {Event} event The change event.
	 * @return {void}
	 */
	const handleCheckboxChange = ( event ) => {
		const checkbox = event.target.closest( SELECTORS.checkbox );
		if ( ! checkbox ) {
			return;
		}
		const label = checkbox.closest( 'label' );
		if ( ! label ) {
			return;
		}
		const categorySlug = label.dataset.category;
		if ( ! categorySlug ) {
			return;
		}

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

		getEventParentElements().forEach( ( eventContainer ) => {
			let categoryElement = eventContainer;

			// If the parent doesn't have a category class, check children
			if ( ! [ ...categoryElement.classList ].some( ( cls ) => cls.startsWith( 'tribe_events_cat-' ) ) ) {
				categoryElement = eventContainer.querySelector( '[class*="tribe_events_cat-"]' );
			}

			const hasMatch = categoryElement ? eventHasMatchingCategory( categoryElement, selectedArray ) : false;

			eventContainer.classList.toggle( SELECTORS.filteredHide, selectedArray.length > 0 && ! hasMatch );
		} );
	};

	/**
	 * Checks if an event matches any selected categories.
	 * @since TBD
	 * @param {HTMLElement} eventEl               The event element to check.
	 * @param {Array}       selectedCategoriesArr Array of selected category slugs.
	 * @return {boolean} True if event matches any selected category, false otherwise.
	 */
	const eventHasMatchingCategory = ( eventEl, selectedCategoriesArr ) => {
		const eventCategories = [ ...eventEl.classList ].filter( ( cls ) => cls.startsWith( 'tribe_events_cat-' ) );
		return selectedCategoriesArr.some( ( cat ) => eventCategories.includes( `tribe_events_cat-${ cat }` ) );
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
		if ( ajaxHooked ) {
			return;
		}
		ajaxHooked = true;
		const originalOpen = window.XMLHttpRequest.prototype.open;
		window.XMLHttpRequest.prototype.open = function ( method, url, ...args ) {
			if ( url.includes( '/wp-json/tribe/views/v2/html' ) ) {
				this.addEventListener( 'load', function () {
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
	 * @return {void}
	 */
	const reapplyFilters = () => {
		// Re-check checkboxes to match selectedCategories
		qsa( SELECTORS.checkbox ).forEach( ( checkbox ) => {
			const label = checkbox.closest( 'label' );
			const cat = label?.dataset.category;
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
	 * @param {number} retryCount The number of retry attempts.
	 * @return {void}
	 */
	const ensureBindings = ( retryCount = 0 ) => {
		if ( retryCount > 5 ) {
			return;
		}
		window.requestAnimationFrame( () => {
			const picker = qs( SELECTORS.picker );
			if ( ! picker ) {
				setTimeout( () => ensureBindings( retryCount + 1 ), 50 );
				return;
			}
			cleanupBindings();
			if ( ! isBound( picker ) ) {
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
		if ( ! picker || ! isBound( picker ) ) {
			return;
		}
		picker.removeAttribute( SELECTORS.dataBound );
	};

	/**
	 * Checks if the picker has already been bound.
	 * @since TBD
	 * @param {HTMLElement} element The element to check.
	 * @return {boolean} True if bound, false otherwise.
	 */
	const isBound = ( element ) => element.hasAttribute( SELECTORS.dataBound );

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
		const grid = qs( SELECTORS.dropdown );
		if ( grid ) {
			grid.addEventListener( 'change', handleCheckboxChange );
		}
		if ( closeButton ) {
			closeButton.addEventListener( 'click', ( event ) => {
				event.stopPropagation();
				const pickerElement = qs( SELECTORS.picker );
				const dropdown = qs( SELECTORS.dropdown );
				if ( pickerElement && dropdown ) {
					closeDropdown( pickerElement, dropdown );
				}
			} );
		}
		if ( resetButton ) {
			resetButton.addEventListener( 'click', resetSelection );
		}
		// MutationObserver to clean up bindings if DOM changes
		if ( ! observer ) {
			observer = new window.MutationObserver( cleanupBindings );
			observer.observe( document.body, { childList: true, subtree: true } );
		}
		window.addEventListener( 'beforeunload', cleanupBindings );
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
