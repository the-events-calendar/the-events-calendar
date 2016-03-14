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
	 * @var string
	 */
	protected $_current_original_cost_separator;

	/**
	 * @var string
	 */
	protected $_supported_decimal_separators = '.,';

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
		 *
		 * @var array
		 */
		return apply_filters( 'tribe_events_cost_separators', array( ',', '.' ) );
	}

	public function get_cost_regex() {
		$separators = '[\\' . implode( '\\', $this->get_separators() ) . ']?';
		return apply_filters( 'tribe_events_cost_regex',
			'(' . $separators . '([\d]+)' . $separators . '([\d]*))' );
	}

	/**
	 * Check if a String is a valid cost
	 *
	 * @param  string $cost String to be checked
	 *
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
	 * @param int|WP_Post $event                The Event post object or event ID
	 * @param bool        $with_currency_symbol Include the currency symbol (optional)
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
			$formatted = $relevant_costs['min'] . _x( ' - ',
					'Cost range separator',
					'the-events-calendar' ) . $relevant_costs['max'];
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
	 * @param $costs    mixed Cost(s) to review for max value
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

		// if there's only one item, we're looking at a single event. If the cost is non-numeric, let's
		// return the non-numeric cost so that value is preserved
		if ( 1 === count( $costs ) && ! is_numeric( current( $costs ) ) ) {
			return current( $costs );
		}

		// make sure we are only trying to get numeric min/max values
		$costs = array_filter( $costs, 'is_numeric' );

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

		// If there isn't anything on the cost just return 0
		if ( empty( $cost ) ) {
			return 0;
		}

		return $cost;
	}

	/**
	 * Returns a maximum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched
	 * via query.
	 *
	 * @param $costs mixed Cost(s) to review for max value
	 *
	 * @return float
	 */
	public function get_maximum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'max' );
	}

	/**
	 * Returns a minimum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched
	 * via query.
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
	 * Parses an event cost into an array of ranges. If a range isn't provided, the resulting array will hold a single
	 * value.
	 *
	 * @param $cost string Cost for event.
	 *
	 * @return array
	 */
	public function parse_cost_range( $costs, $max_decimals = null ) {
		if ( ! is_array( $costs ) && ! is_string( $costs ) ) {
			return array();
		}

		// make sure costs is an array
		$costs = (array) $costs;

		// If there aren't any costs, return a blank array
		if ( 0 === count( $costs ) ) {
			return array();
		}

		// Build the regular expression
		$price_regex = $this->get_cost_regex();
		$max         = 0;

		foreach ( $costs as &$cost ) {
			// Get the required parts
			if ( preg_match_all( '/' . $price_regex . '/', $cost, $matches ) ) {
				$cost = reset( $matches );
			} else {
				$cost = array( $cost );
				continue;
			}

			// Get the max number of decimals for the range
			if ( count( $matches ) === 4 ) {
				$decimals = max( array_map( 'strlen', end( $matches ) ) );
				$max      = max( $max, $decimals );
			}
		}

		// If we passed max decimals
		if ( ! is_null( $max_decimals ) ) {
			$max = max( $max_decimals, $max );
		}

		$ocost = array();
		$costs = call_user_func_array( 'array_merge', $costs );

		foreach ( $costs as $cost ) {
			$numeric_cost = str_replace( $this->get_separators(), '.', $cost );

			if ( is_numeric( $numeric_cost ) ) {
				// Creates a Well Balanced Index that will perform good on a Key Sorting method
				$index = str_replace( array( '.', ',' ), '', number_format( $numeric_cost, $max ) );
			} else {
				// Makes sure that we have "index-safe" string
				$index = sanitize_title( $numeric_cost );
			}

			// Keep the Costs in a organizeable array by keys with the "numeric" value
			$ocost[ $index ] = $cost;
		}

		// Filter keeping the Keys
		ksort( $ocost );

		return (array) $ocost;
	}

	/**
	 * @param       string       $original_string_cost A string cost with or without currency symbol,
	 *                                                 e.g. `10 - 20`, `Free` or `2$ - 4$`.
	 * @param       array|string $merging_cost         A single string cost representation to merge or an array of
	 *                                                 string cost representations to merge, e.g. ['Free', 10, 20,
	 *                                                 'Donation'] or `Donation`.
	 * @param       bool         $with_currency_symbol Whether the output should prepend the currency symbol to the
	 *                                                 numeric costs or not.
	 * @param array              $sorted_mins          An array of non numeric price minimums sorted smaller to larger,
	 *                                                 e.g. `['Really free', 'Somewhat free', 'Free with 3 friends']`.
	 * @param array              $sorted_maxs          An array of non numeric price maximums sorted smaller to larger,
	 *                                                 e.g. `['Donation min $10', 'Donation min $20', 'Donation min
	 *                                                 $100']`.
	 *
	 * @return string|array The merged cost range.
	 */
	public function merge_cost_ranges( $original_string_cost, $merging_cost, $with_currency_symbol, $sorted_mins = array(), $sorted_maxs = array() ) {
		if ( empty( $merging_cost ) || $original_string_cost === $merging_cost ) {
			return $original_string_cost;
		}

		$_merging_cost              = array_map( array( $this, 'convert_decimal_separator' ),
			(array) $merging_cost );
		$_merging_cost              = array_map( array( $this, 'numerize_numbers' ), $_merging_cost );
		$numeric_merging_cost_costs = array_filter( $_merging_cost, 'is_numeric' );

		$matches = array();
		preg_match_all( '!\d+(?:([' . preg_quote( $this->_supported_decimal_separators ) . '])\d+)?!',
			$original_string_cost,
			$matches );
		$this->_current_original_cost_separator = empty( $matches[1][0] ) ? '.' : $matches[1][0];
		$matches[0]                             = empty( $matches[0] ) ? $matches[0] : array_map( array(
			$this,
			'convert_decimal_separator',
		),
			$matches[0] );
		$numeric_orignal_costs                  = empty( $matches[0] ) ? $matches[0] : array_map( 'floatval',
			$matches[0] );

		$all_numeric_costs = array_filter( array_merge( $numeric_merging_cost_costs, $numeric_orignal_costs ) );
		$cost_min          = $cost_max = false;

		$merging_mins     = array_intersect( $sorted_mins, (array) $merging_cost );
		$merging_has_min  = array_search( reset( $merging_mins ), $sorted_mins );
		$original_has_min = array_search( $original_string_cost, $sorted_mins );
		$merging_has_min  = false === $merging_has_min ? 999 : $merging_has_min;
		$original_has_min = false === $original_has_min ? 999 : $original_has_min;
		$string_min_key   = min( $merging_has_min, $original_has_min );
		if ( array_key_exists( $string_min_key, $sorted_mins ) ) {
			$cost_min = $sorted_mins[ $string_min_key ];
		} else {
			$cost_min = empty( $all_numeric_costs ) ? '' : min( $all_numeric_costs );
		}

		$merging_maxs     = array_intersect( $sorted_maxs, (array) $merging_cost );
		$merging_has_max  = array_search( end( $merging_maxs ), $sorted_maxs );
		$original_has_max = array_search( $original_string_cost, $sorted_maxs );
		$merging_has_max  = false === $merging_has_max ? - 1 : $merging_has_max;
		$original_has_max = false === $original_has_max ? - 1 : $original_has_max;
		$string_max_key   = max( $merging_has_max, $original_has_max );
		if ( array_key_exists( $string_max_key, $sorted_maxs ) ) {
			$cost_max = $sorted_maxs[ $string_max_key ];
		} else {
			$cost_max = empty( $all_numeric_costs ) ? '' : max( $all_numeric_costs );
		}

		$cost = array_filter( array( $cost_min, $cost_max ) );

		if ( $with_currency_symbol ) {
			$formatted_cost = array();
			foreach ( $cost as $c ) {
				$formatted_cost[] = is_numeric( $c ) ? tribe_format_currency( $c ) : $c;
			}
			$cost = $formatted_cost;
		}

		return empty( $cost ) ? $original_string_cost : array_map( array( $this, 'restore_original_decimal_separator' ),
			$cost );
	}

	/**
	 * Converts the original decimal separator to ".".
	 *
	 * @param string|int $value
	 *
	 * @return string
	 */
	protected function convert_decimal_separator( $value ) {
		return preg_replace( '/[' . preg_quote( $this->_supported_decimal_separators ) . ']/', '.', $value );
	}

	/**
	 * Restores the decimal separator to its original symbol.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private function restore_original_decimal_separator( $value ) {
		return str_replace( '.', $this->_current_original_cost_separator, $value );
	}

	/**
	 * Extracts int and floats from a numeric "dirty" string like strings that might contain other symbols.
	 *
	 * E.g. "$10" will yield "10"; "23.55$" will yield "23.55".
	 *
	 * @param string|int $value
	 *
	 * @return int|float
	 */
	private function numerize_numbers( $value ) {
		$matches = array();

		$pattern = '/(\\d{1,}([' . $this->_supported_decimal_separators . ']\\d{1,}))/';

		return preg_match( $pattern, $value, $matches ) ? $matches[1] : $value;
	}
}
