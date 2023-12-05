<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet;

use TEC\Tickets_Wallet_Plus\Passes\Apple_Wallet\Pass;

/**
 * Class Event_Modifier
 *
 * @since   6.2.8
 *
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet
 */
class Event_Modifier {

	/**
	 * Add the Event date into the Apple Pass `back` data.
	 *
	 * @since 6.2.8
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
		if (  ! $pass->attendee_exists() ) {
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

		// Add the event start date.
		$data['secondary'][] = [
			'dateStyle'  => 'PKDateStyleMedium',
			'isRelative' => true,
			'key'        => 'event_start_date',
			'label'      => esc_html__( 'Date', 'the-events-calendar' ),
			'timeStyle'  => 'PKDateStyleShort',
			'value'      => $event->dates->start->format( 'Y-m-d\TH:iP' ),
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
		if (  ! $pass->attendee_exists() ) {
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
