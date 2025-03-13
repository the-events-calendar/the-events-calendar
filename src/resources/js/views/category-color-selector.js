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

		// If the clicked element is inside the picker or the dropdown, do nothing
		if ( picker.contains( event.target ) || dropdown.contains( event.target ) ) {
			return;
		}


		// Otherwise, close the dropdown
		dropdown.classList.remove( 'tec-category-color-picker__dropdown--visible' );
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
	obj.handleCheckboxChange = ({ target }) => {
		const categorySlug = target.dataset.category;
		const events = document.querySelectorAll('.tribe-events-calendar-list__event, .tribe-events-calendar-day__event, .tribe-events-calendar-month__calendar-event');

		// Maintain a Set of selected categories.
		obj.selectedCategories = obj.selectedCategories ?? new Set();

		// Update the Set based on checkbox state.
		target.checked ? obj.selectedCategories.add(categorySlug) : obj.selectedCategories.delete(categorySlug);

		const selectedCategoriesArray = [...obj.selectedCategories];

		events.forEach(event => {
			const eventCategories = [...event.classList].filter(cls => cls.startsWith('tribe_events_cat-'));
			const hasMatch = selectedCategoriesArray.some(cat => eventCategories.includes(`tribe_events_cat-${cat}`));

			// Apply filtering classes.
			event.classList.toggle('tec-category-filtered-hide', selectedCategoriesArray.length > 0 && !hasMatch);
		});
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
