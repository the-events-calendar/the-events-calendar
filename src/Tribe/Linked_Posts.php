<?php
/**
 * Object used to handle the linking/unlinking of post types for events
 */
class Tribe__Events__Linked_Posts {
	/**
	 * @var string Meta key prefix for linked posts
	 */
	const META_KEY_PREFIX = '_tribe_linked_post_';

	/**
	 * @var Tribe__Events__Linked_Posts Singleton instance of the class
	 */
	public static $instance;

	/**
	 * @var array Collection of post types that can be linked with events
	 */
	public $linked_post_types;

	/**
	 * Returns a singleton of this class
	 *
	 * @return Tribe__Events__Linked_Posts
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor!
	 */
	public function __construct() {
		$this->register_default_linked_post_types();
	}

	/**
	 * Registers the default linked post types for events
	 *
	 * @since 4.2
	 */
	protected function register_default_linked_post_types() {
		$default_post_types = array(
			'tribe_venue',
			'tribe_organizer',
		);

		/**
		 * Filters the list of default registered linked post types
		 *
		 * @since 4.2
		 *
		 * @var array Array of post type strings
		 */
		$linked_post_types = apply_filters( 'tribe_events_register_default_linked_post_types', $default_post_types );

		foreach ( $linked_post_types as $post_type ) {
			$this->register_linked_post_type( $post_type );
		}
	}

	/**
	 * Registers a post type as a linked post type for events
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 * @param array $args Arguments for the linked post type - note: gets merged with get_post_type_object data
	 *
	 * @return boolean
	 */
	public function register_linked_post_type( $post_type, $args = array() ) {
		if ( ! empty( $this->linked_post_type[ $post_type ] ) ) {
			return false;
		}

		if ( ! $post_type_object = get_post_type_object( $post_type ) ) {
			return false;
		}

		$args = wp_parse_args( $args, tribe_object_to_array( $post_type_object ) );

		/**
		 * Filters the post type arguments before adding them to the collection of linked post types
		 *
		 * @since 4.2
		 *
		 * @var array Array of arguments for the post type
		 * @var string Post type slug
		 */
		$args = apply_filters( 'tribe_events_linked_post_type_args', $args, $post_type );

		$this->linked_post_types[ $post_type ] = $args;

		return true;
	}

	/**
	 * Deregisters a post type as a linked post type for events
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 *
	 * @return boolean
	 */
	public function deregister_linked_post_type( $post_type ) {
		if ( empty( $this->linked_post_types[ $post_type ] ) ) {
			return false;
		}

		unset( $this->linked_post_types[ $post_type ] );

		return true;
	}

	/**
	 * Returns whether or not there are any linked posts for the given post id
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 *
	 * @return boolean
	 */
	public function has_linked_posts( $post_id ) {
		$post_types = $this->get_linked_post_types();

		$post_id_post_type = get_post_type( $post_id );

		$args = array(
			'p' => $post_id,
			'post_type' => $post_id_post_type,
			'meta_query' => array(),
		);

		if ( Tribe__Events__Main::POSTTYPE === $post_id_post_type ) {
			// if the post type that we're looking at is an event, we'll need to find all linked post types
			foreach ( $post_types as $post_type => $post_type_data ) {
				$args['meta_query'][] = array(
					'key'     => self::META_KEY_PREFIX . $post_type,
					'compare' => 'EXISTS',
				);
			}
		} else {
			// if the post type is NOT an event post type, we just want to find the associated event posts
			$args['meta_query'][] = array(
				'key'     => self::META_KEY_PREFIX . Tribe__Events__Main::POSTTYPE,
				'compare' => 'EXISTS',
			);
		}

		$args['meta_query']['relation'] = 'OR';

		$query = new WP_Query( $args );

		/**
		 * Filters the results of the query to determine whether or not there are linked posts
		 *
		 * @var boolean Whether or not there are linked posts
		 * @var int Post ID of the post being looked at
		 */
		return apply_filters( 'tribe_events_has_linked_posts', $query->have_posts(), $post_id );
	}

