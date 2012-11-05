<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and 
 * optionally, the Google map for the event.
 *
 * This view contains the filters required to create an effective single event view.
 *
 * You can recreate an ENTIRELY new single event view by doing a template override, and placing
 * a single-event.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/single-event.php.
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
if ( !defined('ABSPATH') ) { die('-1'); }

	// Check if event has passed
	$notices = empty($notices) ? array() : $notices; 
	$gmt_offset = (get_option('gmt_offset') >= '0' ) ? ' +' . get_option('gmt_offset') : " " . get_option('gmt_offset');
 	$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
 	if ( strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) { ?>
 		<div class="event-passed">
 			<?php $notices[] = __('This event has passed.', 'tribe-events-calendar'); ?>
 		</div>
<?php } 

	// Check if event has passed
	$notices = empty($notices) ? array() : $notices; 
	$gmt_offset = (get_option('gmt_offset') >= '0' ) ? ' +' . get_option('gmt_offset') : " " . get_option('gmt_offset');
 	$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
 	if ( strtotime( tribe_get_end_date( get_the_ID(), false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) { ?>
 		<div class="event-passed">
 			<?php $notices[] = __('This event has passed.', 'tribe-events-calendar'); ?>
 		</div>
<?php } ?>
<?php // Start single template
echo apply_filters( 'tribe_events_single_event_before_template', '', get_the_ID() );

	// Event notice
	echo apply_filters( 'tribe_events_single_event_notices', $notices, get_the_ID() );
	
	// Event content
	echo apply_filters( 'tribe_events_single_event_before_the_content', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_the_content', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_after_the_content', '', get_the_ID() );

	// Event meta
	echo apply_filters( 'tribe_events_single_event_before_the_meta', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_the_meta', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_after_the_meta', '', get_the_ID() );	
		
	// Event pagination
	echo apply_filters( 'tribe_events_single_event_before_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_pagination', '', get_the_ID() );
	echo apply_filters( 'tribe_events_single_event_after_pagination', '', get_the_ID() );

	echo apply_filters( 'tribe_events_single_event_the_comments', '', get_the_ID() );
	
// End single template
echo apply_filters( 'tribe_events_single_event_after_template', '', get_the_ID() );
