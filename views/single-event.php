<?php

/**
 * The abstracted view of a single event.
 * This view contains the hooks and filters required to create an effective single event view.
 *
 * You can recreate and ENTIRELY new single view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a single-event.php file in a /tribe-events/ directory 
 * within your theme directory, which will override this file /events/views/single-event.php.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

// start single template
apply_filters( 'tribe_events_single_event_before_template', '', get_the_ID() );

	// event notice
	apply_filters( 'tribe_events_single_event_notices', '', get_the_ID() );

	// event meta
	apply_filters( 'tribe_events_single_event_before_the_meta', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_the_meta', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_after_the_meta', '', get_the_ID() );

	// event map
	apply_filters( 'tribe_events_single_event_the_map', '', get_the_ID() );

	// event content
	apply_filters( 'tribe_events_single_event_before_the_content', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_the_content', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_after_the_content', '', get_the_ID() );

	// event pagination
	apply_filters( 'tribe_events_single_event_before_pagination', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_pagination', '', get_the_ID() );
	apply_filters( 'tribe_events_single_event_after_pagination', '', get_the_ID() );

// end single template
apply_filters( 'tribe_events_single_event_after_template', '', get_the_ID() );