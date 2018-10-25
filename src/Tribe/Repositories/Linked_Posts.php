<?php
/**
 * The main ORM/Repository class for linked posts.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Repositories__Linked_Posts
 *
 *
 * @since TBD
 */
class Tribe__Events__Repositories__Linked_Posts extends Tribe__Repository {

	/**
	 * The unique fragment that will be used to identify this repository filters.
	 *
	 * @var string
	 */
	protected $filter_name = 'linked_posts';

	/**
	 * Tribe__Events__Repositories__Linked_Posts constructor.
	 *
	 * Sets up the repository default parameters and schema.
	 *
	 * @since TBD
	 */
	public function __construct() {
		parent::__construct();

		$this->default_args = array(
			'post_type'                    => Tribe__Events__Venue::POSTTYPE,
			// We'll be handling the dates, let's mark the query as a non-filtered one.
			'tribe_suppress_query_filters' => true,
		);

		$this->schema = array_merge( $this->schema, array(
			'event' => array( $this, 'filter_by_event' ),
		) );
	}

	/**
	 * Filters linked post types by a specific event of set of events.
	 *
	 * @since TBD
	 *
	 * @param int|WP_Post|array $event Post ID, Post Object, or an array of Post IDs or Objects.
	 */
	public function filter_by_event( $event ) {
		$events = (array) $event;

		$post_ids = array();

		foreach ( $events as $event_id_or_object ) {
			$post_id = Tribe__Events__Main::postIdHelper( $event_id_or_object );

			if ( ! $post_id ) {
				continue;
			}

			$post_ids[] = $post_id;
		}

		$post_ids = array_unique( $post_ids );

		// @todo Figure out logic.
	}

}
