<?php
/**
 * Single Venue Template
 * The template for a venue. By default it displays venue information and lists 
 * events that occur at the specified venue.
 *
 * This view contains the filters required to create an effective single venue view.
 *
 * You can recreate an ENTIRELY new single venue view by doing a template override, and placing
 * a single-venue.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/single-venue.php. 
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

$venue_id = get_the_ID();

// Start single venue template
echo apply_filters( 'tribe_events_single_venue_before_template', '', $venue_id );

	// Start single venue
	echo apply_filters( 'tribe_events_single_venue_before_venue', '', $venue_id );
	
		// Venue map
		echo apply_filters( 'tribe_events_single_venue_map', '', $venue_id );
		
		// Venue meta
		echo apply_filters( 'tribe_events_single_venue_before_the_meta', '', $venue_id );
		echo apply_filters( 'tribe_events_single_venue_the_meta', '', $venue_id );
		echo apply_filters( 'tribe_events_single_venue_after_the_meta', '', $venue_id );

	// End single venue
	echo apply_filters( 'tribe_events_single_venue_after_venue', '', $venue_id );
	
	// Start upcoming event loop
	echo apply_filters( 'tribe_events_single_venue_event_before_loop', '', $venue_id );
	
	
	$venueEvents = tribe_get_events( array( 'venue'=>$venue_id, 'eventDisplay' => 'upcoming', 'posts_per_page' => -1 ) ); 
 	global $post; 
 	
 	if( sizeof( $venueEvents ) > 0 ) { // If we have other venues
		
		// Venue loop title
		echo apply_filters( 'tribe_events_single_venue_event_loop_title', '', $venue_id );

		foreach( $venueEvents as $event ){ // Setup_postdata( $post ); // Our venue loop

			echo apply_filters( 'tribe_events_single_venue_event_inside_before_loop', $venue_id, $event );
			
				// Event start date
				echo apply_filters( 'tribe_events_single_venue_event_the_start_date', $venue_id, $event );
			
				// Event title
				echo apply_filters( 'tribe_events_single_venue_event_the_title', $venue_id, $event );

				// Event content
				echo apply_filters( 'tribe_events_single_venue_event_before_the_content', $venue_id, $event );
				echo apply_filters( 'tribe_events_single_venue_event_the_content', $venue_id, $event );
				echo apply_filters( 'tribe_events_single_venue_event_after_the_content', $venue_id, $event );
			
				// Event meta
				echo apply_filters( 'tribe_events_single_venue_event_before_the_meta', $venue_id, $event );
				echo apply_filters( 'tribe_events_single_venue_event_the_meta', $venue_id, $event );
				echo apply_filters( 'tribe_events_single_venue_event_after_the_meta', $venue_id, $event );
		
			echo apply_filters( 'tribe_events_single_venue_event_inside_after_loop', $venue_id, $event );
			
		}	// End our venue loop					
 	} // End if have other venues
 	
 	// Reset the post and id to the venue post before comments template shows up.
 	$post = get_post($venue_id); 
 	global $id;
	$id = $venue_id;
	
	// End upcoming event loop
	echo apply_filters( 'tribe_events_single_venue_event_after_loop', '', $venue_id );
	
// End single venue template
echo apply_filters( 'tribe_events_single_venue_after_template', '', $venue_id );
