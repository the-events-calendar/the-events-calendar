<?php
/**
 * Buttons Module Template
 * Render the button group.
 *
 * This view contains the filters required to create an effective buttons module view.
 *
 * You can recreate an ENTIRELY new buttons module by doing a template override, and placing
 * a address.php file in a tribe-events/modules/ directory within your theme directory, which
 * will override the /views/modules/buttons.php. 
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

// Start address template
echo apply_filters( 'tribe_events_buttons_before_template', '' );

	// Address meta
	echo apply_filters( 'tribe_events_buttons_before_the_buttons', '' );
	echo apply_filters( 'tribe_events_buttons_the_buttons', '' );
	echo apply_filters( 'tribe_events_buttons_after_the_buttons', '' );

// End address template
echo apply_filters( 'tribe_events_buttons_after_template', '' );