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
	 * Link Event Day.
	 *
	 * @param string $date Which date was passed to build the URL.
	 *
	 * @return string URL.
	 */
	function tribe_get_day_link( $date = null ) {
		$date_obj = Tribe__Date_Utils::build_date_object( $date );
		$url      = tribe_events_get_url( [
			'eventDisplay' => \Tribe\Events\Views\V2\Views\Day_View::get_view_slug(),
			'eventDate' => $date_obj->format( Tribe__Date_Utils::DBDATEFORMAT )
		] );

		/**
		 * Allows the filtering of a given day link to our views.
		 *
		 * @since ??
		 * @since 6.0.0 Uses Views V2 link structure.
		 *
		 * @param string $url  Which URL we are using.
		 * @param string $date Which date was passed to build the URL.
		 */
		return (string) apply_filters( 'tribe_get_day_link', $url, $date );
	}
}
