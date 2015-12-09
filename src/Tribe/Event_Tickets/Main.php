<?php
/**
 * The Events Calendar integration with Event Tickets class
 *
 * @package The Events Calendar
 * @subpackage Event Tickets
 * @since 4.0.1
 */
class Tribe__Events__Event_Tickets__Main {
	/**
	 * Private variable holding the class instance
	 *
	 * @since 4.0.1
	 *
	 * @var Tribe__Events__Event_Tickets__Main
	 */
	private static $instance;

	/**
	 * Contains an instance of the Attendees Report integration class
	 *
	 * @since 4.0.1
	 *
	 * @var Tribe__Events__Event_Tickets__Attendees_Report
	 */
	private $attendees_report;

	/**
	 * Contains an instance of the Ticket Email integration class
	 *
	 * @since 4.0.2
	 *
	 * @var Tribe__Events__Event_Tickets__Ticket_Email
	 */
	private $ticket_email;

	/**
	 * Method to return the private instance of the class
	 *
	 * @since 4.0.1
	 *
	 * @return Tribe__Events__Event_Tickets__Main
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->attendees_report();
		$this->ticket_email();
	}

	/**
	 * Attendees Report integration class object accessor method
	 *
	 * @since 4.0.1
	 *
	 * @param object $object Override Attendees Report object
	 * @return Tribe__Events__Event_Tickets__Attendees_Report
	 */
	public function attendees_report( $object = null ) {
		if ( $object ) {
			$this->attendees_report = $object;
		} elseif ( ! $this->attendees_report ) {
			$this->attendees_report = new Tribe__Events__Event_Tickets__Attendees_Report;
		}

		return $this->attendees_report;
	}

	/**
	 * Ticket email integration class object accessor method
	 *
	 * @since 4.0.2
	 *
	 * @param object $object Override Ticket Email object
	 * @return Tribe__Events__Event_Tickets__Ticket_Email
	 */
	public function ticket_email( $object = null ) {
		if ( $object ) {
			$this->ticket_email = $object;
		} elseif ( ! $this->ticket_email ) {
			$this->ticket_email = new Tribe__Events__Event_Tickets__Ticket_Email;
		}

		return $this->ticket_email;
	}
}
