<?php
/**
 * Cost utility functions
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Cost_Utils extends Tribe__Cost_Utils {

	const UNCOSTED_EVENTS_TRANSIENT = 'tribe_events_have_uncosted_events';

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Cost_Utils
	 */
	public static function instance() {
		_deprecated_function( 'Tribe__Events__Cost_Utils::instance()', '4.5', "tribe( 'tec.cost-utils' )" );
		return tribe( 'tec.cost-utils' );
	}

	/**
	 * Fetches all event costs from the database
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
	 * Fetches an event's cost values
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

		$event_id = Tribe__Main::post_id_helper( $event );

		$relevant_costs = array(
			'min' => $this->get_cost_by_func( $costs, 'min' ),
			'max' => $this->get_cost_by_func( $costs, 'max' ),
		);

		foreach ( $relevant_costs as &$cost ) {
			$cost = $this->maybe_replace_cost_with_free( $cost );
			/**
			 * Filter the cost value prior to applying formatting
			 *
			 * @since 4.9.2
			 *
			 * @param double $cost the event cost
			 * @param int    $event_id  The ID of the event
			 */
			$cost = apply_filters( 'tribe_events_cost_unformatted', $cost, $event_id );

			if ( $with_currency_symbol ) {
				$currency_symbol   = get_post_meta( $event_id, '_EventCurrencySymbol', true );
				$currency_position = get_post_meta( $event_id, '_EventCurrencyPosition', true );

				if ( empty( $currency_position ) ) {
					$currency_position = tribe_get_option( 'reverseCurrencyPosition', false );
				}

				$cost = $this->maybe_format_with_currency( $cost, $event, $currency_symbol, $currency_position );
			}

			$cost = esc_html( $cost );
		}

		if ( $relevant_costs['min'] == $relevant_costs['max'] ) {
			$formatted = $relevant_costs['min'];
		} else {
			$formatted = $relevant_costs['min'] . _x( ' – ',
					'Cost range separator',
					'the-events-calendar' ) . $relevant_costs['max'];
		}

		return $formatted;
	}

	/**
	 * Returns boolean true if there are events for which a cost has not been specified.
	 *
	 * @return bool
	 */
	public function has_uncosted_events() {
		global $wpdb;

		// Expect: false := not set/expired, 1 := have uncosted events, 0 := no uncosted events
		$have_uncosted = get_transient( self::UNCOSTED_EVENTS_TRANSIENT );

		if ( false !== $have_uncosted ) {
			return (bool) $have_uncosted;
		}

		// @todo consider expanding our logic for improved handling of private posts etc
		$uncosted = $wpdb->get_var( $wpdb->prepare( "
			SELECT ID
			FROM   {$wpdb->posts}

			LEFT JOIN {$wpdb->postmeta}
			          ON ( post_id = ID AND meta_key = '_EventCost' )

			WHERE post_type = %s
			      AND (
			          LENGTH( meta_value ) = 0
			          OR meta_value IS NULL
			      )
			      AND post_status NOT IN ( 'auto-draft', 'revision' )

			LIMIT 1
		", Tribe__Events__Main::POSTTYPE ) );

		/**
		 * Whether or not we currently have events without any costs is something we store
		 * in a transient to avoid repeated queries: this filter controls how long in seconds
		 * that transient is allowed to live for.
		 *
		 * @param int $expires_after
		 */
		$expire_after = apply_filters( 'tribe_events_cost_utils_uncosted_events_expiry', HOUR_IN_SECONDS );

		// We cast to an int to avoid confusion when we next check the transient
		// (since bool false will be returned when the transient is not set)
		set_transient( self::UNCOSTED_EVENTS_TRANSIENT, (int) $uncosted, $expire_after );
		return (bool) ( $uncosted > 0 );
	}
}
