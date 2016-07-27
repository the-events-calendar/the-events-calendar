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
		add_action( 'tribe_tickets_attendees_do_event_action_links', array( $this, 'event_action_links' ) );
		add_action( 'tribe_tickets_attendees_event_details_list_top', array( $this, 'event_details_top' ) );
	}

	/**
	 * Injects action links into the attendee screen.
	 *
	 * @param $event_id
	 */
	public function event_action_links( $event_id ) {
		$action_links = array(
			'<a href="' . esc_url( get_edit_post_link( $event_id ) ) . '" title="' . esc_attr_x( 'Edit', 'attendee event actions', 'event-tickets' ) . '">' . esc_html_x( 'Edit', 'attendee event actions', 'event-tickets' ) . '</a>',
			'<a href="' . esc_url( get_permalink( $event_id ) ) . '" title="' . esc_attr_x( 'View', 'attendee event actions', 'event-tickets' ) . '">' . esc_html_x( 'View', 'attendee event actions', 'event-tickets' ) . '</a>',
		);

		/**
		 * Provides an opportunity to add and remove action links from the
		 * attendee screen summary box.
		 *
		 * @param array $action_links
		 */
		$action_links = (array) apply_filters( 'tribe_tickets_attendees_event_action_links', $action_links );

		if ( empty( $action_links ) ) {
			return;
		}

		echo '<div class="event-actions">' . join( ' | ', $action_links ) . '</div>';
	}

	/**
	 * Injects event meta data into the Attendees report
	 */
	public function event_details_top( $event_id ) {
		$post_type = get_post_type( $event_id );
		$post_type_object = get_post_type_object( $post_type );

		if ( Tribe__Events__Main::POSTTYPE === $post_type ) {
			echo '
				<li>
					<strong>' . esc_html__( 'Start Date:', 'event-tickets' ) . '</strong>
					' . tribe_get_start_date( $event_id, false, tribe_get_date_format( true ) ) . ' 
				</li>
			';
		}

		if ( tribe_has_venue( $event_id ) ) {
			$venue_id = tribe_get_venue_id( $event_id );

			echo '
				<li class="venue-name">
					<strong>' . tribe_get_venue_label_singular() . ': </strong>
					<a href="' . get_edit_post_link( $venue_id ) . '" title="' . esc_html__( 'Edit Venue', 'the-events-calendar' ) . '">' . tribe_get_venue( $event_id ) . '</a>
				</li>
			';
		}
	}
}
