<?php
/**
 * Day Grid Template
 * The template for displaying events by day.
 *
 * This view contains the filters required to create an effective day grid view.
 *
 * You can recreate an ENTIRELY new day grid view by doing a template override, and placing
 * a day.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/day.php. 
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

echo apply_filters('tribe_events_day_before_template', '');

	// daily header (navigation)
	echo apply_filters( 'tribe_events_day_the_header', '');

	echo apply_filters( 'tribe_events_day_before_loop', '');

	if ( have_posts() ) {

		while ( have_posts() ) {
			the_post();
			echo apply_filters( 'tribe_events_day_inside_before_loop', '');
			echo apply_filters( 'tribe_events_day_the_event', '');
			echo apply_filters( 'tribe_events_day_inside_after_loop', '');
		}
	}

    echo apply_filters('tribe_events_day_after_loop', '');

echo apply_filters('tribe_events_day_after_template', '');
