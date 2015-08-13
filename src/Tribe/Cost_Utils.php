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

		$costs = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_EventCost'" );

		return $costs;
	}//end get_all_costs

	/**
	 * fetches an event's cost values
	 *
	 * @param int|WP_Post $event The Event post object or event ID
	 *
	 * @return array
	 */
	public function get_event_costs( $event ) {
		$event = get_post( $event );

		if ( ! is_object( $event ) || ! $event instanceof WP_Post ) {
			return array();
		}

		if ( ! tribe_is_event( $event->ID ) ) {
			return array();
		}

		$costs = tribe_get_event_meta( $event->ID, '_EventCost', false );

		$parsed_costs = array();

		foreach ( $costs as $index => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$values = $this->parse_cost_range( $value );
			$parsed_costs = array_merge( $parsed_costs, $values );
		}

		return $parsed_costs;
	}//end get_event_costs

	/**
	 * Returns a formatted event cost
	 *
	 * @param int|WP_Post $event The Event post object or event ID
	 * @param bool $with_currency_symbol Include the currency symbol (optional)
	 *
	 * @return string
	 */
	public function get_formatted_event_cost( $event, $with_currency_symbol = false ) {
		$costs = $this->get_event_costs( $event );

		if ( ! $costs ) {
			return '';
		}

		$relevant_costs = array(
			'min' => $this->get_cost_by_func( $costs, 'min' ),
			'max' => $this->get_cost_by_func( $costs, 'max' ),
		);

		foreach ( $relevant_costs as &$cost ) {
			$cost = $this->maybe_replace_cost_with_free( $cost );

			if ( $with_currency_symbol ) {
				$cost = $this->maybe_format_with_currency( $cost );
			}

			$cost = esc_html( $cost );
		}

		if ( $relevant_costs['min'] == $relevant_costs['max'] ) {
			$formatted = $relevant_costs['min'];
		} else {
			$formatted = $relevant_costs['min'] . _x( ' - ', 'Cost range separator', 'tribe-events-calendar' ) . $relevant_costs['max'];
		}

		return $formatted;
	}//end get_formatted_event_cost

	/**
	 * If the cost is "0", call it "Free"
	 *
	 * @param int|float|string $cost Cost to analyze
	 *
	 * return int|float|string
	 */
	public function maybe_replace_cost_with_free( $cost ) {
		if ( '0' === (string) $cost ) {
			return __( 'Free', 'tribe-events-calendar' );
		}

		return $cost;
	}//end maybe_replace_cost_with_free

	/**
	 * Formats a cost with a currency symbol
	 *
	 * @param int|float|string $cost Cost to format
	 *
	 * return string
	 */
	public function maybe_format_with_currency( $cost ) {
		// check if the currency symbol is desired, and it's just a number in the field
		// be sure to account for european formats in decimals, and thousands separators
		if ( is_numeric( str_replace( array( ',', '.' ), '', $cost ) ) ) {
			$cost = tribe_format_currency( $cost );
		}

		return $cost;
	}//end maybe_format_with_currency

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

		$new_costs = array();

		foreach ( $costs as $index => $value ) {
			$values = $this->parse_cost_range( $value );
			foreach ( $values as $val ) {
				$new_costs[] = $val;
			}
		}

		$costs = $new_costs;

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
