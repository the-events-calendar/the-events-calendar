var tribe_events_event_editor = tribe_events_event_editor || {};

/**
 * Implements behaviours that are specific to the event editor.
 */
( function ( $, obj ) {
	'use strict';

	/**
	 * Setup our selectors.
	 *
	 * @since 6.0.1
	 */
	obj.selectors = {
		sticky_in_month_view_checkbox: 'input[name="EventShowInCalendar"]',
		featured_event_checkbox: 'input[name="feature_event"]',
		organizer: {
			area: '#event_tribe_organizer',
			delete_button: '.tribe-delete-this',
			add_button: '#event_tribe_organizer .tribe-add-post',
			post_dropdown: '.linked-post-dropdown',
			saved_organizers: '.saved-linked-post',
		},
	};

	obj.organizer = {};

	/**
	 * Controls logic for the Organizer delete button to display.
	 *
	 * If more than one organizer exists, display the delete button.
	 * If only one organizer exists, hide the delete button.
	 *
	 * @since 6.0.1
	 * @return void
	 */
	obj.organizer.deleteButtonDisplayLogic = function () {

		const $organizers = $( obj.selectors.organizer.area )
			.find( obj.selectors.organizer.saved_organizers );

		$( obj.selectors.organizer.area )
			.find( obj.selectors.organizer.delete_button )
			.each( function () {

				// If you have more than one organizer then we display the delete button.
				if ( $organizers.length > 1 ) {
					$( this ).show();
					return;
				}

				const $organizer_dropdown =
					$( obj.selectors.organizer.area +
						' ' +
						obj.selectors.organizer.post_dropdown );

				//If this is running, it's because we only have one organizer.
				obj.organizer.addButtonLogic( $organizer_dropdown.val() );

				$( this ).hide();

			} );

	};

	/**
	 * Logic to display, or hide the "Add Organizer" button.
	 *
	 * @since 6.0.1
	 *
	 * @param selectValue
	 * @return void
	 */
	obj.organizer.addButtonLogic = function ( selectValue ) {
		if ( selectValue !== '-1' ) {
			$( obj.selectors.organizer.add_button ).show();
		} else {
			$( obj.selectors.organizer.add_button ).hide();
		}
	};
	/**
	 * Trigger events for bind events.
	 *
	 * @since 6.0.1
	 */
	obj.organizer.bindEvents = function () {

		$( obj.selectors.organizer.area )
			.on( 'change', obj.selectors.organizer.post_dropdown, function () {
			obj.organizer.addButtonLogic( this.value );
			obj.organizer.deleteButtonDisplayLogic();

		} );

		// Functions to run when the delete button is clicked.
		$( obj.selectors.organizer.area )
			.on( 'click', obj.selectors.organizer.delete_button, function () {
				// We have to run this in a setTimeout because the original functionality uses a fade of 500ms.
				// Therefore we use 525ms to run slightly after it is done.
				setTimeout( function () {
					obj.organizer.deleteButtonDisplayLogic();
				}, 525 );

			} );

	};

	/**
	 * If the 'feature event' box is checked, automatically check the
	 * sticky-in-month-view box also.
	 *
	 * @since 6.0.1
	 *
	 */
	obj.auto_enable_sticky_field = function () {
		if ( $( this ).prop( 'checked' ) ) {
			$( obj.selectors.sticky_in_month_view_checkbox ).prop( 'checked', true );
		}
	};

	/**
	 * Bind featured events logic.
	 *
	 * @since 6.0.1
	 */
	obj.bindFeaturedEvents = function () {
		$( obj.selectors.featured_event_checkbox ).on( 'change', obj.auto_enable_sticky_field );
		$( obj ).trigger( 'event-editor-post-init.tribe' );
	};

	/**
	 * Initialize
	 *
	 * @since 6.0.1
	 */
	obj.init = function () {
		// Hide the "Add Organizer" button by default.
		$( obj.selectors.organizer.add_button ).hide();
		// Run our delete button logic.
		obj.organizer.deleteButtonDisplayLogic();
		obj.organizer.bindEvents();
		obj.bindFeaturedEvents();
	};

	// Init our main object.
	obj.init();

} )( jQuery, tribe_events_event_editor );