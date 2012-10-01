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
echo apply_filters( 'tribe_events_calendar_before_template', '', get_the_ID() );

	// Calendar title
	echo apply_filters( 'tribe_events_calendar_before_the_title', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_the_title', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_after_the_title', '', get_the_ID() );

	// Calendar notices
	echo apply_filters( 'tribe_events_calendar_notices', array(), get_the_ID() );

	// Calendar header
    echo apply_filters( 'tribe_events_calendar_before_header', '', get_the_ID() );

    	// Calendar dropdown navigation
    	echo apply_filters( 'tribe_events_calendar_before_nav', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_nav', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_after_nav', '', get_the_ID() );

		// List view buttons
		echo apply_filters( 'tribe_events_calendar_the_view_buttons', '', get_the_ID() );
			
	echo apply_filters( 'tribe_events_calendar_after_header', '', get_the_ID() );
		
	// Calendar grid
	echo apply_filters( 'tribe_events_calendar_before_the_grid', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_the_grid', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_after_the_grid', '', get_the_ID() );

// End calendar template
echo apply_filters( 'tribe_events_calendar_after_template', '', get_the_ID() );
