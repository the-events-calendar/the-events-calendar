<?php
/**
 * Address Module Template
 * Render an address. This is used by default in the single event view.
 *
 * This view contains the hooks and filters required to create an effective address view.
 *
 * You can recreate and ENTIRELY new address view (that does not utilize these hooks and filters)
 * by doing a template override, and placing a address.php file in a tribe-events/modules/ directory 
 * within your theme directory, which will override the /views/modules/address.php.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

// start address template
echo apply_filters( 'tribe_events_address_before_template', '', get_the_ID() );

		// address meta
		echo apply_filters( 'tribe_events_address_before_the_meta', '', get_the_ID() );
		echo apply_filters( 'tribe_events_address_the_meta', '', get_the_ID() );
		echo apply_filters( 'tribe_events_address_after_the_meta', '', get_the_ID() );

	

// end address template
echo apply_filters( 'tribe_events_address_after_template', '', get_the_ID() );