<?php
/**
 * Map Template
 * Render our Map template.
 *
 * This view contains the filters required to create an effective map view.
 *
 * You can recreate an ENTIRELY new map view by doing a template override, and placing
 * a map.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/map.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

	$the_post_id = ( have_posts() ) ? get_the_ID() : null;

	// Start map template
	echo apply_filters( 'tribe_events_map_before_template', '', $the_post_id );

	// Map
	echo apply_filters( 'tribe_events_map_before_the_map', '', $the_post_id );
	echo apply_filters( 'tribe_events_map_the_map', '', $the_post_id );
	echo apply_filters( 'tribe_events_map_after_the_map', '', $the_post_id );
	
	// Options
	echo apply_filters( 'tribe_events_map_before_the_options', '', $the_post_id );
	echo apply_filters( 'tribe_events_map_the_options', '', $the_post_id );
	echo apply_filters( 'tribe_events_map_after_the_options', '', $the_post_id );

	echo apply_filters( 'tribe_events_map_the_title', '', $the_post_id );
	
	// Map header
    echo apply_filters( 'tribe_events_map_before_header', '', $the_post_id );

    	// Navigation
    	echo apply_filters( 'tribe_events_map_before_header_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_map_header_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_map_after_header_nav', '', $the_post_id );

	echo apply_filters( 'tribe_events_map_after_header', '', $the_post_id );
	
	// Results
	echo apply_filters( 'tribe_events_map_before_the_results', '', $the_post_id );
	echo apply_filters( 'tribe_events_map_after_the_results', '', $the_post_id );
	
	// Map footer
    echo apply_filters( 'tribe_events_map_before_footer', '', $the_post_id );

    	// Navigation
    	echo apply_filters( 'tribe_events_map_before_footer_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_map_footer_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_map_after_footer_nav', '', $the_post_id );

	echo apply_filters( 'tribe_events_map_after_footer', '', $the_post_id );

// End map template
echo apply_filters( 'tribe_events_map_after_template', '', $the_post_id );

Tribe_Events_Map_Template::init();