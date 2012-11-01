<?php
/**
 * Events Pro Featured Widget Template
 * This is the template for the output of the featured widget.
 *
 * This view contains the filters required to create an effective featured widget view.
 *
 * You can recreate an ENTIRELY new featured list widget view by doing a template override,
 * and placing a featured-widget.php file in a tribe-events/pro/widgets/ directory 
 * within your theme directory, which will override the /views/widgets/featured-widget.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @return string
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
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
reset( $tribe_ecp->metaTags ); // Move pointer to beginning of array.
foreach( $tribe_ecp->metaTags as $tag ) {
	$var_name = str_replace( '_Event', '', $tag );
	$event[$var_name] = tribe_get_event_meta( $post->ID, $tag, true );
}

$event = (object) $event; //Easier to work with.

ob_start();
post_class( $alt_text,$post->ID );
$class = ob_get_contents();
ob_end_clean();

// Start featured widget template
echo apply_filters( 'tribe_events_pro_featured_widget_before_template', $post->ID );

	// Start single event
	echo apply_filters( 'tribe_events_pro_featured_widget_before_the_event', $post->ID );
		
		// Event title
		echo apply_filters( 'tribe_events_pro_featured_widget_before_the_title', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_the_title', $post );
		echo apply_filters( 'tribe_events_pro_featured_widget_after_the_title', $post->ID );
		
		// Event dates
		echo apply_filters( 'tribe_events_pro_featured_widget_before_the_date', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_the_date', $post->ID, $event );
		echo apply_filters( 'tribe_events_pro_featured_widget_after_the_date', $post->ID );
		
		// Event location
		echo apply_filters( 'tribe_events_pro_featured_widget_before_the_location', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_the_location', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_after_the_location', $post->ID );
		
		// Event content
		echo apply_filters( 'tribe_events_pro_featured_widget_before_the_content', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_the_content', $post->ID );
		echo apply_filters( 'tribe_events_pro_featured_widget_after_the_content', $post->ID );
	
	// End single event
	echo apply_filters( 'tribe_events_pro_featured_widget_after_the_event', $post->ID );

// End featured widget template
echo apply_filters( 'tribe_events_pro_featured_widget_after_template', $post->ID );

$alt_text = ( empty( $alt_text ) ) ? 'alt' : '';