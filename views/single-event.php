<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and 
 * optionally, the Google map for the event.
 *
 * This view contains the filters required to create an effective single event view.
 *
 * You can recreate an ENTIRELY new single event view by doing a template override, and placing
 * a single-event.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/single-event.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$event_id = get_the_ID();

// Start single template
echo apply_filters( 'tribe_events_single_event_before_template', '', $event_id );

	// Event notice
	echo apply_filters( 'tribe_events_single_event_notices', $event_id );

	// Event featured image
	echo apply_filters( 'tribe_events_single_event_featured_image', '', $event_id );
	
	// Event header
    echo apply_filters( 'tribe_events_single_event_before_header', '', $event_id );

    	// Navigation
    	echo apply_filters( 'tribe_events_single_event_before_header_nav', '', $event_id );
		echo apply_filters( 'tribe_events_single_event_header_nav', '', $event_id );
		echo apply_filters( 'tribe_events_single_event_after_header_nav', '', $event_id );

	echo apply_filters( 'tribe_events_single_event_after_header', '', $event_id );
	
	// Event title
	echo apply_filters( 'tribe_events_single_event_before_the_title', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_the_title', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_after_the_title', '', $event_id );

	// Event content
	echo apply_filters( 'tribe_events_single_event_before_the_content', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_the_content', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_after_the_content', '', $event_id );	

	// Event meta
	echo apply_filters( 'tribe_events_single_event_before_the_meta', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_the_meta', '', $event_id );
	echo apply_filters( 'tribe_events_single_event_after_the_meta', '', $event_id );
	
	// Event footer
    echo apply_filters( 'tribe_events_single_event_before_footer', '', $event_id );

    	// Navigation
    	echo apply_filters( 'tribe_events_single_event_before_footer_nav', '', $event_id );
		echo apply_filters( 'tribe_events_single_event_footer_nav', '', $event_id );
		echo apply_filters( 'tribe_events_single_event_after_footer_nav', '', $event_id );

	echo apply_filters( 'tribe_events_single_event_after_footer', '', $event_id );
	
// End single template
echo apply_filters( 'tribe_events_single_event_after_template', '', $event_id );
