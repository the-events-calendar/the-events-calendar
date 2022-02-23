<?php
/**
 * These are for backwards compatibility with the free The Events Calendar plugin.
 * Don't use them.
 *
 */

/**
 * Prints out data attributes used in the template header tags
 *
 * @deprecated 6.0.0 No longer used by templates / views.
 *
 * @param string|null $current_view
 *
 **@category Events
 */
function tribe_events_the_header_attributes( $current_view = null ) {
	_deprecated_function( __FUNCTION__, '6.0.0' );
	return;
}

if ( ! function_exists( 'tribe_get_next_day_date' ) ) {
	/**
	 * Get the next day's date
	 *
	 * @deprecated 6.0.0
	 *
	 * @category Events
	 *
	 * @return string
	 */
	function tribe_get_next_day_date( $start_date ) {
		_deprecated_function( __FUNCTION__, '6.0.0', 'Use PHP date functions.' );
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date( 'Y-m-d', strtotime( $start_date ) ) > '2037-12-30' ) {
				throw new OverflowException( esc_html__( 'Date out of range.', 'the-events-calendar' ) );
			}
		}
		$date = date( 'Y-m-d', strtotime( $start_date . ' +1 day' ) );
		return $date;
	}
}

if ( ! function_exists( 'tribe_get_previous_day_date' ) ) {
	/**
	 * Get the previous day's date
	 *
	 * @deprecated 6.0.0
	 *
	 * @category Events
	 *
	 * @return string
	 */
	function tribe_get_previous_day_date( $start_date ) {
		_deprecated_function( __FUNCTION__, '6.0.0', 'Use PHP date functions.' );
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date( 'Y-m-d', strtotime( $start_date ) ) < '1902-01-02' ) {
				throw new OverflowException( esc_html__( 'Date out of range.', 'the-events-calendar' ) );
			}
		}
		$date = date( 'Y-m-d', strtotime( $start_date . ' -1 day' ) );
		return $date;
	}
}