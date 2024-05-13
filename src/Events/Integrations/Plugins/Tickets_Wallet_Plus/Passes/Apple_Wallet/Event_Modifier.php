<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet;

use DateTimeImmutable;
use TEC\Tickets\Flexible_Tickets\Series_Passes\Series_Passes;
use TEC\Tickets_Wallet_Plus\Passes\Apple_Wallet\Pass;
use Tribe__Date_Utils;

/**
 * Class Event_Modifier
 *
 * @since   6.2.8
 *
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet
 */
class Event_Modifier {

	/**
	 * Format for displaying the Date
	 *
	 * @since 6.3.2
	 *
	 * @var string
	 */
	protected $date_format = 'M j';

	/**
	 * Format for displaying the time.
	 *
	 * @since 6.3.2
	 *
	 * @var string
	 */
	protected $time_format = 'g:ia';


	/**
	 * Add the Event date into the Apple Pass `back` data.
	 *
	 * @since 6.2.8
	 * @since 6.3.2 Removed Event date from secondary array.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_event_data( array $data, Pass $pass ): array {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, tribe( 'tickets.main' )->post_types(), true ) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();

		// Get the event.
		$event = tribe_get_event( $event_id );

		// Bail if it's empty or if the ticket is from a page/post or any other CPT with tickets.
		if ( empty( $event ) || ! in_array( $event->post_type, tribe( 'tickets.main' )->post_types() ) ) {
			return $data;
		}

		// Add the event title.
		$data['primary'][] = [
			'key'   => 'event_title',
			'label' => '',
			'value' => $event->post_title,
		];

		return $data;
	}

	/**
	 * Helper function to format the date and time to display on the pass.
	 *
	 * @since 6.3.2
	 *
	 * @param DateTimeImmutable $start The start date and time.
	 * @param DateTimeImmutable $end The end date and time.
	 *
	 * @return string
	 */
	protected function format_date_time_range( DateTimeImmutable $start, DateTimeImmutable $end ): string {
		$formatted_start = $start->format( $this->get_date_format() ) . ' @ ' . $start->format( $this->get_time_format() );
		$formatted_end   = $end->format( $this->get_date_format() ) . ' @ ' . $end->format( $this->get_time_format() );
		return $formatted_start . ' - ' . $formatted_end;
	}

	/**
	 * Add the Event Date for series.
	 *
	 * @since 6.3.2
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_event_date_series( array $data, Pass $pass ) {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, tribe( 'tickets.main' )->post_types(), true ) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id  = $pass->get_event_id();
		$attendee  = $pass->get_attendee();
		$ticket_id = $attendee['product_id'];
		$provider  = tribe_tickets_get_ticket_provider( (int) $ticket_id );
		$ticket    = $provider->get_ticket( $event_id, $ticket_id );

		if ( Series_Passes::TICKET_TYPE !== $ticket->type() ) {
			return $data;
		}

		$start_date        = ( Tribe__Date_Utils::immutable() )->setTimestamp( $ticket->start_date() );
		$end_date          = ( Tribe__Date_Utils::immutable() )->setTimestamp( $ticket->end_date() );
		$event_time_value  = $start_date->format( $this->get_date_format( 'event_date_time_range_value' ) ) . '-' . $end_date->format( $this->get_date_format( 'event_date_time_range_value' ) );
		$event_dates_value = $this->format_date_time_range( $start_date, $end_date, );

		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => '', // No label for Series Passes.
			'value' => $event_time_value,
		];

		$data['back'][] = [
			'key'   => 'event_dates',
			'label' => esc_html__( 'Event Dates', 'the-events-calendar' ),
			'value' => $event_dates_value,
		];

		$data = $pass->replace_label_by_key( $data, 'back', 'ticket_title', esc_html__( 'Series Pass', 'the-events-calendar' ) );
		$data = $pass->replace_label_by_key( $data, 'auxiliary', 'ticket_title', esc_html__( 'Series Pass', 'the-events-calendar' ) );

		return $data;
	}

	/**
	 * Add the Event Date for multiday events.
	 *
	 * @since 6.3.2
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_event_date_multiday( array $data, Pass $pass ) {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, tribe( 'tickets.main' )->post_types(), true ) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();
		$event    = tribe_get_event( $event_id );

		if ( is_null( $event->multiday ) || $event->multiday <= 1 ) {
			return $data;
		}

		$event_time_value  = $event->dates->start->format( $this->get_date_format( 'event_date_time_range_value' ) ) . '-' . $event->dates->end->format( $this->get_date_format( 'event_date_time_range_value' ) );
		$event_dates_value = $this->format_date_time_range( $event->dates->start, $event->dates->end );

		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => '',
			'value' => $event_time_value,
		];

		$data['back'][] = [
			'key'   => 'event_date_time_range',
			'label' => esc_html__( 'Event Dates', 'the-events-calendar' ),
			'value' => $event_dates_value,
		];

		return $data;
	}

	/**
	 * Add the Event Date for single day events.
	 *
	 * @since 6.3.2
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array
	 */
	public function include_event_date_single( array $data, Pass $pass ): array {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, tribe( 'tickets.main' )->post_types(), true ) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();
		$event    = tribe_get_event( $event_id );

