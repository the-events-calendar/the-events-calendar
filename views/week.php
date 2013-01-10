<?php
/**
 * Week Grid Template
 * The template for displaying events by week.
 *
 * This view contains the filters required to create an effective week grid view.
 *
 * You can recreate an ENTIRELY new week grid view by doing a template override, and placing
 * a week.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/week.php. 
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

// Start week template
echo apply_filters( 'tribe_events_week_before_template', '');

	// Week title
	echo apply_filters( 'tribe_events_week_the_title', '');

    // Week navigation
    /*
	echo apply_filters( 'tribe_events_week_the_header', '');
	*/
	
	// Week header
    echo apply_filters( 'tribe_events_week_before_header', '');

    	// Navigation
    	echo apply_filters( 'tribe_events_week_before_header_nav', '');
		echo apply_filters( 'tribe_events_week_header_nav', '');
		echo apply_filters( 'tribe_events_week_after_header_nav', '');

	echo apply_filters( 'tribe_events_week_after_header', '');

	// Week grid
	echo apply_filters( 'tribe_events_week_the_grid', '');
	
	// Week footer
    echo apply_filters( 'tribe_events_week_before_footer', '');

    	// Navigation
    	echo apply_filters( 'tribe_events_week_before_footer_nav', '');
		echo apply_filters( 'tribe_events_week_footer_nav', '');
		echo apply_filters( 'tribe_events_week_after_footer_nav', '');

	echo apply_filters( 'tribe_events_week_after_footer', '');

// End week template
echo apply_filters( 'tribe_events_week_after_template', '');
