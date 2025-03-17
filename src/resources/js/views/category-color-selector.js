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
tribe.events.categoryColors.picker = ( function () {
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
		picker: '.tec-category-color-picker',
		dropdown: '.tec-category-color-picker__dropdown',
		checkbox: '.tec-category-color-picker__checkbox',
		dropdownIcon: '.tec-category-color-picker__dropdown-icon',
	};

	/**
	 * Toggles the dropdown visibility
	 *
	 * @since TBD
	 *
	 * @param {Event} event - The click event
	 *
	 * @return {void}
	 */
	obj.toggleDropdown = function ( event ) {
		event.stopPropagation();
		const picker = event.currentTarget;
		const dropdown = picker.querySelector( obj.selectors.dropdown );

		if ( ! dropdown ) {
			return;
		}

		// If dropdown is already open, do nothing
		const isOpen = dropdown.classList.contains( 'tec-category-color-picker__dropdown--visible' );
		if ( isOpen ) {
			return;
		}

		// Otherwise, open the dropdown
		dropdown.classList.add( 'tec-category-color-picker__dropdown--visible' );
		picker.classList.add('tec-category-color-picker--open');

		// Get dropdown position.
		const rect = dropdown.getBoundingClientRect();
		const isOffScreen = rect.right > window.innerWidth;

		// Apply class if dropdown would overflow.
		if ( isOffScreen ) {
			picker.classList.add( 'tec-category-color-picker--align-right' );
		} else {
			picker.classList.remove( 'tec-category-color-picker--align-right' );
		}
	};

	/**
	 * Closes the dropdown when clicking outside
	 *
	 * @since TBD
	 *
	 * @param {Event} event - The click event
	 *
	 * @return {void}
	 */
	obj.handleOutsideClick = function ( event ) {
		const picker = document.querySelector( obj.selectors.picker );
		const dropdown = document.querySelector( obj.selectors.dropdown );

		if ( ! picker ) {
			return;
		}

		// If the clicked element is inside the picker or the dropdown, do nothing
		if ( picker.contains( event.target ) || dropdown.contains( event.target ) ) {
			return;
		}


		// Otherwise, close the dropdown
		dropdown.classList.remove( 'tec-category-color-picker__dropdown--visible' );
		picker.classList.remove( 'tec-category-color-picker--open' );
	};


	/**
	 * Handles checkbox value changes and updates event visibility.
	 *
	 * @since TBD
	 *
	 * @param {Event} event - The change event.
	 *
	 * @return {void}
	 */
	obj.handleCheckboxChange = ( { target } ) => {
		const categorySlug = target.dataset.category;
		const events = document.querySelectorAll( '.tribe-events-calendar-list__event, .tribe-events-calendar-day__event, .tribe-events-calendar-month__calendar-event, .tribe-events-pro-summary__event, .tribe-events-pro-photo__event, .tribe-events-pro-week-grid__event, .tribe-events-pro-week-grid__multiday-event, .tribe-events-calendar-month__multiday-event' );

		// Maintain a Set of selected categories.
		obj.selectedCategories = obj.selectedCategories ?? new Set();

		// Update the Set based on checkbox state.
		target.checked ? obj.selectedCategories.add( categorySlug ) : obj.selectedCategories.delete( categorySlug );

		const selectedCategoriesArray = [ ...obj.selectedCategories ];

		events.forEach( event => {
			const eventCategories = [ ...event.classList ].filter( cls => cls.startsWith( 'tribe_events_cat-' ) );
			const hasMatch = selectedCategoriesArray.some( cat => eventCategories.includes( `tribe_events_cat-${ cat }` ) );

			// Apply filtering classes.
			event.classList.toggle(
				'tec-category-filtered-hide',
				selectedCategoriesArray.length > 0 && ! hasMatch
			);
		} );
	};

	/**
	 * Hooks into XMLHttpRequest to detect AJAX completion.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.monitorTECAjax = function () {
		const originalOpen = XMLHttpRequest.prototype.open;

		XMLHttpRequest.prototype.open = function ( method, url, ...args ) {
			// Check if this is a TEC-related AJAX request.
			if ( url.includes( '/wp-json/tribe/views/v2/html' ) ) {
				this.addEventListener(
					"load",
					function () {
						if ( this.readyState === 4 && this.status === 200 ) {
							obj.ensureBindings();
						}
					}
				);
			}

			// Call the original open method.
			return originalOpen.apply(
				this,
				[ method, url, ...args ]
			);
		};
	}


	/**
	 * Ensures event bindings persist after AJAX updates.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ensureBindings = function () {
		// Wait for the next frame to ensure the DOM has settled.
		requestAnimationFrame( () => {
			const picker = document.querySelector( obj.selectors.picker );

			if ( ! picker ) {
				console.warn( "Picker not found, trying again..." );
				setTimeout(
					obj.ensureBindings,
					50
				);
				return;
			}

			obj.cleanupBindings(); // Cleanup first to avoid duplicates.

			if ( ! picker.hasAttribute( 'data-bound' ) ) {
				obj.bindEvents();
				// Prevent duplicate bindings.
				picker.setAttribute(
					'data-bound',
					'true'
				);
			}
		} );
	};


	/**
	 * Removes old event bindings to prevent duplicate listeners.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.cleanupBindings = function () {
		const picker = document.querySelector( obj.selectors.picker );

		if ( picker && picker.hasAttribute( 'data-bound' ) ) {
			obj.unbindEvents();
			picker.removeAttribute( 'data-bound' ); // Ensure we reset bindings properly
		}
	};


	/**
	 * Binds events for the category color picker
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		const picker = document.querySelector( obj.selectors.picker );
		const checkboxes = document.querySelectorAll( obj.selectors.checkbox );

		if ( picker ) {
			picker.addEventListener(
				'click',
				obj.toggleDropdown
			);
		}

		document.addEventListener(
			'click',
			obj.handleOutsideClick
		);

		checkboxes.forEach( checkbox => {
			checkbox.addEventListener(
				'change',
				obj.handleCheckboxChange
			);
		} );
	};

	/**
	 * Unbinds events for the category color picker
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function () {
		const picker = document.querySelector( obj.selectors.picker );
		const checkboxes = document.querySelectorAll( obj.selectors.checkbox );

		if ( picker ) {
			picker.removeEventListener(
				'click',
				obj.toggleDropdown
			);
		}

		document.removeEventListener(
			'click',
			obj.handleOutsideClick
		);

		checkboxes.forEach( checkbox => {
			checkbox.removeEventListener(
				'change',
				obj.handleCheckboxChange
			);
		} );
	};

	/**
	 * Initializes the category color picker
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.init = function () {
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
		'DOMContentLoaded',
		obj.init
	);

	return obj;
} )();
