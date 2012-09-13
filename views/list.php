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

$notices = empty($notices) ? array() : $notices;
if ( ! have_posts() ) { // Messages if currently no events
	$tribe_ecp = TribeEvents::instance();
	$is_cat_message = '';

	if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
		$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
		if( tribe_is_upcoming() ) {
			$is_cat_message = sprintf( __( ' listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
		} else if( tribe_is_past() ) {
			$is_cat_message = sprintf( __( ' listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
		}
	}

	if( tribe_is_day() )
		$notices[] = sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) );

	if( tribe_is_upcoming() ) {
		$notices[] = __( 'No upcoming events', 'tribe-events-calendar' ) . $is_cat_message;
	} elseif( tribe_is_past() ) {
		$notices[] = __( 'No previous events' , 'tribe-events-calendar' ) . $is_cat_message;
	}
}

// start list template
echo apply_filters( 'tribe_events_list_before_template', '', get_the_ID() );

	// list view buttons
	echo apply_filters( 'tribe_events_list_the_view_buttons', '', get_the_ID() );

	// list notices
	echo apply_filters( 'tribe_events_list_notices', $notices, $notices, get_the_ID() );
	
	// start list loop
	echo apply_filters( 'tribe_events_list_before_loop', '', get_the_ID() );

	// does this page have posts?
	if ( have_posts() ) {
		
		$hasPosts = true;

		// Start Loop
		while ( have_posts() ) {
			
			the_post();
			global $more; 
			$more = false;

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
				echo apply_filters( 'tribe_events_list_the_meta', '', get_the_ID() );
				echo apply_filters( 'tribe_events_list_after_the_meta', '', get_the_ID() );
		
		
			echo apply_filters( 'tribe_events_list_inside_after_loop', '', get_the_ID() );

		} // end list loop
	} // end if list has posts

	echo apply_filters( 'tribe_events_list_after_loop', '', get_the_ID() );
	
	// list pagination
	echo apply_filters( 'tribe_events_list_before_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_after_pagination', '', get_the_ID() );

// end list template
echo apply_filters( 'tribe_events_list_after_template', $hasPosts, get_the_ID() );
