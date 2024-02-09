<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet;

use DateTimeImmutable;
use TEC\Tickets\Commerce\Module;
use TEC\Tickets_Wallet_Plus\Passes\Apple_Wallet\Pass;
use Tribe__Tickets__Tickets;

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
	 * @var string
	 */
	protected $date_format = 'M j';

	/**
	 * Format for displaying the time.
	 *
	 * @var string
	 */
	protected $time_format = 'g:ia';


	/**
	 * Add the Event date into the Apple Pass `back` data.
	 *
	 * @since 6.2.8
	 * @since TBD Removed Event date from secondary array.
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

		if ( empty( $data['secondary'] ) || ! is_array( $data['secondary'] ) ) {
			$data['secondary'] = [];
		}

		if ( empty( $data['auxiliary'] ) || ! is_array( $data['auxiliary'] ) ) {
			$data['auxiliary'] = [];
		}

		// Add the event title.
		$data['secondary'][] = [
			'key'   => 'event_title',
			'label' => esc_html__( 'Event', 'the-events-calendar' ),
			'value' => $event->post_title,
		];

		return $data;
	}

	/**
	 * Helper function to format the date and time to display on the pass.
	 *
	 * @param $start
	 * @param $end
	 *
	 * @return string
	 */
	private function format_date_time_range(
		$start,
		$end
	) {
		$formatted_start = $start->format( $this->date_format ) . ' @ ' . $start->format( $this->time_format );
		$formatted_end   = $end->format( $this->date_format ) . ' @ ' . $end->format( $this->time_format );
		return $formatted_start . ' - ' . $formatted_end;
	}

	/**
	 * Add the Event Date for series.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_event_date_series(
		array $data,
		Pass  $pass
	) {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array(
			\Tribe__Events__Main::POSTTYPE,
			tribe( 'tickets.main' )->post_types(),
			true
		) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();
		// @todo Redscar - Confirm tribe_is_event_series() is the correct function to use.
		$is_series_pass = tribe_is_event_series( $event_id );

		if ( ! $is_series_pass ) {
			return $data;
		}

		$attendee  = $pass->get_attendee();
		$ticket_id = $attendee['product_id'];
		$provider  = tribe_tickets_get_ticket_provider( (int) $ticket_id );
		$ticket    = $provider->get_ticket(
			$event_id,
			$ticket_id
		);

		$start_date = ( new DateTimeImmutable() )->setTimestamp( $ticket->start_date() );
		$end_date   = ( new DateTimeImmutable() )->setTimestamp( $ticket->end_date() );

		$event_time_value = $start_date->format( $this->date_format ) . '-' . $end_date->format( $this->date_format );

		$event_dates_value = $this->format_date_time_range(
			$start_date,
			$end_date,
		);


		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => '', // No label for Series Passes.
			'value' => $event_time_value,
		];

		$data['secondary'][] = [
			'key'   => 'event_dates',
			'label' => esc_html__(
				'Event Dates',
				'the-events-calendar'
			),
			'value' => $event_dates_value,
		];

		return $data;
	}

	/**
	 * Add the Event Date for multiday events.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array Modified pass data.
	 */
	public function include_event_date_multiday(
		array $data,
		Pass $pass
	) {

		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array(
			\Tribe__Events__Main::POSTTYPE,
			tribe( 'tickets.main' )->post_types(),
			true
		) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();
		$event    = tribe_get_event( $event_id );

		$event_spans_multiple_days = $event->dates->start->format( 'Y-m-d' ) !== $event->dates->end->format( 'Y-m-d' );

		if ( ! $event_spans_multiple_days ) {
			return $data;
		}

		$event_time_value  = $event->dates->start->format( $this->date_format )
							. '-' .
							$event->dates->end->format( $this->date_format );
		$event_dates_value = $this->format_date_time_range(
			$event->dates->start,
			$event->dates->end
		);

		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => '', // No label for Series Passes.
			'value' => $event_time_value,
		];

		$data['secondary'][] = [
			'key'   => 'event_dates',
			'label' => esc_html__(
				'Event Dates',
				'the-events-calendar'
			),
			'value' => $event_dates_value,
		];


		return $data;
	}

	/**
	 * Add the Event Date for single day events.
	 *
	 * @param array $data The Apple Pass data.
	 * @param Pass  $pass The Apple Pass object.
	 *
	 * @return array
	 */
	public function include_event_date_single(
		array $data,
		Pass $pass
	): array {
		// Bail if `tribe_events` CPT is not enabled to have tickets.
		if ( ! in_array(
			\Tribe__Events__Main::POSTTYPE,
			tribe( 'tickets.main' )->post_types(),
			true
		) ) {
			return $data;
		}

		// Bail if there is no attendee.
		if ( ! $pass->attendee_exists() ) {
			return $data;
		}

		$event_id = $pass->get_event_id();
		$event    = tribe_get_event( $event_id );

		$event_spans_multiple_days = $event->dates->start->format( 'Y-m-d' ) !== $event->dates->end->format( 'Y-m-d' );

		if ( $event_spans_multiple_days ) {
			return $data;
		}

		$label            = $event->dates->start->format( $this->time_format );
		$event_time_value = $event->dates->start->format( $this->date_format );


		$data['header'][] = [
			'key'   => 'event_date_time_range',
			'label' => $label,
			'value' => $event_time_value,
		];

		return $data;
	}

	/**
	 * Add the Venue data into the Apple Pass `back` data.
	 *
	 * @since 6.2.8
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

		$data['auxiliary'][] = [
			'key'   => 'event_venue',
			'label' => esc_html__( 'Venue', 'the-events-calendar' ),
			'value' => $venue->post_title,
		];

		return $data;
	}

	/**
	 * Add event data to sample Apple Wallet pass.
	 *
	 * @since 6.2.8
	 * @since TBD removed date from secondary and moved to header.
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
}
