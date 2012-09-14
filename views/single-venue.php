<?php
/**
 * Single Venue Template
 * The template for a venue. By default it displays venue information and lists 
 * events that occur at the specified venue.
 *
 * This view contains the filters required to create an effective single venue view.
 *
 * You can recreate an ENTIRELY new single venue view by doing a template override, and placing
 * a single-venue.php file in a tribe-events/ directory within your theme directory, which
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

// start single venue template
echo apply_filters( 'tribe_events_single_venue_before_template', '', get_the_ID() );

	// start single venue
	echo apply_filters( 'tribe_events_single_venue_before_venue', '', get_the_ID() );
	
		// venue map
		echo apply_filters( 'tribe_events_single_venue_map', '', get_the_ID() );
		
		// venue meta
		echo apply_filters( 'tribe_events_single_venue_before_the_meta', '', get_the_ID() );
		echo apply_filters( 'tribe_events_single_venue_the_meta', '', get_the_ID() );
		echo apply_filters( 'tribe_events_single_venue_after_the_meta', '', get_the_ID() );

	// end single venue
	echo apply_filters( 'tribe_events_single_venue_after_venue', '', get_the_ID() );
	
	// start upcoming event loop
	echo apply_filters( 'tribe_events_single_venue_before_loop', '', get_the_ID() );
	
	$venue_id = get_the_id();
	$venueEvents = tribe_get_events( array( 'venue'=>get_the_ID(), 'eventDisplay' => 'upcoming', 'posts_per_page' => -1 ) ); 
 	global $post; 
 	$first = true;
 	
 	if( sizeof( $venueEvents ) > 0 ) { // if we have other venues
		
		// venue loop title
		echo apply_filters( 'tribe_events_single_venue_loop_title', '', get_the_ID() );

		foreach( $venueEvents as $post ): setup_postdata( $post ); // our venue loop

			echo apply_filters( 'tribe_events_single_venue_inside_before_loop', '', get_the_ID() );
			
				// event start date
				echo apply_filters( 'tribe_events_single_venue_the_start_date', '', get_the_ID() );
			
				// event title
				echo apply_filters( 'tribe_events_single_venue_the_title', '', get_the_ID() );

				// event content
				echo apply_filters( 'tribe_events_single_venue_before_the_content', '', get_the_ID() );
				echo apply_filters( 'tribe_events_single_venue_the_content', '', get_the_ID() );
				echo apply_filters( 'tribe_events_single_venue_after_the_content', '', get_the_ID() );
			
				// event meta
				echo apply_filters( 'tribe_events_single_venue_before_the_meta', '', get_the_ID() );
				echo apply_filters( 'tribe_events_single_venue_the_meta', '', get_the_ID() );
				echo apply_filters( 'tribe_events_single_venue_after_the_meta', '', get_the_ID() );
		
			echo apply_filters( 'tribe_events_single_venue_inside_after_loop', '', get_the_ID() );
			
		endforeach;	// end our venue loop					
 	} // end if have other venues
 	
 	// Reset the post and id to the venue post before comments template shows up.
 	$post = get_post($venue_id); 
 	global $id;
	$id = $venue_id;
	
	// end upcoming event loop
	echo apply_filters( 'tribe_events_single_venue_after_loop', '', get_the_ID() );
	
// end single venue template
echo apply_filters( 'tribe_events_single_venue_after_template', '', get_the_ID() );
