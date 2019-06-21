<?php

/**
 * Class Tribe__Events__Linked_Posts__Base
 *
 * The base for each linked post managing class.
 *
 * @since TDB
 */
abstract class Tribe__Events__Linked_Posts__Base {
	/**
	 * @var string The post type managed by the linked post class.
	 */
	protected $post_type = '';

	/**
	 * @var string The prefix that will be used for the linked post custom fields.
	 */
	protected $meta_prefix = '';

	/**
	 * @var string The meta key relating a post of the type managed by the class to events.
	 */
	protected $event_meta_key = '';

	/**
	 * Returns an array of post fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <post_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see   Tribe__Duplicate__Strategy_Factory for supported strategies
	 *
	 * @since TDB
	 */
	abstract protected function get_duplicate_post_fields();

	/**
	 * Returns an array of post custom fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <custom_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see   Tribe__Duplicate__Strategy_Factory for supported strategies
	 *
	 * @since TDB
	 */
	abstract protected function get_duplicate_custom_fields();

	/**
	 * Finds posts of the type managed by the class that contain the search string.
	 *
	 * The method will search in the post and custom fields defined by the class.
	 *
	 * @param string $search
	 *
	 * @return array|bool An array of post IDs or `false` if nothing was found.
	 *
	 * @see   get_duplicate_post_fields()
	 * @see   get_duplicate_custom_fields()
	 *
	 * @since TDB
	 */
	public function find_like( $search ) {
		/** @var Tribe__Cache $cache */
		$cache      = tribe( 'cache' );
		$components = array( __CLASS__, __FUNCTION__, $search );
		$cache_key  = $cache->make_key( $components );

		if ( $cached = $cache[ $cache_key ] ) {
			return $cached;
		}

		$post_fields = $this->get_duplicate_post_fields();
		$post_fields = array_combine(
			array_keys( $post_fields ),
			array_fill( 0, count( $post_fields ), array( 'match' => 'like' ) )
		);

		$custom_fields = $this->get_duplicate_custom_fields();
		$custom_fields = array_combine(
			array_keys( $custom_fields ),
			array_fill( 0, count( $custom_fields ), array( 'match' => 'like' ) )
		);

		/** @var Tribe__Duplicate__Post $duplicates */
		$duplicates = tribe( 'post-duplicate' );
		$duplicates->set_post_type( $this->post_type );
		$duplicates->use_post_fields( $post_fields );
		$duplicates->use_custom_fields( $custom_fields );
		$duplicates->set_where_operator( 'OR' );

		$merged = array_merge( $post_fields, $custom_fields );

		$data = array_combine(
			array_keys( $merged ),
			array_fill( 0, count( $merged ), $search )
		);

		$found = $duplicates->find_all_for( $data );

		$cache[ $cache_key ] = $found;

		return $found;
	}

