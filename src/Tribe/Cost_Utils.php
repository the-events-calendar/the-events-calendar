<?php
/**
 * Cost utility functions
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Cost_Utils {
	/**
	 * Static Singleton Factory Method
	 *
	 *@return Tribe__Events__Cost_Helpers
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$className = __CLASS__;
			$instance = new $className;
		}

		return $instance;
	}//end instance

	/**
	 * fetches all event costs from the database
	 *
	 * @return array
	 */
	public function get_all_costs() {
		global $wpdb;

		$costs = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_EventCost'" );

		return $costs;
	}//end get_all_costs

	/**
	 * Returns a particular cost within an array of costs
	 *
	 * @param $costs mixed Cost(s) to review for max value
	 * @param $function string Function to use to determine which cost to return from range. Valid values: max, min
	 *
	 * @return float
	 */
	protected function get_cost_by_func( $costs = null, $function = 'max' ) {
		if ( null === $costs ) {
			$costs = $this->get_all_costs();
		} elseif ( ! is_array( $costs ) ) {
			$costs = array( $costs );
		}

		foreach ( $costs as $index => $value ) {
			$values = $this->parse_cost_range( $value );
			$costs = array_merge( $costs, $values );
		}

		if ( empty( $costs ) ) {
			return 0;
		}

		switch ( $function ) {
			case 'min':
				$cost = min( $costs );
				break;
			case 'max':
			default:
				$cost = max( $costs );
				break;
		}//end switch

		if ( ! is_numeric( $cost ) ) {
			return 0;
		}

		return $cost;
	}//end get_cost_by_func

	/**
	 * Returns a maximum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched via query.
	 *
	 * @param $costs mixed Cost(s) to review for max value
	 *
	 * @return float
	 */
	public function get_maximum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'max' );
	}//end get_maximum_cost

	/**
	 * Returns a minimum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched via query.
	 *
	 * @param $costs mixed Cost(s) to review for min value
	 *
	 * @return float
	 */
	public function get_minimum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'min' );
	}//end get_minimum_cost

	/**
	 * Parses an event cost into an array of ranges. If a range isn't provided, the resulting array will hold a single value.
	 *
	 * @param $cost string Cost for event.
	 *
	 * @return array
	 */
	public function parse_cost_range( $cost ) {
		// try to find the lowest numerical value in a possible range
		if ( preg_match( '/^(-?[\d]+)[^\d\.]+([\d\.]+)/', $cost, $matches ) ) {
			$values = array(
				$matches[1],
				$matches[2],
			);

			return $values;
		}//end if

		// convert non-range into an actual numeric value
		$value = preg_replace( '/^[^\d]+(\d+\.?\d*)?.*$/', '$1', $cost );

		return array( $value );
	}//end parse_cost_range
}//end class
