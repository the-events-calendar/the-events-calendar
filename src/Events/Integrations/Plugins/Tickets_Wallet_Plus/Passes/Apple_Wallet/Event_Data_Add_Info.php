<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet;

/**
 * Class Event_Data_Add_Info
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus\Passes\Apple_Wallet
 */
class Event_Data_Add_Info {

	/**
	 * Add the Event date into the Apple Pass `back` data.
	 *
	 * @since TBD
	 *
	 * @param array $pass_data The existing pass data.
	 * @param array $attendee  The attendee information.
	 *
	 * @return array Modified pass data.
	 */
	public function add_event_date_to_apple_pass_data( array $pass_data, array $attendee ): array {

		if ( ! tec_tickets_tec_events_is_active() ) {
			return $pass_data;
		}

		// Bail if `tribe_events` CPT is not enabled to have tickets.
		$enabled_post_types = (array) tribe_get_option( 'ticket-enabled-post-types', [] );
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, $enabled_post_types, true ) ) {
			return $pass_data;
		}

		$event_id = $attendee['post_id'];

		// Get the event.
		$event = tribe_get_event( $event_id );

		// Bail if it's empty or if the ticket is from a page/post or any other CPT with tickets.
		if ( empty( $event ) || $event->post_type !== \Tribe__Events__Main::POSTTYPE ) {
			return $pass_data;
		}

		if ( empty( $pass_data['secondary'] ) || ! is_array( $pass_data['secondary'] ) ) {
			$pass_data['secondary'] = [];
		}

		if ( empty( $pass_data['auxiliary'] ) || ! is_array( $pass_data['auxiliary'] ) ) {
			$pass_data['auxiliary'] = [];
		}


		// Add the event title.
		$pass_data['secondary'][] = [
			'key'   => 'event_title',
			'label' => esc_html__( 'Event', 'event-tickets-wallet-plus' ),
			'value' => $event->post_title,
		];

		// Add the event start date.
		$pass_data['secondary'][] = [
			'dateStyle'  => 'PKDateStyleMedium',
			'isRelative' => true,
			'key'        => 'event_start_date',
			'label'      => esc_html__( 'Date', 'event-tickets-wallet-plus' ),
			'timeStyle'  => 'PKDateStyleShort',
			'value'      => $event->dates->start->format( 'Y-m-d\TH:iP' ),
		];

		return $pass_data;
	}

	/**
	 * Add the Venue data into the Apple Pass `back` data.
	 *
	 * @since TBD
	 *
	 * @param array $pass_data The existing pass data.
	 * @param array $attendee  The attendee information.
	 *
	 * @return array Modified pass data.
	 */
	public function add_venue_to_apple_pass_data( array $pass_data, array $attendee ): array {
		if ( ! tec_tickets_tec_events_is_active() ) {
			return $pass_data;
		}

		// Bail if `tribe_events` CPT is not enabled to have tickets.
		$enabled_post_types = (array) tribe_get_option( 'ticket-enabled-post-types', [] );
		if ( ! in_array( \Tribe__Events__Main::POSTTYPE, $enabled_post_types, true ) ) {
			return $pass_data;
		}

		$event_id = $attendee['post_id'];

		// Get the event.
		$event = tribe_get_event( $event_id );

		if ( $event->venues->count() ) {
			$venue = $event->venues[0];

			$pass_data['auxiliary'][] = [
				'key'   => 'event_venue',
				'label' => esc_html__( 'Venue', 'event-tickets-wallet-plus' ),
				'value' => $venue->post_title,
			];
		}

		return $pass_data;
	}
}