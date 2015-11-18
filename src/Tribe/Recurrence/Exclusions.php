<?php


class Tribe__Events__Pro__Recurrence__Exclusions {

	/**
	 * Tribe__Events__Pro__Recurrence__Exclusions constructor.
	 */
	public function __construct() {
	}

	/**
	 * @var Tribe__Events__Pro__Recurrence__Exclusions
	 */
	protected static $instance;

	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Accepts an array of $date_durations and removes any falling on the dates listed
	 * within $exclusion_dates.
	 *
	 * Both parameters are arrays of arrays, each inner array or "date duration" taking the
	 * following form:
	 *
	 *     [ 'timestamp' => int,
	 *       'duration'  => int  ]
	 *
	 * In the case of exclusions, duration will always be zero as custom exclusions do
	 * not currently support custom durations, so that element is ignored during comparison.
	 *
	 * @param array $date_durations
	 * @param array $exclusion_dates
	 *
	 * @return array
	 */
	public function remove_exclusions( array $date_durations, array $exclusion_dates ) {
		$exclusion_timestamps = array();

		foreach ( $exclusion_dates as $exclusion ) {
			$exclusion_timestamps[] = $exclusion[ 'timestamp' ];
		}

		foreach ( $date_durations as $key => $date_duration ) {
			if ( in_array( $date_duration[ 'timestamp' ], $exclusion_timestamps ) ) {
				unset( $date_durations[ $key ] );
			}
		}

		return array_values( $date_durations );
	}
}