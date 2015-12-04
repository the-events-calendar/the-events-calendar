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
	 * @since 4.0.1
	 * @var Tribe__Events__Event_Tickets__Attendees_Report
	 */
	private $attendees_report;

	/**
	 * Method to return the private instance of the class
	 *
	 * @since 4.0.1
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
	}

	/**
	 * Attendees Report integration class object accessor method
	 */
	public function attendees_report( $object = null ) {
		if ( $object ) {
			$this->attendees_report = $object;
		} elseif ( ! $this->attendees_report ) {
			$this->attendees_report = new Tribe__Events__Event_Tickets__Attendees_Report;
		}

		return $this->attendees_report;
	}
}
