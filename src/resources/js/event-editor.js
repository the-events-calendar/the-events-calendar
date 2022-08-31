var tribe_events_event_editor = tribe_events_event_editor || {};

/**
 * Implements behaviours that are specific to the event editor.
 */
jQuery( function( $ ) {
	var obj = tribe_events_event_editor,
		$sticky_in_month_view_checkbox = $( 'input[name="EventShowInCalendar"]' ),
		$featured_event_checkbox = $( 'input[name="feature_event"]' ),
		add_organizer_button = '#event_tribe_organizer .tribe-add-post',
		organizer_area = '#event_tribe_organizer',
		organizer_delete_button = '.tribe-delete-this';

	/**
	 * If the 'feature event' box is checked, automatically check the
	 * sticky-in-month-view box also.
	 */
	obj.auto_enable_sticky_field = function() {
		if ( $( this ).prop( 'checked' ) ) {
			$sticky_in_month_view_checkbox.prop( 'checked', true );
		}
	};
	/**
	 * Logic for the organizer area -
	 *"Add Organizer" button should be hidden by default, only appearing when there is more than 1 organizer.
	 *"Trash" icon / delete button should be hidden by default, only appearing when there is more than 1 organizer or when an organizer has the value of -1.
	 */
	$( add_organizer_button ).hide();
	organizer_delete_button_display_logic();

	$( organizer_area ).on( 'change', '.linked-post-dropdown', function ( e ) {

		if ( this.value != -1 ) {
			$( add_organizer_button ).show();
		} else {
			$( add_organizer_button ).hide();
		}

		organizer_delete_button_display_logic();

	} );

	// Functions to run when the delete button is clicked.
	$( organizer_area ).on( 'click', organizer_delete_button, function ( e ) {
		// We have to run this in a setTimeout because the original functionality uses a fade of 500ms. Therefore we use 510ms to run slightly after it is done.
		setTimeout( function () {
			organizer_delete_button_display_logic();
		}, 510 );

	} );
	/**
	 * Controls logic for the Organizer delete button to display.
	 *
	 * If more than 1 organizer exists, display the delete button.
	 * If only 1 organizer exists, hide the delete button.
	 *
	 * @return void
	 */
	function organizer_delete_button_display_logic(){

		$( organizer_area ).find( organizer_delete_button ).each( function () {
			// If you have more than 1 organizer than we display the delete button.
			if ( $(organizer_area).find('.saved-linked-post').length > 1 ) {
				$( this ).show();
				return;
			}
			//If this is running, it's because we only have 1 organizer
			if ( $( organizer_area + ' .linked-post-dropdown' ).val() != -1 ) {
				$( add_organizer_button ).show();
			}

			$( this ).hide();

		} );

	}

	$featured_event_checkbox.on( 'change', obj.auto_enable_sticky_field );
	$( obj ).trigger( 'event-editor-post-init.tribe' );
} );
