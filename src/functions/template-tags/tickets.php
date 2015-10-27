<?php
/**
 * Ticketing functions.
 *
 * Helpers to work with and customize ticketing-related features.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	exit( '-1' );
}


if ( ! function_exists( 'tribe_events_has_tickets' ) ) {
	/**
	 * Determines if any tickets exist for the current event (a specific event
	 * may be specified, though, by passing the post ID or post object).
	 *
	 * @param $event
	 *
	 * @return bool
	 */
	function tribe_events_has_tickets( $event = null ) {
		if ( null === ( $event = tribe_events_get_event( $event ) ) ) {
			return false;
		}

		$tickets = Tribe__Events__Tickets__Tickets::get_all_event_tickets( $event->ID );
		return ! empty( $tickets );
	}
}

if ( ! function_exists( 'tribe_events_has_soldout' ) ) {
	/**
	 * Determines if the event has sold out of tickets.
	 *
	 * Note that this will also return true if the event has no tickets
	 * whatsoever, and so it may be best to test with tribe_events_has_tickets()
	 * before using this to avoid ambiguity.
	 *
	 * @param null $event
	 *
	 * @return bool
	 */
	function tribe_events_has_soldout( $event = null ) {
		$has_tickets = tribe_events_has_tickets( $event );
		$no_stock = tribe_events_count_available_tickets( $event ) < 1;
		$unlimited_inventory_items = tribe_events_has_unlimited_stock_tickets( $event );

		return ( $has_tickets && $no_stock && ! $unlimited_inventory_items );
	}
}//end if

if ( ! function_exists( 'tribe_events_partially_soldout' ) ) {
	/**
	 * Indicates if one or more of the tickets available for this event (but not
	 * all) have sold out.
	 *
	 * This is useful to indicate if for example 2 out of three ticket types
	 * have soldout but one still has stock remaining.
	 *
	 * @param null $event
	 *
	 * @return bool
	 */
	function tribe_events_partially_soldout( $event = null ) {
		if ( null === ( $event = tribe_events_get_event( $event ) ) ) {
			return false;
		}

		$stock_is_available = false;
		$some_have_soldout = false;

		foreach ( Tribe__Events__Tickets__Tickets::get_all_event_tickets( $event->ID ) as $ticket ) {
			if ( ! $stock_is_available && 0 < $ticket->stock ) {
				$stock_is_available = true;
			}

			if ( ! $some_have_soldout && 0 == $ticket->stock ) {
				$some_have_soldout = true;
			}
		}

		return $some_have_soldout && $stock_is_available;
	}
}//end if

if ( ! function_exists( 'tribe_events_count_available_tickets' ) ) {
	/**
	 * Counts the total number of tickets still available for sale for a
	 * specific event.
	 *
	 * @param null $event
	 *
	 * @return int
	 */
	function tribe_events_count_available_tickets( $event = null ) {
		$count = 0;

		if ( null === ( $event = tribe_events_get_event( $event ) ) ) {
			return 0;
		}

		foreach ( Tribe__Events__Tickets__Tickets::get_all_event_tickets( $event->ID ) as $ticket ) {
			$count += $ticket->stock;
		}

		return $count;
	}
}//end if

if ( ! function_exists( 'tribe_events_has_unlimited_stock_tickets' ) ) {
	/**
	 * Returns true if the event contains one or more tickets which are not
	 * subject to any inventory limitations.
	 *
	 * @param null $event
	 *
	 * @return bool
	 */
	function tribe_events_has_unlimited_stock_tickets( $event = null ) {
		if ( null === ( $event = tribe_events_get_event( $event ) ) ) {
			return 0;
		}

		foreach ( Tribe__Events__Tickets__Tickets::get_all_event_tickets( $event->ID ) as $ticket ) {
			if ( Tribe__Events__Tickets__Ticket_Object::UNLIMITED_STOCK === $ticket->stock ) return true;
		}

		return false;
	}
}//end if

if ( ! function_exists( 'tribe_events_product_is_ticket' ) ) {
	/**
	 * Determines if the product object (or product ID) represents a ticket for
	 * an event.
	 *
	 * @param $product
	 *
	 * @return bool
	 */
	function tribe_events_product_is_ticket( $product ) {
		$matching_event = tribe_events_get_ticket_event( $product );
		return ( false !== $matching_event );
	}
}//end if

if ( ! function_exists( 'tribe_events_get_ticket_event' ) ) {
	/**
	 * Accepts the post object or ID for a product and, if it represents an event
	 * ticket, returns the corresponding event object.
	 *
	 * If this cannot be determined boolean false will be returned instead.
	 *
	 * @param $possible_ticket
	 *
	 * @return bool|WP_Post
	 */
	function tribe_events_get_ticket_event( $possible_ticket ) {
		return Tribe__Events__Tickets__Tickets::find_matching_event( $possible_ticket );
	}
}//end if

if ( ! function_exists( 'tribe_events_ticket_is_on_sale' ) ) {
	/**
	 * Checks if the ticket is on sale (in relation to it's start/end sale dates).
	 *
	 * @param Tribe__Events__Tickets__Ticket_Object $ticket
	 *
	 * @return bool
	 */
	function tribe_events_ticket_is_on_sale( Tribe__Events__Tickets__Ticket_Object $ticket ) {
		// No dates set? Then it's on sale!
		if ( empty( $ticket->start_date ) && empty( $ticket->end_date ) ) {
			return true;
		}

		// Timestamps for comparison purposes
		$now    = time();
		$start  = strtotime( $ticket->start_date );
		$finish = strtotime( $ticket->end_date );

		// Are we within the applicable date range?
		$has_started = ( empty( $ticket->start_date ) || ( $start && $now > $start ) );
		$not_ended   = ( empty( $ticket->end_date ) || ( $finish && $now < $finish ) );

		// Result
		return ( $has_started && $not_ended );
	}
}//end if
