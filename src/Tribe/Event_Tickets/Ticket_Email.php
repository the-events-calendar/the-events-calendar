<?php
/**
 * The Events Calendar integration with Event Tickets ticket email
 *
 * @package The Events Calendar
 * @subpackage Event Tickets
 * @since 4.0.2
 */
class Tribe__Events__Event_Tickets__Ticket_Email {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds hooks for injecting/overriding aspects of the ticket emails from Event Tickets
	 *
	 * @since 4.0.2
	 */
	public function add_hooks() {
		add_filter( 'event_tickets_email_include_start_date', [ $this, 'maybe_include_start_date' ], 10, 2 );
	}

	/**
	 * Includes the start date in the ticket email if the post type is appropriate
	 *
	 * @since 4.0.2
	 * @param boolean $include_start_date Whether or not to include the start date
	 * @param int $event_id Event ID
	 * @return boolean
	 */
	public function maybe_include_start_date( $include_start_date, $event_id ) {
		// if the post type isn't the TEC post type, don't change the boolean
		if ( Tribe__Events__Main::POSTTYPE !== get_post_type( $event_id ) ) {
			return $include_start_date;
		}

		return true;
	}
}
