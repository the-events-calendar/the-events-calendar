<?php
/**
 * Grid View Template
 * This file loads the TEC month view, specifically the 
 * month view navigation. The actual rendering if the calendar happens in the 
 * table.php template.
 *
 * You can customize this view by putting a replacement file of the same name 
 * (calendar.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
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
