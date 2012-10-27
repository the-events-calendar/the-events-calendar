<?php
/**
 * Events List Template
 * The template for a list of events. This includes the Past Events and Upcoming Events views 
 * as well as those same views filtered to a specific category.
 *
 * This view contains the filters required to create an effective events list view.
 *
 * You can recreate an ENTIRELY new list view by doing a template override, and placing
 * a list.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/list.php.
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

// Our various messages if there are no events for the query
$notices = empty($notices) ? array() : $notices;
if ( ! have_posts() ) { // Messages if currently no events
	$tribe_ecp = TribeEvents::instance();
	$is_cat_message = '';

	if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
		$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
		if( tribe_is_upcoming() ) {
			$is_cat_message = sprintf( __( '<p>listed under %s. Check out past events for this category or view the full calendar.</p>', 'tribe-events-calendar' ), $cat->name );
		} else if( tribe_is_past() ) {
			$is_cat_message = sprintf( __( '<p>listed under %s. Check out upcoming events for this category or view the full calendar.</p>', 'tribe-events-calendar' ), $cat->name );
		}
	}

	if( tribe_is_day() )
		$notices[] = sprintf( __( '<p>No events scheduled for <strong>%s</strong>. Please try another day.</p>', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) );

	if( tribe_is_upcoming() ) {
		$notices[] = __( '<p>No upcoming events</p>', 'tribe-events-calendar' ) . $is_cat_message;
	} elseif( tribe_is_past() ) {
		$notices[] = __( '<p>No previous events</p>' , 'tribe-events-calendar' ) . $is_cat_message;
	}
}


// Start list template
echo apply_filters( 'tribe_events_list_before_template', '', get_the_ID() );

	// List notices
	echo apply_filters( 'tribe_events_list_notices', $notices, $notices, get_the_ID() );
	
	// Start list loop
	echo apply_filters( 'tribe_events_list_before_loop', '', get_the_ID() );

	$hasPosts = false;

	// Does this page have posts?
	if ( have_posts() ) {
		
		$hasPosts = true;

		// Start Loop
		while ( have_posts() ) {
			
			the_post();
			global $more; 
			$more = false;

			echo apply_filters( 'tribe_events_list_inside_before_loop', '', get_the_ID() );
			
				// Event start date
				echo apply_filters( 'tribe_events_list_the_start_date', '', get_the_ID() );
			
				// Event title
				echo apply_filters( 'tribe_events_list_the_title', '', get_the_ID() );

				// Event content
				echo apply_filters( 'tribe_events_list_before_the_content', '', get_the_ID() );
				echo apply_filters( 'tribe_events_list_the_content', '', get_the_ID() );
				echo apply_filters( 'tribe_events_list_after_the_content', '', get_the_ID() );
			
				// Event meta
				echo apply_filters( 'tribe_events_list_before_the_meta', '', get_the_ID() );
				echo apply_filters( 'tribe_events_list_the_meta', '', get_the_ID() );
				echo apply_filters( 'tribe_events_list_after_the_meta', '', get_the_ID() );
		
			echo apply_filters( 'tribe_events_list_inside_after_loop', '', get_the_ID() );

		} // End list loop
	} // End if list has posts

	echo apply_filters( 'tribe_events_list_after_loop', '', get_the_ID() );
	
	// List pagination
	echo apply_filters( 'tribe_events_list_before_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_list_after_pagination', '', get_the_ID() );

// End list template
echo apply_filters( 'tribe_events_list_after_template', $hasPosts, get_the_ID() );
