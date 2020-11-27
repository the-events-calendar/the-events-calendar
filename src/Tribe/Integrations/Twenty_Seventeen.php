<?php
/**
 * Facilitates smoother integration with the Twenty Seventeen theme.
 *
 * @since 4.5.10
 */
class Tribe__Events__Integrations__Twenty_Seventeen {
	/**
	 * Performs setup for the Twenty Seventeen integration singleton.
	 *
	 * @since 4.5.10
	 */
	public function hook() {
		add_filter( 'body_class', [ $this, 'body_classes' ] );
	}

	/**
	 * Filters body classes for event archives.
	 *
	 * The default for event views is to remove the 'has-sidebar' class and
	 * modify 'page-one-column' to 'page-two-column', to achieve better fit
	 * and avoid JS errors.
	 *
	 * @since 4.5.10
	 * @see   https://central.tri.be/issues/70853
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		$is_event_view = tribe_is_event_query();

		/**
		 * Determines if the 'has-sidebar' body class should be removed or not.
		 *
		 * The default is to do this, for event views, as if this is present when
		 * the sidebar is not (which is normal), JS errors can result.
		 *
		 * @since 4.5.10
		 *
		 * @param bool  $should_remove
		 * @param array $classes
		 */
		$remove_sidebar_class = apply_filters(
			'tribe_events_twenty_seventeen_remove_sidebar_class',
			$is_event_view,
			$classes
		);

		if ( $remove_sidebar_class && $index = array_search( 'has-sidebar', $classes ) ) {
			unset( $classes[ $index ] );
		}

		/**
		 * Determines if the 'page-two-column' body class should be converted
		 * to 'page-one-column' or not.
		 *
		 * The default is to do this, for event views, to ensure a better layout
		 * for event views under Twenty Seventeen.
		 *
		 * @param bool  $convert_to_one_column
		 * @param array $classes
		 */
		$convert_to_one_column = apply_filters(
			'tribe_events_twenty_seventeen_convert_to_one_column',
			$is_event_view,
			$classes
		);

		if ( $convert_to_one_column && $index = array_search( 'page-two-column', $classes ) ) {
			$classes[ $index ] = 'page-one-column';
		}

		// Use standard full height header when main events page is used as the front page
		if ( ! is_singular() && ! is_archive() && tribe_get_option( 'front_page_event_archive', true ) ) {
			$classes[] = 'twentyseventeen-front-page';
		}

		return $classes;
	}
}