	/**
	 * Returns all linked posts for the given post id
	 *
	 * Post collection is indexed by post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 *
	 * @return array
	 */
	public function get_linked_posts( $post_id ) {
		$post_types = $this->get_linked_post_types();

		$post_id_post_type = get_post_type( $post_id );

		$posts = array();

		if ( Tribe__Events__Main::POSTTYPE === $post_id_post_type ) {
			foreach ( $post_types as $post_type => $post_type_data ) {
				$posts[ $post_type ] = $this->get_linked_posts_by_post_type( $post_id, $post_type );
			}
		} else {
			$post_type = Tribe__Events__Main::POSTTYPE;
			$posts[ $post_type ] = $this->get_linked_posts_by_post_type( $post_id, $post_type );
		}

		/**
		 * Filters the collection of linked posts for the provided post id
		 *
		 * @since 4.2
		 *
		 * @var array Collection of posts linked to the post id
		 * @var int Post ID of the post being looked at
		 */
		return apply_filters( 'tribe_events_get_linked_posts', $posts, $post_id );
	}

	/**
	 * Returns whether or not there are linked posts of the specified post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 * @param string $post_type Post type of linked posts to look for
	 *
	 * @return boolean
	 */
	public function has_linked_posts_by_post_type( $post_id, $post_type ) {
		$has_linked_posts = ! empty( $this->get_linked_posts_by_post_type( $post_id, $post_type ) );

		/**
		 * Filters whether or not a post has any linked posts of a given type
		 *
		 * @since 4.2
		 *
		 * @var boolean Whether or not there are any linked posts for the given post by the given post type
		 * @var int Post ID being looked at
		 * @var string Post type of linked posts
		 */
		$has_linked_posts = apply_filters( 'tribe_events_has_linked_posts_by_post_type', $has_linked_posts, $post_id, $post_type );

		return $has_linked_posts;
	}

	/**
	 * Returns linked posts of the specified post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 * @param string $post_type Post type of linked posts to look for
	 *
	 * @return array
	 */
	public function get_linked_posts_by_post_type( $post_id, $post_type ) {
		$result = array();

		if ( $linked_post_ids = get_post_meta( $post_id, self::META_KEY_PREFIX . $post_type ) ) {
			$args = array(
				'post_type' => $post_type,
				'post__in' => $linked_post_ids,
				'post_status' => 'publish',
			);

			$query = new WP_Query( $args );

			$result = $query->get_posts();
		}

		/**
		 * Filters the linked posts of a given type for the given post
		 *
		 * @since 4.2
		 *
		 * @var array Linked posts for the given post by the given post type
		 * @var int Post ID being looked at
		 * @var string Post type of linked posts
		 */
		return apply_filters( 'tribe_events_get_linked_posts_by_post_type', $result, $post_id, $post_type );
	}

	/**
	 * Returns the linked post types
	 *
	 * @since 4.2
	 *
	 * @return array
	 */
	public function get_linked_post_types() {
		return $this->linked_post_types;
	}

	/**
	 * Returns whether or not there are any linked post types
	 *
	 * @since 4.2
	 *
	 * @return boolean
	 */
	public function has_linked_post_types() {
		return ! empty( $this->linked_post_types );
	}

	/**
	 * Returns whether or not the provided post type is a linked post type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 *
	 * @return boolean
	 */
	public function is_linked_post_type( $post_type ) {
		return ! empty( $this->linked_post_types[ $post_type ] );
	}

	public function link_post( $post_id, $post_id_to_link ) {
		$primary_post_type = get_post_type( $post_id );
		$link_post_type = get_post_type( $post_id_to_link );

		if ( empty( $this->get_linked_post_types[ $link_post_type ] ) ) {
			return false;
		}

		$meta_key = self::META_KEY_PREFIX . $link_post_type;

		$linked_posts = get_post_meta( $post_id, $meta_key );

		// if the post is already linked, don't re-link it
		if ( in_array( $post_id_to_link, $linked_posts ) ) {
			return true;
		}
	}
}
