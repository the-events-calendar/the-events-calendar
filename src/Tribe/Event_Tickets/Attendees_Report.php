<?php
/**
 * The Events Calendar integration with Event Tickets Attendees Report class
 *
 * @package The Events Calendar
 * @subpackage Event Tickets
 * @since 4.0.1
 */
class Tribe__Events__Event_Tickets__Attendees_Report {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds hooks for injecting/overriding aspects of the Attendees Report from Event Tickets
	 *
	 * @since 4.0.1
	 */
	public function add_hooks() {
		add_action( 'tribe_tickets_attendees_event_details_list_top', [ $this, 'event_details_top' ] );
		add_action( 'tribe_tickets_plus_report_event_details_list_top', [ $this, 'event_details_top' ] );
	}

	/**
	 * Injects event meta data into the Attendees report
	 *
	 * @param int $event_id
	 */
	public function event_details_top( $event_id ) {
		$post_type = get_post_type( $event_id );

		if ( Tribe__Events__Main::POSTTYPE === $post_type ) {
			echo '
				<li>
					<strong>' . esc_html__( 'Event Date:', 'the-events-calendar' ) . '</strong>
					' . tribe_get_start_date( $event_id, false, tribe_get_date_format( true ) ) . '
				</li>
			';
		}

		if ( tribe_has_venue( $event_id ) ) {
			$venue_id = tribe_get_venue_id( $event_id );

			/**
			 * Allows for control of the specific "edit post" URLs used for venue.
			 *
			 * @since 4.6.13
			 *
			 * @param string $link     The default "edit post" URL.
			 * @param int    $venue_id The Post ID of the venue.
			 */
			$edit_venue_link = apply_filters( 'tribe_tickets_venue_action_links_edit_url', get_edit_post_link( $venue_id ), $venue_id );

			echo '
				<li class="venue-name">
					<strong>' . tribe_get_venue_label_singular() . ': </strong>
					<a href="' . esc_url( $edit_venue_link ) . '" title="' . esc_html__( 'Edit Venue', 'the-events-calendar' ) . '">' . tribe_get_venue( $event_id ) . '</a>
				</li>
			';
		}
	}
}
