<?php
/**
 * Calendar View Template
 * This file loads the TEC month or calendar view, specifically the month view navigation.
 *
 * This view contains the hooks and filters required to create an effective calendar month view.
 *
 * You can recreate and ENTIRELY new list view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a calendar.php file in a tribe-events/ directory 
 * within your theme directory, which will override the /views/calendar.php.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

echo apply_filters( 'tribe_events_calendar_before_template', '', get_the_ID() );

	// calendar title
	echo apply_filters( 'tribe_events_calendar_before_the_title', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_the_title', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_after_the_title', '', get_the_ID() );

	echo apply_filters( 'tribe_events_calendar_notices', array(), get_the_ID() );

    echo apply_filters( 'tribe_events_calendar_before_header', '', get_the_ID() );

    	// calendar dropdown navigation
    	echo apply_filters( 'tribe_events_calendar_before_nav', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_nav', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_after_nav', '', get_the_ID() );

		// calendar pagination
		echo apply_filters( 'tribe_events_calendar_before_buttons', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_buttons', '', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_after_buttons', '', get_the_ID() );
			
	echo apply_filters( 'tribe_events_calendar_after_header', '', get_the_ID() );
		
	// See the views/modules/calendar-grid.php template for customization
	tribe_calendar_grid();

// end calendar template
echo apply_filters( 'tribe_events_calendar_after_template', '', get_the_ID() );
