<?php
/**
 * Single Organizer Template
 * The template for an organizer. By default it displays organizer information and lists 
 * events that occur with the specified organizer.
 *
 * This view contains the filters required to create an effective single organizer view.
 *
 * You can recreate an ENTIRELY new single organizer view by doing a template override, and placing
 * a single-organizer.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/single-organizer.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
 
if ( !defined('ABSPATH') ) { die('-1'); }

$organizer_id = get_the_ID();

// Start single organizer template
echo apply_filters( 'tribe_events_single_organizer_before_template', '', $organizer_id );

	// Start single organizer
	echo apply_filters( 'tribe_events_single_organizer_before_organizer', '', $organizer_id );
	
		// organizer featured image
		echo apply_filters( 'tribe_events_single_organizer_featured_image', '', $organizer_id );
		
		// organizer meta
		echo apply_filters( 'tribe_events_single_organizer_before_the_meta', '', $organizer_id );
		echo apply_filters( 'tribe_events_single_organizer_the_meta', '', $organizer_id );
		echo apply_filters( 'tribe_events_single_organizer_after_the_meta', '', $organizer_id );

	// End single organizer
	echo apply_filters( 'tribe_events_single_organizer_after_organizer', '', $organizer_id );
	
	// upcoming event list
	echo apply_filters( 'tribe_events_single_organizer_upcoming_events', '', $organizer_id );
	
// End single organizer template
echo apply_filters( 'tribe_events_single_organizer_after_template', '', $organizer_id );




