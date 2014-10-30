<?php

/**
 * Integrates the ticketing framework with our Google Event Data functionality.
 *
 * @see Tribe__Events__Google_Data_Markup
 */
class Tribe__Events__Tickets__Google_Event_Data {
	/**
	 * @var stdClass
	 */
	protected $event;

	/**
	 * @var int
	 */
	protected $event_id;

	/**
	 * @var TribeEventsTicketObject
	 */
	protected $ticket;

	/**
	 * Value indicating low stock availability for a specific ticket.
	 *
	 * This can be overridden with the tribe_events_tickets_google_low_inventory_level filter.
	 *
	 * @var int
	 */
	protected $low_stock = 5;


	/**
	 * Setup Google Event Data for tickets.
	 */
	public function __construct() {
		$this->low_stock = apply_filters( 'tribe_events_tickets_google_low_inventory_level', $this->low_stock );
		add_filter( 'tribe_google_event_data', array( $this, 'add_ticket_data' ) );
	}

	/**
	 * Accepts an array of objects used to generate Google Event Data and adds
	 * "offers" data representing tickets where appropriate.
	 *
	 * @param array $events_data
	 *
	 * @return array
	 */
	public function add_ticket_data( array $events_data ) {
		foreach ( $events_data as $this->event_id => &$this->event ) {
			if ( ! tribe_events_has_tickets( $this->event_id ) ) continue;
			$this->add_ticket_offers();
		}

		return $events_data;
	}

	/**
	 * Adds ticket data to the offers property of the event object.
	 */
	protected function add_ticket_offers() {
		foreach ( TribeEventsTickets::get_all_event_tickets( $this->event_id ) as $this->ticket ) {
			$this->add_individual_offer();
		}
	}

	/**
	 * Adds an individual ticket to the offers property.
	 */
	protected function add_individual_offer() {
		// Create the offers property if it does not already exist
		if ( ! isset( $this->event->offers ) ) $this->event->offers = array();

		// Add an offer for the current event
		$this->event->offers[] = $this->build_offer();
	}

	/**
	 * Builds an object representing a ticket offer.
	 *
	 * @return object
	 */
	protected function build_offer() {
		$offer = array(
			'@type'        => 'Offer',
			'url'          => $this->ticket->frontend_link,
			'price'        => $this->ticket->price,
			'category'     => 'primary',
			'availability' => $this->get_ticket_availability(),
		);

		$this->add_time_restrictions( $offer );

		/**
		 * Allows modifications to be made to the offer object representing a specific
		 * event ticket.
		 *
		 * @param object                  $offer
		 * @param TribeEventsTicketObject $ticket
		 * @param object                  $event
		 */
		return (object) apply_filters( 'tribe_events_tickets_google_event_data_offer', (object) $offer, $this->ticket, $this->event );
	}

	/**
	 * Returns a string indicating current availability of the ticket.
	 *
	 * @return string
	 */
	protected function get_ticket_availability() {
		$stock = $this->ticket->stock;

		if ( $stock <= 0 && $stock !== '' ) {
			return 'SoldOut';
		}
		if ( $stock > 1 && $stock <= $this->low_stock ) {
			return 'LimitedAvailability';
		}
		else {
			return 'InStock';
		}
	}

	/**
	 * Adds validFrom and validThrough fields if appropriate.
	 *
	 * @param array &$offer
	 */
	protected function add_time_restrictions( array &$offer ) {
		if ( ! empty( $this->ticket->start_date ) ) {
			$offer['validFrom'] = date( DateTime::ISO8601, strtotime( $this->ticket->start_date ) );
		}

		if ( ! empty( $this->ticket->end_date ) ) {
			$offer['validThrough'] = date( DateTime::ISO8601, strtotime( $this->ticket->end_date ) );
		}
	}
}