		if ( $event->multiday > 1 || 'tribe_event_series' === $event->post_type ) {
			return $data;
		}

		$label            = $event->dates->start->format( $this->get_time_format( 'event_date_time_range_label' ) );
		$event_time_value = $event->dates->start->format( $this->get_date_format( 'event_date_time_range_value' ) );


		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => $label,
			'value' => $event_time_value,
		];

		$data['back'][] = [
			'key'   => 'event_date_time_range',
			'label' => 'Event Dates',
			'value' => tribe_get_start_date( $event_id ),
		];

		return $data;
	}

	/**
	 * Add the Venue data into the Apple Pass `back` data.
	 *
	 * @since 6.2.8
	 * @since 6.3.2 Added Location to the back of the pass.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_venue_data( array $data, Pass $pass ): array {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, tribe( 'tickets.main' )->post_types(), true ) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();

		// Get the event.
		$event = tribe_get_event( $event_id );

		if ( empty( $event->venues->count() ) ) {
			return $data;
		}

		$venue = $event->venues[0];

		$venue_location_parts = [];

		// Concatenate address and city without a comma, only add if non-empty.
		if ( ! empty( $venue->address ) || ! empty( $venue->city ) ) {
			$venue_location_parts[] = trim( $venue->address . ' ' . $venue->city );
		}

		// Append zip and state if they are not empty, with commas as appropriate.
		if ( ! empty( $venue->zip ) ) {
			$venue_location_parts[] = $venue->zip;
		}
		if ( ! empty( $venue->state ) ) {
			$venue_location_parts[] = $venue->state;
		}

		// Combine the parts into a string, separating by a comma only between zip and state.
		$venue_location = implode( ', ', $venue_location_parts );
		if ( ! empty( $venue_location_parts ) ) {
			$data['back'][] = [
				'key'   => 'venue_location',
				'label' => esc_html__( 'Location', 'the-events-calendar' ),
				'value' => $venue_location,
			];
		}

		return $data;
	}

	/**
	 * Add event data to sample Apple Wallet pass.
	 *
	 * @since 6.2.8
	 * @since 6.3.2 removed date from secondary and moved to header.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array
	 */
	public function add_event_data_to_sample( array $data, Pass $pass ) {
		// Add the event title.
		$data['secondary'][] = [
			'key'   => 'event_title',
			'label' => esc_html__( 'Event', 'the-events-calendar' ),
			'value' => esc_html__( 'Arts in the Park', 'the-events-calendar' ),
		];

		// Add the event start date.
		$data['secondary'][] = [
			'dateStyle'  => 'PKDateStyleMedium',
			'isRelative' => true,
			'key'        => 'event_start_date',
			'label'      => esc_html__( 'Date', 'the-events-calendar' ),
			'timeStyle'  => 'PKDateStyleShort',
			'value'      => gmdate( 'Y-m-d\TH:iP' ),
		];

		// Add the event venue.
		$data['auxiliary'][] = [
			'key'   => 'event_venue',
			'label' => esc_html__( 'Venue', 'the-events-calendar' ),
			'value' => esc_html__( 'Central Park', 'the-events-calendar' ),
		];

		return $data;
	}

	/**
	 * Gets the filtered date format.
	 *
	 * @since 6.3.2
	 *
	 * @param string|null $location Location where the date formatting is used.
	 *
	 * @return string Filtered date format.
	 */
	protected function get_date_format( string $location = null ): string {
		/**
		 * Filters the date format used in the Apple Wallet passes.
		 *
		 * @since 6.3.2
		 *
		 * @param string $date_format The current date format. Default is `M j`.
		 * @param null|string $location Location where the date formatting is used.
		 *
		 * @return string The filtered date format.
		 */
		return apply_filters( 'tec_tickets_wallet_plus_apple_wallet_date_format', $this->date_format, $location );
	}

	/**
	 * Gets the filtered time format.
	 *
	 * @since 6.3.2
	 *
	 * @param string|null $location Location where the time formatting is used.
	 *
	 * @return string Filtered time format.
	 */
	protected function get_time_format( string $location = null ): string {
		/**
		 * Filters the time format used in the Apple Wallet passes.
		 *
		 * @since 6.3.2
		 *
		 * @param string $time_format The current time format. Default is `g:ia`.
		 * @param null|string $location Location where the time formatting is used.
		 *
		 * @return string The filtered time format.
		 */
		return apply_filters( 'tec_tickets_wallet_plus_apple_wallet_time_format', $this->time_format, $location );
	}

}
