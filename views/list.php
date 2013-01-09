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

$the_post_id = ( have_posts() ) ? get_the_ID() : null;

// Start list template
echo apply_filters( 'tribe_events_list_before_template', '', $the_post_id );
	
	echo apply_filters( 'tribe_events_list_the_title', '', $the_post_id );

	// List notices
	echo apply_filters( 'tribe_events_list_notices', $the_post_id );
	
	// List header
    echo apply_filters( 'tribe_events_list_before_header', '');

    	// Navigation
    	echo apply_filters( 'tribe_events_list_before_header_nav', '');
		echo apply_filters( 'tribe_events_list_header_nav', '');
		echo apply_filters( 'tribe_events_list_after_header_nav', '');

	echo apply_filters( 'tribe_events_list_after_header', '');

	// Start list loop
	echo apply_filters( 'tribe_events_list_before_loop', '', $the_post_id );

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
							
				// Event image
				echo apply_filters( 'tribe_events_list_the_event_image', '', get_the_ID() );
				
					// Event details start
					echo apply_filters( 'tribe_events_list_before_the_event_details', '', get_the_ID() );

					// Event title
					echo apply_filters( 'tribe_events_list_the_event_title', '', get_the_ID() );

					// Event meta
					echo apply_filters( 'tribe_events_list_before_the_meta', '', get_the_ID() );
					echo apply_filters( 'tribe_events_list_the_meta', '', get_the_ID() );
					echo apply_filters( 'tribe_events_list_after_the_meta', '', get_the_ID() );

					// Event content
					echo apply_filters( 'tribe_events_list_before_the_content', '', get_the_ID() );
					echo apply_filters( 'tribe_events_list_the_content', '', get_the_ID() );
					echo apply_filters( 'tribe_events_list_after_the_content', '', get_the_ID() );

				// Event details end
				echo apply_filters( 'tribe_events_list_after_the_event_details', '', get_the_ID() );				
			
			echo apply_filters( 'tribe_events_list_inside_after_loop', '', get_the_ID() );


		} // End list loop
	} // End if list has posts

	echo apply_filters( 'tribe_events_list_after_loop', '', $the_post_id );
	
	// List footer
    echo apply_filters( 'tribe_events_list_before_footer', '');

    	// Navigation
    	echo apply_filters( 'tribe_events_list_before_footer_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_list_footer_nav', '', $the_post_id );
		echo apply_filters( 'tribe_events_list_after_footer_nav', '', $the_post_id );

	echo apply_filters( 'tribe_events_list_after_footer', '');
	
	// List pagination
	/*
	echo apply_filters( 'tribe_events_list_before_pagination', '', $the_post_id );
	echo apply_filters( 'tribe_events_list_pagination', '', $the_post_id );
	echo apply_filters( 'tribe_events_list_after_pagination', '', $the_post_id );
	*/

// End list template
echo apply_filters( 'tribe_events_list_after_template', $hasPosts, $the_post_id );