	/**
	 * Returns posts linked to the specified event.
	 *
	 * @param int|WP_Post $event_id
	 *
	 * @return array An array of matching post IDs.
	 *
	 * @since 4.6
	 */
	public function find_for_event( $event_id ) {
		/** @var Tribe__Cache $cache */
		$cache      = tribe( 'cache' );
		$components = array( __CLASS__, __FUNCTION__, $event_id );
		$cache_key  = $cache->make_key( $components );

		if ( $cached = $cache[ $cache_key ] ) {
			return $cached;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$event_id = Tribe__Main::post_id_helper( $event_id );

		if ( empty( $event_id ) ) {
			return array();
		}

		$query    = "SELECT pm.meta_value
				FROM {$wpdb-> posts} p
				JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND pm.post_id = %d
				AND meta_key = %s";
		$prepared = $wpdb->prepare( $query, Tribe__Events__Main::POSTTYPE, $event_id, $this->event_meta_key );

		$results = $wpdb->get_col( $prepared );


		if ( empty( $results ) ) {
			return array();
		}

		$found = array_map( 'intval', $results );

		$cache[ $cache_key ] = $found;

		return $found;
	}

	/**
	 * Returns an array of posts that have events, past or future, linked to them.
	 *
	 * @param bool  $has_events          Whether to look for posts with linked events or not.
	 * @param array $excluded_post_stati An array of post stati that should not be
	 *                                   considered for the purpose of marking a post
	 *                                   as "with events".
	 *
	 * @return array An array of matching post IDs.
	 *
	 * @since 4.6
	 */
	public function find_with_events( $has_events = true, $excluded_post_stati = null ) {
		/** @var Tribe__Cache $cache */
		$cache         = tribe( 'cache' );
		$function_args = func_get_args();
		$components    = array_merge( array( __CLASS__, __FUNCTION__ ), $function_args );
		$cache_key     = $cache->make_key( $components );

		if ( $cached = $cache[ $cache_key ] ) {
			return $cached;
		}
		$has_events = tribe_is_truthy( $has_events );

		if ( null === $excluded_post_stati ) {
			$excluded_post_stati = array( 'pending', 'draft' );
		}

		/**
		 * Filters the post stati that should be excluded when looking for venues with events.
		 *
		 * By default if an event is linked to a venue but in "pending" or "draft" status that venue
		 * will not be marked as having events.
		 *
		 * @param array $excluded_post_stati
		 *
		 *
		 * @since 4.6
		 */
		$excluded_post_stati = apply_filters( "tribe_{$this->post_type}_has_events_excluded_post_stati", $excluded_post_stati );

		/** @var wpdb $wpdb */
		global $wpdb;

		$post_status_clause = '';
		if ( ! empty( $excluded_post_stati ) ) {
			$excluded_post_stati_in = array();
			foreach ( $excluded_post_stati as $status ) {
				$excluded_post_stati_in[] = $wpdb->prepare( '%s', $status );
			}
			$excluded_post_stati_in = implode( ',', $excluded_post_stati_in );

			$post_status_clause = "AND p.post_status NOT IN ({$excluded_post_stati_in})";
		}

		$has_events_query = "SELECT DISTINCT pm.meta_value
				FROM {$wpdb->posts} p 
				JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s
				{$post_status_clause}
				AND meta_key = %s";

		$prepared_has_events_query = $wpdb->prepare(
			$has_events_query,
			Tribe__Events__Main::POSTTYPE,
			$this->event_meta_key
		);

		$results = $wpdb->get_col( $prepared_has_events_query );

		if ( ! $has_events ) {
			$query    = "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s";
			$prepared = $wpdb->prepare( $query, $this->post_type );
			$venues   = $wpdb->get_col( $prepared );

			if ( empty( $venues ) ) {
				$cache[ $cache_key ] = array();

				return array();
			}

			$found = array_diff( $venues, $results );

			if ( empty( $found ) ) {
				$cache[ $cache_key ] = [];

				return [];
			}
		} else {
			$found = $results;
		}

		$found               = array_map( 'intval', array_values( $found ) );

		$cache[ $cache_key ] = $found;

		return $found;
	}

	/**
	 * Finds posts of the type managed by the class that are related to upcoming events.
	 *
	 * @param bool              $only_with_upcoming
	 * @param null|string|array $event_post_status Only fetch events with the defined post status or stati;
	 *                                             will default to the post status set according to the current
	 *                                             user capabilities if not provided.
	 *
	 * @return array|bool An array of post IDs or `false` if nothing was found.
	 *
	 * @since TDB
	 */
	public function find_with_upcoming_events( $only_with_upcoming = true, $event_post_status = null ) {
		/** @var Tribe__Cache $cache */
		$cache      = tribe( 'cache' );
		$components = array( __CLASS__, __FUNCTION__, $only_with_upcoming );
		$cache_key  = $cache->make_key( $components );

		if ( $cached = $cache[ $cache_key ] ) {
			return $cached;
		}

		$only_with_upcoming = tribe_is_truthy( $only_with_upcoming );

		$args = array(
			'fields'     => 'ids',
			'start_date' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, time() ),
		);

		if ( null !== $event_post_status ) {
			$args['post_status'] = $event_post_status;
		}

		$events = tribe_get_events( $args );

		if ( empty( $events ) ) {
			$cache[ $cache_key ] = array();

			return array();
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$events_in = implode( ',', $events );
		$in_clause = $only_with_upcoming
			? "WHERE p.ID IN ({$events_in})"
			: "WHERE p.ID NOT IN ({$events_in})";

		$query = "SELECT pm.meta_value
			FROM {$wpdb->posts} p
			JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			{$in_clause}
			AND pm.meta_key = %s";

		$prepared = $wpdb->prepare( $query, $this->event_meta_key );

		$found = $wpdb->get_col( $prepared );
		if ( ! $only_with_upcoming ) {
			$without_events = $this->find_with_events( false );
			$found          = array_unique( array_merge( $found, $without_events ) );
		}

		if ( empty( $found ) ) {
			$cache[ $cache_key ] = array();

			return array();
		}

		$found = array_map( 'intval', $found );

		$cache[ $cache_key ] = $found;

		return $found;
	}

	/**
	 * Prefixes a key with the correct meta key prefix if needed.
	 *
	 * @param string $key
	 *
	 * @return string
	 *
	 * @since TDB
	 */
	protected function prefix_key( $key ) {
		if ( 0 !== strpos( $key, $this->meta_prefix ) && in_array( $key, Tribe__Events__Organizer::$meta_keys ) ) {
			return $this->meta_prefix . $key;
		}

		return $key;
	}
}
