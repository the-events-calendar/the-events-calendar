var tribe_events_event_editor = tribe_events_event_editor || {};

/**
 * Implements behaviours that are specific to the event editor.
 */
jQuery( function( $ ) {
	var obj = tribe_events_event_editor,
		$sticky_in_month_view_checkbox = $( 'input[name="EventShowInCalendar"]' ),
		$featured_event_checkbox = $( 'input[name="feature_event"]' );

	/**
	 * If the 'feature event' box is checked, automatically check the
	 * sticky-in-month-view box also.
	 */
	obj.auto_enable_sticky_field = function() {
		if ( $( this ).prop( 'checked' ) ) {
			$sticky_in_month_view_checkbox.prop( 'checked', true );
		}
	};

	$featured_event_checkbox.on( 'change', obj.auto_enable_sticky_field );
	$( obj ).trigger( 'event-editor-post-init.tribe' );
} );
