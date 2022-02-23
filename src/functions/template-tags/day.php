<?php
if ( ! function_exists( 'tribe_is_day' ) ) {
	/**
	 * Single Day Test
	 *
	 * Returns true if the query is set for single day, false otherwise
	 *
	 * @category Events
	 * @return bool
	 */
	function tribe_is_day() {
		$view_slug = tribe_context()->get( 'view', 'default' );

		if ( 'default' === $view_slug ) {
			$view_slug = tribe( \Tribe\Events\Views\V2\Manager::class )->get_default_view_slug();
		}

		$is_day = 'day' === $view_slug;

		return apply_filters( 'tribe_is_day', $is_day );
	}
}

if ( ! function_exists( 'tribe_get_day_link' ) ) {
	/**
	 * Link Event Day
	 *
	 * @category Events
	 * @param string $date
	 *
	 * @return string URL
	 */
	function tribe_get_day_link( $date = null ) {
		$tribe_ecp = Tribe__Events__Main::instance();

		return apply_filters( 'tribe_get_day_link', $tribe_ecp->getLink( 'day', $date ), $date );
	}
}