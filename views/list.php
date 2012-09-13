<?php
/**
 * Events List Template
 * The TEC template for a list of events. This includes the Past Events and Upcoming Events views 
 * as well as those same views filtered to a specific category.
 *
 * This view contains the hooks and filters required to create an effective event list view.
 *
 * You can recreate and ENTIRELY new list view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a list.php file in a /tribe-events/ directory 
 * within your theme directory, which will override this file /events/views/list.php.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

// start list template
echo apply_filters( 'tribe_events_list_before_template', '', get_the_ID() );

	// list view buttons
	echo apply_filters( 'tribe_events_list_the_view_buttons', '', get_the_ID() );
	
	// start list loop
	echo apply_filters( 'tribe_events_list_before_loop', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_inside_before_loop', '', get_the_ID() );
	
		// event start date
		echo apply_filters( 'tribe_events_list_the_start_date', '', get_the_ID() );
	
		// event title
		echo apply_filters( 'tribe_events_list_the_title', '', get_the_ID() );

		// event content
		echo apply_filters( 'tribe_events_list_before_the_content', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_the_content', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_after_the_content', '', get_the_ID() );
	
		// event meta
		echo apply_filters( 'tribe_events_list_before_the_meta', '', get_the_ID() );
		apply_filters( 'tribe_events_list_the_meta', '', get_the_ID() );
		echo apply_filters( 'tribe_events_list_after_the_meta', '', get_the_ID() );
	
	// end list loop
	echo apply_filters( 'tribe_events_list_inside_after_loop', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_after_loop', '', get_the_ID() );
	
	// event notice
	echo apply_filters( 'tribe_events_list_notices', $notices, $notices, get_the_ID() );

	// list pagination
	echo apply_filters( 'tribe_events_list_before_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_prev_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_next_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_after_pagination', '', get_the_ID() );

// end list template
echo apply_filters( 'tribe_events_list_after_template', '', get_the_ID() );
