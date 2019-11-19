<?php
/**
 * A value object representing an event query database result and the minimal entity of information required to work
 * with events.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */

namespace Tribe\Events\Views\V2\Query;

/**
 * Class Event_Result
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */
class Event_Result {

	/**
	 * An array of data keys that are required for an event result to be valid.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected static $required_keys = [ 'ID', 'post_status', 'start_date', 'end_date', 'timezone', 'all_day' ];
	/**
	 * The data wrapped by the value object.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Event_Result constructor.
	 *
	 * @param array $result
	 */
	public function __construct( array $data ) {
		foreach ( static::$required_keys as $required_key ) {
			if ( ! array_key_exists( $required_key, $data ) ) {
				throw new \InvalidArgumentException( 'The "' . $required_key . '" data key is missing!' );
			}
		}
		$this->data = $data;
		// Normalized the `all_day` flag property.
		$this->data['all_day'] = ! empty( $this->data['all_day'] );
	}

	/**
	 * Returns the event post ID.
	 *
	 * @since TBD
	 *
	 * @return int The event post ID.
	 */
	public function id() {
		return $this->data['ID'];
	}

	/**
	 * Returns the event start date in the site timezone.
	 *
	 * @since TBD
	 *
	 * @return string The event start date and time, in the `Y-m-d H:i:s` format.
	 */
	public function start_date() {
		return $this->data['start_date'];
	}

	/**
	 * Returns the event end date in the site timezone.
	 *
	 * @since TBD
	 *
	 * @return string The event end date and time, in the `Y-m-d H:i:s` format.
	 */
	public function end_date() {
		return $this->data['end_date'];
	}

	/**
	 * Returns the event timezone string.
	 *
	 * @since TBD
	 *
	 * @return string The event timezone string.
	 */
	public function timezone() {
		return $this->data['timezone'];
	}

	/**
	 * Returns a flag indicating whether the event is an all-day one or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the event is an all-day one or not.
	 */
	public function all_day() {
		return $this->data['all_day'];
	}

	/**
	 * Returns the event post status.
	 *
	 * @since TBD
	 *
	 * @return bool The event post status.
	 */
	public function status() {
		return $this->data['post_status'];
	}

	/**
	 * Dumps the event result data to array.
	 *
	 * @since TBD
	 *
	 * @return array The event result data.
	 */
	public function to_array() {
		return $this->data;
	}
}
