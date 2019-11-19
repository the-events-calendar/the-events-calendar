<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */

namespace Tribe\Events\Views\V2\Query;

use Tribe\Utils\Collection_Interface;
use Tribe\Utils\Collection_Trait;
use Tribe__Utils__Array as Arr;

class Events_Result_Set implements Collection_Interface {
	use Collection_Trait;
	/**
	 * An array of event results in this result set.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $event_results;

	public function __construct( array $event_results = [] ) {
		$this->event_results = $event_results;
	}

	/**
	 * Builds a result set from different type of values.
	 *
	 * @since TBD
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
			try {
				return new Events_Result_Set( $value );
			} catch ( \Exception $e ) {
				return new Events_Result_Set( [] );
			}
		}

		return new Events_Result_Set( [] );
	}

	public function count() {
		return count( $this->event_results );
	}

	public function order_by( $order_by, $order ) {
		$order = strtoupper( $order );
		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			throw new\InvalidArgumentException( 'Order "' . $order . '" is not supported, only "ASC" and "DESC" are.' );
		}

		// @todo @be here support more ordering criteria than date.
		$order_by_key_map = [
			'event_date' => 'start_date',
		];

		$order_by_key = Arr::get( $order_by_key_map, $order_by, 'start_date' );

		$this->event_results = wp_list_sort( $this->event_results, $order_by_key, $order );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function all() {
		return $this->event_results;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return wp_json_encode( $this->event_results );
	}

	/**
	 * Plucks a key from all the event results in the collection.
	 *
	 * @since TBD
	 *
	 * @param string $column The key to pluck.
	 *
	 * @return array An array of all the values associated to the key for each event result in the set.
	 */
	public function pluck( $column ) {
		return wp_list_pluck( $this->event_results, $column );
	}
}
