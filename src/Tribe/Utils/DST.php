<?php


class Tribe__Events__Utils__DST {

	/**
	 * Whether the time is in DST or not.
	 *
	 * @var bool
	 */
	protected $_in_dst;

	/**
	 * @var string A UNIX timestamp
	 */
	protected $time;

	/**
	 * @var bool
	 */
	protected $is_dst_cache;

	/**
	 * Tribe__Events__Utils__DST constructor.
	 *
	 * @param int|string $time    Either a UNIX timestamp or an english format date.
	 * @param null|bool  $_in_dst An injectable DST status, meant for tests.
	 */
	public function __construct( $time, $_in_dst = null ) {
		if ( ! is_numeric( $time ) ) {
			$time = strtotime( $time );
		}

		$this->time    = $time;
		$this->_in_dst = $_in_dst;
	}

	/**
	 * Whether the current time is in DST or not.
	 *
	 * @return bool
	 */
	public function is_in_dst() {
		if ( is_null($this->is_dst_cache) ) {
			$default_timezone_backup = date_default_timezone_get();
			$wp_timezone_string      = get_option( 'timezone_string' );
			$wp_timezone_string      = empty( $wp_timezone_string ) ? $default_timezone_backup : $wp_timezone_string;

			date_default_timezone_set( $wp_timezone_string );
			$this->is_dst_cache = is_null( $this->_in_dst ) ? (bool) date( 'I', $this->time ) : $this->_in_dst;
			date_default_timezone_set( $default_timezone_backup );
		}

		return $this->is_dst_cache;
	}

	/**
	 * Returns the time of the object aligned with another date object.
	 *
	 * If both in date or both not in DST the same time; if this time is in DST and the
	 * target date is not in DST then the time +1hr, else the time -1 hr.
	 *
	 * @param Tribe__Events__Utils__DST $dst Another DST object this one should be aligned with.
	 *
	 * @return int The DST aligned UNIX timestamp.
	 */
	public function get_time_aligned_with( Tribe__Events__Utils__DST $dst ) {
		$dst_aligned = $this->is_in_dst() == $dst->is_in_dst();
		$offset      = $dst->is_in_dst() - $this->is_in_dst();

		return $dst_aligned ? $this->time : $this->time + $offset * 3600;
	}
}