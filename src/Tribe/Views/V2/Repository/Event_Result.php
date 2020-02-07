<?php
/**
 * A value object representing an event query database result and the minimal entity of information required to work
 * with events.
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */

namespace Tribe\Events\Views\V2\Repository;

/**
 * Class Event_Result
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */
class Event_Result {

	/**
	 * An array of data keys that are required for an event result to be valid.
	 *
	 * @since 4.9.13
	 *
	 * @var array
	 */
	protected static $required_keys = [ 'ID', 'post_status', 'start_date', 'end_date', 'timezone', 'all_day' ];
	/**
	 * The data wrapped by the value object.
	 *
	 * @since 4.9.13
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
	 * Builds and returns a result set from an array of values.
	 *
	 * @since 4.9.13
	 *
	 * @param array $value The value to build the instance from.
	 *
	 * @return static An result instance.
	 */
	public static function from_value( $value ) {
		return $value instanceof static ? $value : new static( $value );
	}

	/**
	 * Returns the event post ID.
	 *
	 * @since 4.9.13
	 *
	 * @return int The event post ID.
	 */
	public function id() {
		return (int) $this->data['ID'];
	}

	/**
	 * Returns the event start date in the site timezone.
	 *
	 * @since 4.9.13
	 *
	 * @return string The event start date and time, in the `Y-m-d H:i:s` format.
	 */
	public function start_date() {
		return $this->data['start_date'];
	}

	/**
	 * Returns the event end date in the site timezone.
	 *
	 * @since 4.9.13
	 *
	 * @return string The event end date and time, in the `Y-m-d H:i:s` format.
	 */
	public function end_date() {
		return $this->data['end_date'];
	}

	/**
	 * Returns the event timezone string.
	 *
	 * @since 4.9.13
	 *
	 * @return string The event timezone string.
	 */
	public function timezone() {
		return $this->data['timezone'];
	}

	/**
	 * Returns a flag indicating whether the event is an all-day one or not.
	 *
	 * @since 4.9.13
	 *
	 * @return bool Whether the event is an all-day one or not.
	 */
	public function all_day() {
		return $this->data['all_day'];
	}

	/**
	 * Returns the event post status.
	 *
	 * @since 4.9.13
	 *
	 * @return bool The event post status.
	 */
	public function status() {
		return $this->data['post_status'];
	}

	/**
	 * Dumps the event result data to array.
	 *
	 * @since 4.9.13
	 *
	 * @return array The event result data.
	 */
	public function to_array() {
		return $this->data;
	}

	public function __get( $name ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		throw new \InvalidArgumentException( 'Property "' . $name . '" is not accessible or defined on Event Result.' );
	}

	/**
	 * Sets a property on the result, returning a modified clone.
	 *
	 * @since 4.9.13
	 *
	 * @param string $name  The name of the property to set.
	 * @param mixed  $value The property value.
	 *
	 * @return Event_Result A clone of this result.
	 */
	public function __set( $name, $value ) {
		$clone          = clone $this;
		$clone->{$name} = $value;

		return $clone;
	}

	/**
	 * Checks whether a data entry is set or not.
	 *
	 * @since 4.9.13
	 *
	 * @param string $name The name of the data entry to set.
	 *
	 * @return bool Whether a data entry is set or not.
	 */
	public function __isset( $name ) {
		return isset( $this->data[ $name ] );
	}
}
