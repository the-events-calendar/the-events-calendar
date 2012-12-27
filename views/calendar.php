<?php
/**
 * Calendar Template
 * This file loads the TEC month or calendar view, specifically the month view navigation.
 *
 * This view contains the filters required to create an effective calendar month view.
 *
 * You can recreate an ENTIRELY new calendar view by doing a template override, and placing
 * a calendar.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/calendar.php. 
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

// Start calendar template
echo apply_filters( 'tribe_events_calendar_before_template', '' );

	// Calendar title
	echo apply_filters( 'tribe_events_calendar_before_the_title', '');
	echo apply_filters( 'tribe_events_calendar_the_title', '');
	echo apply_filters( 'tribe_events_calendar_after_the_title', '');

	// Calendar notices
	echo apply_filters( 'tribe_events_calendar_notices', array());

	// Calendar header
    echo apply_filters( 'tribe_events_calendar_before_header', '');

    	// Calendar dropdown navigation
    	echo apply_filters( 'tribe_events_calendar_before_nav', '');
		echo apply_filters( 'tribe_events_calendar_nav', '');
		echo apply_filters( 'tribe_events_calendar_after_nav', '');

	echo apply_filters( 'tribe_events_calendar_after_header', '');
		
	// Calendar grid
	echo apply_filters( 'tribe_events_calendar_before_the_grid', '');
	echo apply_filters( 'tribe_events_calendar_the_grid', '');
	echo apply_filters( 'tribe_events_calendar_after_the_grid', '');

// End calendar template
echo apply_filters( 'tribe_events_calendar_after_template', '');
