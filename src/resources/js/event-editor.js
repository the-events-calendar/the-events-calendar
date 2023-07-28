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
		addButton: '.tribe-add-post',
		deleteButton: '.tribe-delete-this',
		dropDown: '.linked-post-dropdown',
		featuredEventCheckbox: 'input[name="feature_event"]',
		finalDropDown: '.linked-post-dropdown:last-of-type',
		linkedPostWrapper: '.linked-post-wrapper',
		organizers: '#event_tribe_organizer',
		savedItems: '.saved-linked-post',
		stickyInMonthViewCheckbox: 'input[name="EventShowInCalendar"]',
		venues: '#event_tribe_organizer',
	};

	obj.linkedPost = {};

	/**
	 * Controls logic for the Organizer delete button to display.
	 *
	 * If more than one organizer exists, display the delete button.
	 * If only one organizer exists, hide the delete button.
	 *
	 * @since 6.0.1
	 * @return void
	 */
	obj.linkedPost.deleteButtonDisplayLogic = function () {

		const $posts = $( obj.selectors.linkedPostWrapper )
			.find( obj.selectors.savedItems );

		$( obj.selectors.linkedPostWrapper )
			.find( obj.selectors.deleteButton )
			.each( function () {

				// If you have more than one organizer then we display the delete button.
				if ( $posts.length > 1 ) {
					$( this ).show();
					return;
				}

				const $dropdown = $( obj.selectors.finalDropDown );

				//If this is running, it's because we only have one organizer.
				obj.linkedPost.addButtonLogic( $dropdown );

				$( this ).hide();

			} );

	};

	/**
	 * Logic to display, or hide the "Add" button.
	 *
	 * @since 6.0.1
	 *
	 * @param $dropdown
	 * @return void
	 */
	obj.linkedPost.addButtonLogic = function ( $dropdown ) {
		const $wrapper = $dropdown.closest( obj.selectors.linkedPostWrapper );
		const $finalDropDown = $wrapper.find( obj.selectors.finalDropDown );
		const $addButton = $wrapper.find( obj.selectors.addButton );
		console.log( $finalDropDown.val() );
		if ( $finalDropDown.val() !== '-1' ) {
			$addButton.show();
		} else {
			$addButton.hide();
		}
	};

	/**
	 * Trigger events for bind events.
	 *
	 * @since 6.0.1
	 */
	obj.linkedPost.bindEvents = function () {

		$( obj.selectors.linkedPostWrapper ).on(
			'change',
			obj.selectors.dropDown,
			function () {
				obj.linkedPost.addButtonLogic( $( this ) );
				obj.linkedPost.deleteButtonDisplayLogic();
			}
		);

		// Functions to run when the delete button is clicked.
		$( obj.selectors.linkedPostWrapper )
			.on( 'click', obj.selectors.deleteButton, function () {
				// We have to run this in a setTimeout because the original functionality uses a fade of 500ms.
				// Therefore we use 525ms to run slightly after it is done.
				setTimeout( function () {
					obj.linkedPost.deleteButtonDisplayLogic();
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
			$( obj.selectors.stickyInMonthViewCheckbox ).prop( 'checked', true );
		}
	};

	/**
	 * Bind featured events logic.
	 *
	 * @since 6.0.1
	 */
	obj.bindFeaturedEvents = function () {
		$( obj.selectors.featuredEventCheckbox ).on( 'change', obj.auto_enable_sticky_field );
		$( obj ).trigger( 'event-editor-post-init.tribe' );
	};

	/**
	 * Initialize
	 *
	 * @since 6.0.1
	 */
	obj.init = function () {
		const $finalDropDowns = $( obj.selectors.linkedPostWrapper + ' ' + obj.selectors.finalDropDown );
		$finalDropDowns.each( function ( index, el ) {
			obj.linkedPost.addButtonLogic( $( el ) );
		} );

		obj.linkedPost.deleteButtonDisplayLogic();
		obj.linkedPost.bindEvents();
		obj.bindFeaturedEvents();
	};

	// Init our main object.
	$( obj.init() );

} )( jQuery, tribe_events_event_editor );