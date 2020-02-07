<?php
/**
 * A collection of Event_Results.
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */

namespace Tribe\Events\Views\V2\Repository;

use Tribe\Utils\Collection_Interface;
use Tribe\Utils\Collection_Trait;
use Tribe__Utils__Array as Arr;

/**
 * Class Events_Result_Set
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */
class Events_Result_Set implements Collection_Interface {
	use Collection_Trait;

	/**
	 * An array of event results in this result set.
	 *
	 * @since 4.9.13
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Events_Result_Set constructor.
	 *
	 * @param Event_Result[]|array $event_results An array of event results.
	 */
	public function __construct( array $event_results = [] ) {
		$this->items = $this->normalize_event_results( $event_results );
	}

	/**
	 * Returns whether a string represents a serialized instance of the class or not.
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $value The value to test.
	 *
	 * @return bool Whether the input value is a string representing a serialized instance of the class or not.
	 */
	protected static function is_serialized( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}

		$serialized_start = sprintf( 'C:%d:"%s"', strlen( __CLASS__ ), __CLASS__ );

		return 0 === strpos( $value, $serialized_start );
	}

	/**
	 * Unserializes, with error handling, a result set to return a new instance of this class.
	 *
	 * @since 5.0.0
	 *
	 * @param string $value The serialized version of the result set.
	 *
	 * @return Events_Result_Set The unserialized result set, or an empty result set on failure.
	 */
	protected static function from_serialized( $value ) {
		try {
			$set = unserialize( $value );
			if ( false === $set || ! $set instanceof static ) {
				return new Events_Result_Set( [] );
			}

			return $set;
		} catch ( \Exception $e ) {
			return new Events_Result_Set( [] );
		}
	}

	/**
	 * Builds a set from an array of event results.
	 *
	 * @since 5.0.0
	 *
	 * @param array<Event_Result> $event_results An array of event results.
	 *
	 * @return Events_Result_Set A new set, built from the input Event Results.
	 */
	protected static function from_array( $event_results ) {
		try {
			return new Events_Result_Set( $event_results );
		} catch ( \Exception $e ) {
			return new Events_Result_Set( [] );
		}
	}

	/**
	 * Builds a result set from different type of values.
	 *
	 * @since 4.9.13
	 *
	 * @param mixed $value A result set, that will be returned intact, an array of event results
	 *
	 * @return Events_Result_Set The original set, a set built on an array of `Event_Result` instances, or a set
	 *                           built on an empty array if the set could not be built.
	 */
	public static function from_value( $value ) {
		if ( $value instanceof Events_Result_Set ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return self::from_array( $value );
		}

		if ( self::is_serialized( $value ) ) {
			return self::from_serialized( $value );
		}

		return new Events_Result_Set( [] );
	}

	/**
	 * Returns the number of Event Results in this set.
	 *
	 * @since 5.0.0
	 *
	 * @return int The number of Event Results in this set.
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Orders the Event Results by a specified criteria.
	 *
	 * @since 5.0.0
	 *
	 * @param string $order_by The key to order the Event Results by, currently supported is only `start_date`.
	 * @param string $order The order direction, one of `ASC` or `DESC`.
	 *
	 * @return $this The current object, for chaining.
	 */
	public function order_by( $order_by, $order ) {
		$order = strtoupper( $order );
		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			throw new \InvalidArgumentException( 'Order "' . $order . '" is not supported, only "ASC" and "DESC" are.' );
		}

		// @todo @be here support more ordering criteria than date.
		$order_by_key_map = [
			'event_date' => 'start_date',
		];

		$order_by_key = Arr::get( $order_by_key_map, $order_by, 'start_date' );

		/*
		 * The `wp_list_sort` function will convert each element of the set in an array.
		 * Since we cannot control that `(array)$item` cast, we pre-convert each
		 * element into an array and convert it back to a set of `Event_Result` after the sorting.
		 */
		$this->items = static::from_value(
			wp_list_sort(
				$this->to_array(),
				$order_by_key,
				$order
			)
		);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function all() {
		return $this->items;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return wp_json_encode( $this->items );
	}

	/**
	 * Plucks a key from all the event results in the collection.
	 *
	 * @since 4.9.13
	 *
	 * @param string $column The key to pluck.
	 *
	 * @return array An array of all the values associated to the key for each event result in the set.
	 */
	public function pluck( $column ) {
		return wp_list_pluck( $this->items, $column );
	}

	/**
	 * Iterates over the result set and to return the array version of each result.
	 *
	 * @since 4.9.13
	 *
	 * @return array An array of arrays, each one the array version of an `Event_Result`.
	 */
	public function to_array() {
		return array_map( static function ( Event_Result $event_result ) {
			return $event_result->to_array();
		}, $this->items );
	}

	/**
	 * Overrides the base `Collection_Trait` implementation to normalize all the items in the result set.
	 *
	 * @since 4.9.13
	 *
	 * @param string $data The serialized data.
	 */
	public function unserialize( $data ) {
		$event_results = unserialize( $data );
		$this->items   = $this->normalize_event_results( $event_results );
	}

	/**
	 * Normalizes the event results in this set ensuring each one is an instance of `Event_Result`.
	 *
	 * @since 4.9.13
	 *
	 * @param array $event_results A set of event results in array or object format..
	 *
	 * @return Event_Result[] The normalized set of results.
	 */
	protected function normalize_event_results( array $event_results ) {
		return array_map( static function ( $result ) {
			return Event_Result::from_value( $result );
		}, $event_results );
	}
}
