<?php
/**
 * Defines the functions that provide the entry points to the ORM code.
 *
 * All functions here defined should be "pluggable" and replaceable.
 *
 * @since 4.9
 */

if ( ! function_exists( 'tribe_events' ) ) {
	/**
	 * Builds and returns the correct event repository.
	 *
	 * @since 4.9
	 *
	 * @param string $repository The slug of the repository to build/return.
	 *
	 * @return Tribe__Repository__Interface An instance of the requested repository
	 *                                      class.
	 */
	function tribe_events( $repository = 'default' ) {
		$map = array(
			'default' => 'events.event-repository',
		);

		$args = func_num_args() > 1 ? array_slice( func_get_args(), 1 ) : [];

		/**
		 * Filters the map relating event repository slugs to service container bindings.
		 *
		 * @since 4.9
		 * @since 4.9.13 Added additional call arguements support.
		 *
		 * @param array  $map        A map in the shape [ <repository_slug> => <service_name> ]
		 * @param string $repository The currently requested implementation.
		 * @param array $args        An array of additional call arguments used to call the function beside the
		 *                           repository slug.
		 */
		$map = apply_filters( 'tribe_events_event_repository_map', $map, $repository, $args );

		return tribe( Tribe__Utils__Array::get( $map, $repository, $map['default'] ) );
	}
}

if ( ! function_exists( 'tribe_organizers' ) ) {
	/**
	 * Builds and returns the correct organizer repository.
	 *
	 * @since 4.9
	 *
	 * @param string $repository The slug of the repository to build/return.
	 *
	 * @return Tribe__Repository__Interface An instance of the requested repository
	 *                                      class.
	 */
	function tribe_organizers( $repository = 'default' ) {
		$map = array(
			'default' => 'events.organizer-repository',
		);

		/**
		 * Filters the map relating organizer repository slugs to service container bindings.
		 *
		 * @since 4.9
		 *
		 * @param array  $map        A map in the shape [ <repository_slug> => <service_name> ]
		 * @param string $repository The currently requested implementation.
		 */
		$map = apply_filters( 'tribe_events_organizer_repository_map', $map, $repository );

		return tribe( Tribe__Utils__Array::get( $map, $repository, $map['default'] ) );
	}
}

if ( ! function_exists( 'tribe_venues' ) ) {
	/**
	 * Builds and returns the correct venue repository.
	 *
	 * @since 4.9
	 *
	 * @param string $repository The slug of the repository to build/return.
	 *
	 * @return Tribe__Repository__Interface An instance of the requested repository
	 *                                      class.
	 */
	function tribe_venues( $repository = 'default' ) {
		$map = array(
			'default' => 'events.venue-repository',
		);

		/**
		 * Filters the map relating venue repository slugs to service container bindings.
		 *
		 * @since 4.9
		 *
		 * @param array  $map        A map in the shape [ <repository_slug> => <service_name> ]
		 * @param string $repository The currently requested implementation.
		 */
		$map = apply_filters( 'tribe_events_venue_repository_map', $map, $repository );

		return tribe( Tribe__Utils__Array::get( $map, $repository, $map['default'] ) );
	}
}
