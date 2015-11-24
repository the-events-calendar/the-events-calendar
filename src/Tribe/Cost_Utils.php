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
	 * @return Tribe__Events__Cost_Utils
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * fetches all event costs from the database
	 *
	 * @return array
	 */
	public function get_all_costs() {
		global $wpdb;

		$costs = $wpdb->get_col( "
			SELECT
				DISTINCT meta_value
			FROM
				{$wpdb->postmeta}
			WHERE
				meta_key = '_EventCost'
				AND LENGTH( meta_value ) > 0;
		" );

		return $this->parse_cost_range( $costs );
	}

	/**
	 * Fetch the possible separators
	 *
	 * @return array
	 */
	public function get_separators() {
		/**
		 * Allow users to create more possible separators, they must be only 1 char
		 * @var array
		 */
		return apply_filters( 'tribe_events_cost_separators', array( ',', '.' ) );
	}

	public function get_cost_regex() {
		return apply_filters( 'tribe_events_cost_regex', '(([\d]+)[\\' . implode( '\\', $this->get_separators() ) . ']?([\d]*))' );
	}

	/**
	 * Check if a String is a valid cost
	 *
	 * @param  string  $cost String to be checked
	 * @return boolean
	 */
	public function is_valid_cost( $cost, $allow_negative = true ) {
		return preg_match( $this->get_cost_regex(), trim( $cost ) );
	}

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

			$parsed_costs += $this->parse_cost_range( $value );
		}

		return $parsed_costs;
	}

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
			$formatted = $relevant_costs['min'] . _x( ' - ', 'Cost range separator', 'the-events-calendar' ) . $relevant_costs['max'];
		}

		return $formatted;
	}

	/**
	 * If the cost is "0", call it "Free"
	 *
	 * @param int|float|string $cost Cost to analyze
	 *
	 * return int|float|string
	 */
	public function maybe_replace_cost_with_free( $cost ) {
		if ( '0' === (string) $cost ) {
			return esc_html__( 'Free', 'the-events-calendar' );
		}

		return $cost;
	}

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
		if ( is_numeric( str_replace( $this->get_separators(), '', $cost ) ) ) {
			$cost = tribe_format_currency( $cost );
		}

		return $cost;
	}

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
		} else {
			$costs = (array) $costs;
		}

		$costs = $this->parse_cost_range( $costs );

		if ( empty( $costs ) ) {
			return 0;
		}

		switch ( $function ) {
			case 'min':
				$cost = $costs[ min( array_keys( $costs ) ) ];
				break;
			case 'max':
			default:
				$cost = $costs[ max( array_keys( $costs ) ) ];
				break;
		}

		// use a regular expression instead of is_numeric
		if ( ! preg_match( $this->get_cost_regex(), $cost ) ) {
			return 0;
		}

		return $cost;
	}

	/**
	 * Returns a maximum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched via query.
	 *
	 * @param $costs mixed Cost(s) to review for max value
	 *
	 * @return float
	 */
	public function get_maximum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'max' );
	}

	/**
	 * Returns a minimum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched via query.
	 *
	 * @param $costs mixed Cost(s) to review for min value
	 *
	 * @return float
	 */
	public function get_minimum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'min' );
	}

	/**
	 * Returns boolean true if there are events for which a cost has not been specified.
	 *
	 * @return bool
	 */
	public function has_uncosted_events() {
		global $wpdb;

		$uncosted = $wpdb->get_var( "
			SELECT COUNT( * )
			FROM   {$wpdb->posts}

			LEFT JOIN {$wpdb->postmeta}
			          ON ( post_id = ID AND meta_key = '_EventCost' )

			WHERE LENGTH( meta_value ) = 0
			      OR meta_value IS NULL

			LIMIT 1
		" );

		return (bool) ( $uncosted > 0 );
	}

	/**
	 * Parses an event cost into an array of ranges. If a range isn't provided, the resulting array will hold a single value.
	 *
	 * @param $cost string Cost for event.
	 *
	 * @return array
	 */
	public function parse_cost_range( $costs, $max_decimals = null ) {
		if ( ! is_array( $costs ) && ! is_string( $costs ) ) {
			return array();
		}

		// remove any empty prices
		$costs = array_filter( (array) $costs );

		// If it's empty returns 0
		if ( empty( $costs ) ) {
			return array();
		}

		// Build the regular expression
		$price_regex = $this->get_cost_regex();
		$max = 0;

		foreach ( $costs as &$cost ) {
			// Get the required parts
			if ( preg_match_all( '/' . $price_regex . '/', $cost, $matches ) ) {
				$cost = reset( $matches );
			}

			// Get the max number of decimals for the range
			if ( count( $matches ) === 4 ) {
				$decimals = max( array_map( 'strlen', end( $matches ) ) );
				$max = max( $max, $decimals );
			}
		}

		// If we passed max decimals
		if ( ! is_null( $max_decimals ) ) {
			$max = max( $max_decimals, $max );
		}

		$ocost = array();
		$costs = call_user_func_array( 'array_merge', $costs );

		foreach ( $costs as $cost ) {
			// Creates a Well Balanced Index that will perform good on a Key Sorting method
			$index = str_replace( '.', '', number_format( str_replace( $this->get_separators(), '.', $cost ), $max ) );

			// Keep the Costs in a organizeable array by keys with the "numeric" value
			$ocost[ $index ] = $cost;
		}

		// Filter keeping the Keys
		ksort( $ocost );

		return (array) $ocost;
	}
}
