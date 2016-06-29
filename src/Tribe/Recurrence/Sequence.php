<?php


class Tribe__Events__Pro__Recurrence__Sequence {

	/**
	 * @var array
	 */
	protected $sequence;

	/**
	 * @var int
	 */
	protected $parent_event_id;

	/**
	 * @var bool
	 */
	protected $has_sorted_sequence = false;

	/**
	 * @var string
	 */
	protected $timezone_string;

	/**
	 * @var array
	 */
	protected $sorted_sequence;

	/**
	 * Tribe__Events__Pro__Recurrence__Sequence constructor.
	 *
	 * @param array $sequence
	 * @param int   $parent_event_id
	 */
	public function __construct( array $sequence, $parent_event_id ) {
		if ( ! empty( $sequence ) ) {
			$this->ensure_sequence_format( $sequence );
		}

		$this->ensure_event( $parent_event_id );

		$this->sequence        = $sequence;
		$this->parent_event_id = $parent_event_id;
	}

	/**
	 * Checks that each sequence element is an array containing a timestamp.
	 *
	 * @param array $sequence
	 */
	private function ensure_sequence_format( array $sequence ) {
		foreach ( $sequence as $key => $value ) {
			if ( ! ( is_array( $value ) && isset( $value['timestamp'] ) && is_numeric( $value['timestamp'] ) ) ) {
				throw new InvalidArgumentException( 'Any sequence entry must contain a Unix timestamp under the `timestamp` key' );
			}
		}
	}

	/**
	 * @param $parent_event_id
	 */
	private function ensure_event( $parent_event_id ) {
		if ( ! tribe_is_event( $parent_event_id ) ) {
			throw new InvalidArgumentException( 'Parent event ID should be a valid event post' );
		}
	}

	/**
	 * @return array The sequence sorted by start date (not time) ASC
	 */
	public function get_sorted_sequence_array() {
		$this->sort_sequence();

		return $this->sequence;
	}

	private function sort_sequence() {
		if ( $this->has_sorted_sequence ) {
			return;
		}

		// determine the parent event timezone to use for same day comparison between events
		$timezone              = Tribe__Events__Timezones::get_event_timezone_string( $this->parent_event_id );
		$this->timezone_string = Tribe__Events__Timezones::generate_timezone_string_from_utc_offset( $timezone );

		// sort the dates to create by starting time
		usort( $this->sequence, array( $this, 'sort_by_start_date' ) );
		$this->has_sorted_sequence = true;
	}

	/**
	 * @return array The sequence sorted with an added `sequence` key to specify the sequence order.
	 */
	public function get_sorted_sequence() {
		$this->sort_sequence();

		if ( null !== $this->sorted_sequence ) {
			return $this->sorted_sequence;
		}

		$last_entry_timestamp = false;
		$sequence_number      = 1;


		$sequence = $this->sequence;
		$output = $sequence;

		foreach ( $sequence as $key => $entry ) {
			$same_start_date_and_time = $entry['timestamp'] === $last_entry_timestamp;
			$is_part_of_sequence      = $same_start_date_and_time || $this->timestamps_are_in_same_day( $entry['timestamp'], $last_entry_timestamp );

			if ( ! $is_part_of_sequence ) {
				$sequence_number = 1;
			} else {
				++ $sequence_number;
			}

			$output[$key]['sequence']    = $sequence_number;
			$last_entry_timestamp = $entry['timestamp'];
		}

		$this->sorted_sequence = $output;

		return $output;
	}


	/**
	 * @param array $a
	 * @param array $b
	 *
	 * @return int
	 */
	private function sort_by_start_date( $a, $b ) {
		$a_timestamp = $a['timestamp'];
		$b_timestamp = $b['timestamp'];

		if ( $a_timestamp == $b_timestamp ) {
			return 0;
		}

		return ( $a_timestamp < $b_timestamp ) ? - 1 : 1;
	}

	/**
	 * @param int $a_timestamp
	 * @param int $b_timestamp
	 *
	 * @return bool
	 */
	private function timestamps_are_in_same_day( $a_timestamp, $b_timestamp ) {
		if ( false === $a_timestamp || false === $b_timestamp ) {
			return false;
		}

		$timezone     = new DateTimeZone( $this->timezone_string );
		$format       = DateTime::COOKIE;
		$a_start_date = new DateTime( date( $format, $a_timestamp ), $timezone );
		$b_start_date = new DateTime( date( $format, $b_timestamp ), $timezone );

		return $a_start_date->format( 'Y-d-m' ) === $b_start_date->format( 'Y-d-m' );
	}

}