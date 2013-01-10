<?php
/**
 * Single Venue Template
 * The template for a venue. By default it displays venue information and lists 
 * events that occur at the specified venue.
 *
 * This view contains the filters required to create an effective single venue view.
 *
 * You can recreate an ENTIRELY new single venue view by doing a template override, and placing
 * a single-venue.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/single-venue.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

$venue_id = get_the_ID();

// Start single venue template
echo apply_filters( 'tribe_events_single_venue_before_template', '', $venue_id );

	// Start single venue
	echo apply_filters( 'tribe_events_single_venue_before_venue', '', $venue_id );

		// Venue featured image
		echo apply_filters( 'tribe_events_single_venue_featured_image', '', $venue_id );
		
		// Venue map
		echo apply_filters( 'tribe_events_single_venue_map', '', $venue_id );

		// Venue title
		echo apply_filters( 'tribe_events_single_venue_the_title', '', $venue_id );
	
		// Venue meta
		echo apply_filters( 'tribe_events_single_venue_before_the_meta', '', $venue_id );
		echo apply_filters( 'tribe_events_single_venue_the_meta', '', $venue_id );
		echo apply_filters( 'tribe_events_single_venue_after_the_meta', '', $venue_id );

	// End single venue
	echo apply_filters( 'tribe_events_single_venue_after_venue', '', $venue_id );
	
	// Upcoming event list
	echo apply_filters( 'tribe_events_single_venue_upcoming_events', '', $venue_id );
	
// End single venue template
echo apply_filters( 'tribe_events_single_venue_after_template', '', $venue_id );
