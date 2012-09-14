<?php
/**
 * Events List Widget Template
 * This is the template for the output of the events list widget. 
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is needed.
 *
 * This view contains the filters required to create an effective events list widget view.
 *
 * You can recreate an ENTIRELY new events list widget view by doing a template override,
 * and placing a list-widget.php file in a tribe-events/widgets/ directory 
 * within your theme directory, which will override the /views/widgets/list-widget.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @return string
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

// Vars set:
// '$event->AllDay',
// '$event->StartDate',
// '$event->EndDate',
// '$event->ShowMapLink',
// '$event->ShowMap',
// '$event->Cost',
// '$event->Phone',

if ( !defined('ABSPATH') ) { die('-1'); }

$event = array();
$tribe_ecp = TribeEvents::instance();
reset( $tribe_ecp->metaTags ); // Move pointer to beginning of array
foreach( $tribe_ecp->metaTags as $tag ) {
	$var_name = str_replace( '_Event', '', $tag );
	$event[$var_name] = tribe_get_event_meta( $post->ID, $tag, true );
}

$event = (object) $event; // Easier to work with

ob_start();
if ( !isset( $alt_text ) ) { $alt_text = ''; }
post_class( $alt_text, $post->ID );
$class = ob_get_contents();
ob_end_clean();

// start list widget template
echo apply_filters( 'tribe_events_list_widget_before_template', '', get_the_ID() );

	// start single event
	echo apply_filters( 'tribe_events_list_widget_before_the_event', '', get_the_ID() );
	
		// event dates
		echo apply_filters( 'tribe_events_list_widget_before_the_date', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_widget_the_date', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_widget_after_the_date', '', get_the_ID() );

		// event title
		echo apply_filters( 'tribe_events_list_widget_before_the_title', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_widget_the_title', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_widget_after_the_title', '', get_the_ID() );
	
	// end single event
	echo apply_filters( 'tribe_events_list_widget_after_the_event', '', get_the_ID() );

// end list widget template
echo apply_filters( 'tribe_events_list_widget_after_template', '', get_the_ID() );
