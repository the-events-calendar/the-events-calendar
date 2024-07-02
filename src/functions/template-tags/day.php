<?php
if ( ! function_exists( 'tribe_is_day' ) ) {
	/**
	 * Single Day Test
	 *
	 * Returns true if the query is set for single day, false otherwise
	 *
	 * @return bool
	 */
	function tribe_is_day(): bool {
		$context  = tribe_context();
		$is_day = tec_is_view( \Tribe\Events\Views\V2\Views\Day_View::get_view_slug() );

		/**
		 * Allows filtering of the tribe_is_day boolean value.
		 *
		 * @since 6.0.7 Added context to parameters.
		 *
		 * @param bool           $is_day  If we're on the Day View
		 * @param Tribe__Context $context The current context
		 */
		return apply_filters( 'tribe_is_day', $is_day, $context );
	}
}

if ( ! function_exists( 'tribe_get_day_link' ) ) {
	/**
	 * Generates a URL for the Day View of a specific date.
	 *
	 * This function constructs a URL for the Day View of the events calendar
	 * for a given date. If no date is provided, the current date will be used.
	 *
	 * @since 4.x.x
	 * @since 6.0.0 Uses Views V2 link structure.
	 *
	 * @param string|null $date The date for which to build the URL. Default is null, which means the current date.
	 *
	 * @return string The URL for the Day View of the specified date.
	 */
	function tribe_get_day_link( $date = null ) {
		$date_obj = Tribe__Date_Utils::build_date_object( $date );
		$url      = tribe_events_get_url( [
			'eventDisplay' => \Tribe\Events\Views\V2\Views\Day_View::get_view_slug(),
			'eventDate' => $date_obj->format( Tribe__Date_Utils::DBDATEFORMAT )
		] );

		/**
		 * Filters the URL for the Day View of the specified date.
		 *
		 * This filter allows customization of the URL generated for the Day View of
		 * the events calendar for a given date.
		 *
		 * @since ??
		 * @since 6.0.0 Uses Views V2 link structure.
		 *
		 * @param string      $url  The generated URL for the Day View.
		 * @param string|null $date The date for which the URL was generated.
		 */
		return (string) apply_filters( 'tribe_get_day_link', $url, $date );
	}
}
