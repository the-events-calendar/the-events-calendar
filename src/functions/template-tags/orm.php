<?php
/**
 * Defines the functions that provide the entry points to the ORM code.
 *
 * All functions here defined should be "pluggable" and replaceable.
 *
 * @since TBD
 */

if ( ! function_exists( 'tribe_events' ) ) {
	/**
	 * Builds and returns the correct event repository.
	 *
	 * @since TBD
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

		/**
		 * Filters the map relating event repository slugs to service container bindings.
		 *
		 * @since TBD
		 *
		 * @param array  $map        A map in the shape [ <repository_slug> => <service_name> ]
		 * @param string $repository The currently requested implementation.
		 */
		$map = apply_filters( 'tribe_events_event_repository_map', $map, $repository );

		return tribe( Tribe__Utils__Array::get( $map, $repository, $map['default'] ) );
	}
}

if ( ! function_exists( 'tribe_linked_posts' ) ) {
	/**
	 * Builds and returns the correct linked posts repository.
	 *
	 * @since TBD
	 *
	 * @param string $repository The slug of the repository to build/return.
	 *
	 * @return Tribe__Repository__Interface An instance of the requested repository
	 *                                      class.
	 */
	function tribe_linked_posts( $repository = 'default' ) {
		$map = array(
			'default' => 'events.linked-posts-repository',
		);

		/**
		 * Filters the map relating linked posts repository slugs to service container bindings.
		 *
		 * @since TBD
		 *
		 * @param array  $map        A map in the shape [ <repository_slug> => <service_name> ]
		 * @param string $repository The currently requested implementation.
		 */
		$map = apply_filters( 'tribe_events_linked_posts_repository_map', $map, $repository );

		return tribe( Tribe__Utils__Array::get( $map, $repository, $map['default'] ) );
	}
}
