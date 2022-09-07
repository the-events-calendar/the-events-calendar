var tribe_events_event_editor = tribe_events_event_editor || {};

/**
 * Implements behaviours that are specific to the event editor.
 */
( function ( $, obj ) {
	'use strict';

	obj = obj || {};

	obj.selectors = {
		$sticky_in_month_view_checkbox: $( 'input[name="EventShowInCalendar"]' ),
		$featured_event_checkbox: $( 'input[name="feature_event"]' ),
		organizer: {
			area: '#event_tribe_organizer',
			delete_button: '.tribe-delete-this',
			add_button: '#event_tribe_organizer .tribe-add-post',
		},
	};

	obj.organizer = {};
	/**
	 * Controls logic for the Organizer delete button to display.
	 *
	 * If more than 1 organizer exists, display the delete button.
	 * If only 1 organizer exists, hide the delete button.
	 *
	 * @return void
	 */
	obj.organizer.deleteButtonDisplayLogic = () => {

		$( obj.selectors.organizer.area ).find( obj.selectors.organizer.delete_button ).each( function () {
			// If you have more than 1 organizer than we display the delete button.
			if ( $( obj.selectors.organizer.area ).find( '.saved-linked-post' ).length > 1 ) {
				$( this ).show();
				return;
			}
			//If this is running, it's because we only have 1 organizer
			if ( $( obj.selectors.organizer.area + ' .linked-post-dropdown' ).val() !== -1 ) {
				$( obj.selectors.organizer.add_button ).show();
			}

			$( this ).hide();

		} );

	};

	obj.organizer.bindEvents = () => {

		/**
		 * Logic for the organizer area -
		 *"Add Organizer" button should be hidden by default, only appearing when there is more than 1 organizer.
		 *"Trash" icon / delete button should be hidden by default, only appearing when there is more than 1 organizer
		 *  or when an organizer has the value of -1.
		 */
		$( obj.selectors.organizer.add_button ).hide();
		obj.organizer.deleteButtonDisplayLogic();

		$( obj.selectors.organizer.area ).on( 'change', '.linked-post-dropdown', function () {

			if ( this.value !== -1 ) {
				$( obj.selectors.organizer.add_button ).show();
			} else {
				$( obj.selectors.organizer.add_button ).hide();
			}

			obj.organizer.deleteButtonDisplayLogic();

		} );

		// Functions to run when the delete button is clicked.
		$( obj.selectors.organizer.area ).on( 'click', obj.selectors.organizer.delete_button, function () {
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
	 */
	obj.auto_enable_sticky_field = function () {
		if ( $( this ).prop( 'checked' ) ) {
			obj.selectors.$sticky_in_month_view_checkbox.prop( 'checked', true );
		}
	};

	obj.bindFeaturedEvents = () => {
		obj.selectors.$featured_event_checkbox.on( 'change', obj.auto_enable_sticky_field );
		$( obj ).trigger( 'event-editor-post-init.tribe' );
	};

	obj.init = () => {
		obj.organizer.bindEvents();
		obj.bindFeaturedEvents();
	};

	//Init our main object
	obj.init();

} )( jQuery );