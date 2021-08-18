<?php
/**
 * Hooks and registers the functions and implementations needed to provide
 * the ORM/Repository classes.
 *
 * @since 4.9
 */

/**
 * Class Tribe__Events__Service_Providers__ORM
 *
 * @since 4.9
 */
class Tribe__Events__Service_Providers__ORM extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		require_once tribe( 'tec.main' )->plugin_path . 'src/functions/template-tags/orm.php';

		// Not bound as a singleton to leverage the repository instance properties and to allow decoration and injection.
		$this->container->bind( 'events.event-repository', 'Tribe__Events__Repositories__Event' );
		$this->container->bind( 'events.organizer-repository', 'Tribe__Events__Repositories__Organizer' );
		$this->container->bind( 'events.venue-repository', 'Tribe__Events__Repositories__Venue' );

		add_filter( 'tribe_events_has_next_args', [ $this, 'maybe_remove_date_meta_queries' ], 1, 2 );
		add_filter( 'tribe_events_has_previous_args', [ $this, 'maybe_remove_date_meta_queries' ], 1, 2 );
		add_action( 'tribe_repository_events_pre_get_posts', [ $this, 'ensure_event_post_types_on_search' ], 50 );
	}

	/**
	 * Handles next and previous links arguments when generated on a repository-managed query.
	 *
	 * The next and previous links are built by using the global query arguments and slightly altering them.
	 * This approach, when done on the arguments provided by a repository generated query, might yield duplicated
	 * meta queries that will, in turn, return wrong results.
	 * The arguments will be already set in the arguments of the query.
	 *
	 * @since 4.9
	 *
	 * @param array          $args An array of query arguments that will be used to check if there are next or previous
	 *                             events.
	 * @param \WP_Query|null $query The query the arguments were taken from.
	 *
	 * @return array A filtered array of arguments where the date-related contents of the meta query are removed to
	 *               avoid duplicates.
	 */
	public function maybe_remove_date_meta_queries( array $args = [], WP_Query $query = null ) {
		if ( empty( $args['meta_query'] ) || ! $query instanceof WP_Query ) {
			return $args;
		}

		if ( empty( $query->builder ) || ! $query->builder instanceof Tribe__Repository__Interface ) {
			return $args;
		}

		$args['meta_query'] = tribe_filter_meta_query(
			$args['meta_query'],
			[ 'key' => '/_Event(Start|End)Date(UTC)*/' ]
		);

		return $args;
	}

	/**
	 * Ensures that if something hooks into `pre_get_posts` and alters the query post type
	 * the `tribe_events` post type is preserved so event searches don't break.
	 *
	 * @todo: investigate if we need this for organizers/venues as well.
	 *
	 * @since 5.8.2
	 *
	 * @param  WP_Query $query
	 *
	 * @return  WP_Query modified query
	 */
	public function ensure_event_post_types_on_search( $query ) {
		if ( ! $query->is_search ) {
			return $query;
		}

		if ( ! in_array( Tribe__Events__Main::POSTTYPE , (array) $query->query['post_type'] ) ) {
			return $query;
		}

		$query_post_type = $query->query_vars['post_type'];

		if ( ! is_array( $query_post_type ) ) {
			// If it's not an array, overwrite it.
			$query_post_type = Tribe__Events__Main::POSTTYPE;
		} else {
			// Else add event.
			$query_post_type[] = Tribe__Events__Main::POSTTYPE;
		}

		$query->query_vars['post_type'] = $query_post_type;

		return $query;
	}
}
