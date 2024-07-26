<?php

/**
 * Object used to handle the linking/unlinking of post types for events
 */
class Tribe__Events__Linked_Posts {
	/**
	 * @var string Meta key prefix for linked posts.
	 */
	const META_KEY_PREFIX = '_tribe_linked_post_';

	/**
	 * @var Tribe__Events__Linked_Posts Singleton instance of the class.
	 */
	public static $instance;

	/**
	 * @var Tribe__Events__Main Singleton.
	 */
	public $main;

	/**
	 * @var array Collection of post types that can be linked with events.
	 */
	public $linked_post_types;

	/**
	 * @var Tribe__Cache
	 */
	protected $cache;

	/**
	 * Returns a singleton of this class.
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
	 *
	 * @param Tribe__Cache|null $cache
	 */
	public function __construct( Tribe__Cache $cache = null ) {
		$this->cache = null !== $cache ? $cache : tribe( 'cache' );

		$this->main = Tribe__Events__Main::instance();
		$this->register_default_linked_post_types();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		wp_localize_script( 'tribe-events-admin', 'tribe_events_linked_posts', $this->get_post_type_container_data() );
	}

	/**
	 * Generates post_type => container key value pairs of linked post types for use on the front end.
	 */
	public function get_post_type_container_data() {
		$data       = [
			'post_types' => [],
		];

		if ( ! count( (array) $this->linked_post_types ) ) {
			return $data;
		}

		$post_types = array_keys( $this->linked_post_types );

		foreach ( $post_types as $post_type ) {
			$data['post_types'][ $post_type ] = $this->get_post_type_container( $post_type );
		}

		return $data;
	}

	/**
	 * Registers the default linked post types for events.
	 *
	 * @since 4.2
	 */
	public function register_default_linked_post_types() {
		$default_post_types = [
			Tribe__Events__Venue::POSTTYPE,
			Tribe__Events__Organizer::POSTTYPE,
		];

		/**
		 * Filters the list of default registered linked post types.
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
	 * Registers a post type as a linked post type for events.
	 *
	 * Notable arguments that can be passed/filtered while registering linked post types:
	 * - labels['name']
	 * - labels['singular_name']
	 * - allow_multiple (default: true) specifies how many of the post type can be linked with an event
	 * - allow_creation (default: false) specifies whether or not post creation should be allowed
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug.
	 * @param array  $args      Arguments for the linked post type - note: gets merged with get_post_type_object data.
	 *
	 * @return boolean
	 */
	public function register_linked_post_type( $post_type, $args = [] ) {
		if ( $this->is_linked_post_type( $post_type ) ) {
			return false;
		}

		if ( ! $post_type_object = get_post_type_object( $post_type ) ) {
			return false;
		}

		$default_args = [
			'name'                    => $post_type_object->labels->name,
			'singular_name'           => $post_type_object->labels->singular_name,
			'singular_name_lowercase' => $post_type_object->labels->singular_name_lowercase,
			'allow_multiple'          => true,
			'allow_creation'          => false,
		];

		$args = wp_parse_args( $args, $default_args );

		/**
		 * Filters the post type arguments before adding them to the collection of linked post types
		 *
		 * @since 4.2
		 *
		 * @param array  $args      Array of arguments for the post type
		 * @param string $post_type Post type slug
		 */
		$args = apply_filters( 'tribe_events_linked_post_type_args', $args, $post_type );

		$this->linked_post_types[ $post_type ] = $args;

		return true;
	}

	/**
	 * Returns the meta key for the given post type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug.
	 *
	 * @return string
	 */
	public function get_meta_key( $post_type ) {
		if ( Tribe__Events__Venue::POSTTYPE === $post_type ) {
			return '_EventVenueID';
		}

		if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
			return '_EventOrganizerID';
		}

		return self::META_KEY_PREFIX . $post_type;
	}

	/**
	 * Returns the meta key for linked post order
	 *
	 * @deprecated 4.6.23
	 * @todo Remove on 4.7
	 *
	 * @since 4.6.13
	 *
	 * @param string $post_type Post Type
	 *
	 * @return bool|string
	 */
	public function get_order_meta_key( $post_type ) {
		_deprecated_function( __METHOD__, '4.6.23', 'We do not use a separate postmeta field to store the ordering.' );

		if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
			return '_EventOrganizerID_Order';
		}

		/**
		 * This allows for things like Extensions to hook in here and return their own key
		 * See '_EventOrganizerID_Order' above for an example
		 *
		 * @since 4.6.14
		 *
		 * @param bool false (not linked)
		 * @param string $post_type current (potentially linked) post type
		 * @return string
		 */
		return apply_filters( 'tribe_events_linked_post_type_meta_key', false, $post_type );
	}

	/**
	 * Returns the post type's form field container name
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type slug.
	 *
	 * @return string
	 */
	public function get_post_type_container( $linked_post_type ) {
		/**
		 * Filters the array element that contains the post type data in the $_POST object
		 *
		 * @param string Post type index
		 * @param string Post type
		 */
		return apply_filters( 'tribe_events_linked_post_type_container', "linked_{$linked_post_type}", $linked_post_type );
	}

	/**
	 * Returns the post type's ID field name.
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
	 *
	 * @return string
	 */
	public function get_post_type_id_field_index( $linked_post_type ) {
		/**
		 * Filters the array index that contains the post type ID
		 *
		 * @param string $id Post type id index
		 * @param string $linked_post_type Post type
		 */
		return apply_filters( 'tribe_events_linked_post_id_field_index', 'id', $linked_post_type );
	}

	/**
	 * Returns the post type's name field
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
	 *
	 * @return string
	 */
	public function get_post_type_name_field_index( $linked_post_type ) {
		/**
		 * Filters the array index that contains the post name
		 *
		 * @param string $name Post type name index
		 * @param string $linked_post_type Post type
		 */
		return apply_filters( 'tribe_events_linked_post_name_field_index', 'name', $linked_post_type );
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
		if ( $this->is_linked_post_type( $post_type ) ) {
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

		$args = [
			'p'          => $post_id,
			'post_type'  => $post_id_post_type,
			'meta_query' => [],
		];

		if ( Tribe__Events__Main::POSTTYPE === $post_id_post_type ) {
			// if the post type that we're looking at is an event, we'll need to find all linked post types.
			foreach ( $post_types as $post_type => $post_type_data ) {
				$args['meta_query'][] = [
					'key'     => $this->get_meta_key( $post_type ),
					'compare' => 'EXISTS',
				];
			}
		} else {
			// if the post type is NOT an event post type, we just want to find the associated event posts.
			$args['meta_query'][] = [
				'key'     => $this->get_meta_key( Tribe__Events__Main::POSTTYPE ),
				'compare' => 'EXISTS',
			];
		}

		$args['meta_query']['relation'] = 'OR';

		$query = new WP_Query( $args );

		/**
		 * Filters the results of the query to determine whether or not there are linked posts
		 *
		 * @param boolean Whether or not there are linked posts
		 * @param int Post ID of the post being looked at
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

		$posts = [];

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
		 * @param array Collection of posts linked to the post id
		 * @param int Post ID of the post being looked at
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
		$linked_posts_by_post_type = $this->get_linked_posts_by_post_type( $post_id, $post_type );
		$has_linked_posts = ! empty( $linked_posts_by_post_type );

		/**
		 * Filters whether or not a post has any linked posts of a given type
		 *
		 * @since 4.2
		 *
		 * @param boolean Whether or not there are any linked posts for the given post by the given post type
		 * @param int Post ID being looked at
		 * @param string Post type of linked posts
		 */
		$has_linked_posts = apply_filters( 'tribe_events_has_linked_posts_by_post_type', $has_linked_posts, $post_id, $post_type );

		return $has_linked_posts;
	}

	/**
	 * Returns an array of linked post ID(s) of the specified post type.
	 *
	 * @since 4.6.22
	 *
	 * @param int    $post_id   Post ID of the object.
	 * @param string $post_type Post type of linked posts to look for.
	 *
	 * @return array
	 */
	public function get_linked_post_ids_by_post_type( $post_id, $post_type ) {
		$linked_post_meta_key = $this->get_meta_key( $post_type );

		$linked_post_ids = get_post_meta( $post_id, $linked_post_meta_key );

		if ( empty( $linked_post_ids ) || ! is_array( $linked_post_ids ) ) {
			$linked_post_ids = [];
		}

		$linked_post_ids = array_map( 'absint', $linked_post_ids );
		$linked_post_ids = array_filter( $linked_post_ids );
		$linked_post_ids = array_unique( $linked_post_ids );

		/**
		 * Filters the linked post ID(s) of a given type for the given post.
		 *
		 * @since 4.6.22
		 *
		 * @param array $linked_post_ids Linked post ID(s).
		 * @param int $post_id Post ID being looked at.
		 * @param string $post_type Post type of linked posts.
		 */
		return apply_filters( 'tribe_events_get_linked_post_ids_by_post_type', $linked_post_ids, $post_id, $post_type );
	}

	/**
	 * Returns an array of linked WP_Post objects of the specified post type.
	 *
	 * @since 4.2
	 *
	 * @see Tribe__Events__Linked_Posts::get_linked_post_ids_by_post_type
	 *
	 * @param int    $post_id   Post ID of the object.
	 * @param string $post_type Post type of linked posts to look for.
	 *
	 * @return array
	 */
	public function get_linked_posts_by_post_type( $post_id, $post_type ) {
		$existing_linked_post_ids = $this->get_linked_post_ids_by_post_type( $post_id, $post_type );

		$result = $this->get_linked_post_info( $post_type, [], $existing_linked_post_ids );

		/**
		 * Filters the linked posts of a given type for the given post
		 *
		 * @since 4.2
		 *
		 * @param array  $result    Linked posts for the given post by the given post type.
		 * @param int    $post_id   Post ID being looked at.
		 * @param string $post_type Post type of linked posts.
		 */
		return apply_filters( 'tribe_events_get_linked_posts_by_post_type', $result, $post_id, $post_type );
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
	 * Returns the linked post types
	 *
	 * @since 4.2
	 *
	 * @return array
	 */
	public function get_linked_post_types() {
		return (array) $this->linked_post_types;
	}

	/**
	 * Get Linked Post info
	 *
	 * @param string    $linked_post_type   Post type of linked post.
	 * @param array     $args               Extra WP Query args.
	 * @param array|int $linked_post_ids    Post ID(s).
	 *
	 * @return array
	 */
	public function get_linked_post_info( $linked_post_type, $args = [], $linked_post_ids = null ) {
		$func_args = func_get_args();
		$cache_key = $this->cache->make_key( $func_args, 'linked_post_info_' );
		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		/**
		 * Whether to return all linked posts if the args actually find no linked posts.
		 *
		 * @since 4.6.22
		 *
		 * @param bool      $return_all_if_none True if you want all posts returned if none
		 *                                      are found (e.g. creating a drop-down).
		 *                                      False if you want none returned if none are
		 *                                      found (e.g. actually querying for matches).
		 * @param string    $linked_post_type   Post type of linked post.
		 * @param array     $args               WP Query args before merging with defaults.
		 * @param array|int $linked_post_ids    Post ID(s).
		 *
		 * @return bool
		 */
		$return_all_if_none = (bool) apply_filters( 'tribe_events_return_all_linked_posts_if_none', false, $linked_post_type, $args, $linked_post_ids );

		// Explicitly force zero results if appropriate. Necessary because passing an empty array will actually display all posts, per https://core.trac.wordpress.org/ticket/28099.
		if (
			empty( $linked_post_ids )
			&& false === $return_all_if_none
		) {
			$linked_post_ids = [ -1 ];
		}

		$defaults = [
			'post_type'            => $linked_post_type,
			'post_status'          => [
				'publish',
				'draft',
				'private',
				'pending',
			],
			'order'                => 'ASC',
			'orderby'              => 'post__in post_title',
			'ignore_sticky_posts ' => true,
			'nopaging'             => true,
		];

		if ( is_array( $linked_post_ids ) ) {
			$defaults['post__in'] = $linked_post_ids;
		} elseif ( 0 < absint( $linked_post_ids ) ) {
			$defaults['p'] = absint( $linked_post_ids );
		}

		$args = wp_parse_args( $args, $defaults );

		/**
		 * The WP_Query arguments used when getting information per Linked Post.
		 *
		 * Useful if you want to add `orderby` or override existing arguments.
		 *
		 * @param array     $args             The WP_Query arguments.
		 * @param string    $linked_post_type The post type key.
		 * @param int|array $linked_post_ids  A single Linked Post ID or an array of Linked Post IDs.
		 *
		 * @return array
		 */
		$args = apply_filters( 'tribe_events_get_linked_post_info_args', $args, $linked_post_type, $linked_post_ids );

		/**
		 * Filters the linked posts query allowing third-party plugins to replace it.
		 *
		 * This is an opt-out filter: to avoid The Events Calendar from running the linked posts query as it would
		 * normally do third parties should return anything that is not exactly `null` to replace the query and provide
		 * alternative linked posts.
		 *
		 * @param array $linked_posts Defaults to `null`; will be an array if another plugin did run the query.
		 * @param array $args         An array of query arguments in the same format used to provide arguments to WP_Query.
		 *
		 */
		$linked_posts = apply_filters( 'tribe_events_linked_posts_query', null, $args );

		if ( null !== $linked_posts ) {
			return $linked_posts;
		}

		$result = new WP_Query( $args );

		if ( $result->have_posts() ) {
			$linked_posts = $result->posts;
		} else {
			$linked_posts = [];
		}

		$this->cache[ $cache_key ] = $linked_posts;

		return $linked_posts;
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

	/**
	 * Returns whether or not the provided linked post type allows multiple posts of that type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type
	 *
	 * @return boolean
	 */
	public function allow_multiple( $post_type ) {
		return ! empty( $this->linked_post_types[ $post_type ]['allow_multiple'] );
	}

	/**
	 * Links two posts together
	 *
	 * @since 4.2
	 *
	 * @param int $target_post_id Post ID of post to add linked post to
	 * @param int $subject_post_id Post ID of post to add as a linked post to the target
	 *
	 * @return int|false The result of `add_metadata()` - the meta ID on success, false on failure.
	 */
	public function link_post( $target_post_id, $subject_post_id ) {
		$linked_posts      = false;
		$target_post_type  = get_post_type( $target_post_id );
		$subject_post_type = get_post_type( $subject_post_id );

		if (
			Tribe__Events__Main::POSTTYPE !== $target_post_type
			&& Tribe__Events__Main::POSTTYPE === $subject_post_type
		) {
			// swap the post IDs and post types around so we are assigning in the correct direction.
			$temp_post_id    = $target_post_id;
			$target_post_id  = $subject_post_id;
			$subject_post_id = $temp_post_id;

			$temp_post_type    = $target_post_type;
			$target_post_type  = $subject_post_type;
			$subject_post_type = $temp_post_type;
		}

		if ( ! $this->is_linked_post_type( $subject_post_type ) ) {
			return $linked_posts;
		}

		$subject_meta_key  = $this->get_meta_key( $subject_post_type );
		$target_link_posts = get_post_meta( $target_post_id, $subject_meta_key );

		// if the subject isn't in the target's linked posts, add it.
		if ( ! in_array( $subject_post_id, $target_link_posts ) ) {
			// if multiples are not allowed, make sure we remove all linked posts of that type before we.
			// link the new one.
			if ( ! $this->allow_multiple( $subject_post_type ) ) {
				foreach ( $target_link_posts as $attached_post ) {
					$this->unlink_post( $target_post_id, $attached_post );
				}
			}

			// add the subject to the target.
			$linked_posts = add_metadata( 'post', $target_post_id, $subject_meta_key, $subject_post_id );
		}

		if ( $linked_posts ) {
			/**
			 * Fired after two posts have been linked
			 *
			 * @param int Post ID of post to add linked post to
			 * @param int Post ID of post to add as a linked post to the target
			 */
			do_action( 'tribe_events_link_post', $target_post_id, $subject_post_id );
		}

		return $linked_posts;
	}

	/**
	 * Save Order of Linked Posts
	 *
	 * @deprecated 4.6.23
	 * @todo Remove on 4.7
	 *
	 * @since 4.6.13
	 *
	 * @param int $target_post_id post id to save meta from
	 * @param string $post_type the post-type to get the key for
	 * @param array $current_order an array of the linked post ids being saved
	 */
	public function order_linked_posts( $target_post_id, $post_type, $current_order ) {
		_deprecated_function( __METHOD__, '4.6.23', 'Linked posts are ordered by `meta_id` by default via `get_post_meta()`.' );

		$linked_ids_order_key = $this->get_order_meta_key( $post_type );

		if ( ! $linked_ids_order_key ) {
			return;
		}

		update_post_meta( $target_post_id, $linked_ids_order_key, $current_order );
	}

	/**
	 * Unlinks two posts from each other.
	 *
	 * @since 4.2
	 *
	 * @param int $target_post_id Post ID of post to remove linked post from
	 * @param int $subject_post_id Post ID of post to remove as a linked post from the target
	 */
	public function unlink_post( $target_post_id, $subject_post_id ) {
		$target_post_type  = get_post_type( $target_post_id );
		$subject_post_type = get_post_type( $subject_post_id );

		if (
			Tribe__Events__Main::POSTTYPE !== $target_post_type
			&& Tribe__Events__Main::POSTTYPE === $subject_post_type
		) {
			// swap the post IDs and post types around so we are assigning in the correct direction.
			$temp_post_id    = $target_post_id;
			$target_post_id  = $subject_post_id;
			$subject_post_id = $temp_post_id;

			$temp_post_type    = $target_post_type;
			$target_post_type  = $subject_post_type;
			$subject_post_type = $temp_post_type;
		}

		$subject_meta_key  = $this->get_meta_key( $subject_post_type );

		delete_metadata( 'post', $target_post_id, $subject_meta_key, $subject_post_id );

		/**
		 * Fired after two posts have been unlinked
		 *
		 * @since 4.2
		 *
		 * @param int Post ID of post to add linked post to
		 * @param int Post ID of post to add as a linked post to the target
		 */
		do_action( 'tribe_events_unlink_post', $target_post_id, $subject_post_id );
	}

	/**
	 * Detects linked post type data within a form submission and executes the post type-specific handlers
	 *
	 * @since 4.2
	 *
	 * @param int $event_id Submitted Event ID
	 * @param array $submission Submitted form data
	 */
	public function handle_submission( $event_id, $submission ) {
		$linked_post_types = $this->get_linked_post_types();

		foreach ( $linked_post_types as $linked_post_type => $linked_post_type_data ) {
			$linked_post_type_data = $this->get_linked_post_type_data( $submission, $linked_post_type );
			$this->handle_submission_by_post_type( $event_id, $linked_post_type, $linked_post_type_data );

			if ( ! $linked_post_type_data && has_blocks( $event_id ) ) {
				$meta_key              = $this->get_meta_key( $linked_post_type );
				$current_post_id_order = get_post_meta( $event_id, $meta_key, false );
				$new_post_id_order     = $this->maybe_get_new_order_from_blocks( $event_id, $linked_post_type, $current_post_id_order );

				$this->maybe_reorder_linked_posts_ids( $event_id, $linked_post_type, $new_post_id_order, $current_post_id_order );
			}
		}
	}

	/**
	 * Handles the submission of linked post data
	 *
	 * @since 4.2
	 *
	 * @param int   $event_id   Submitted Event ID.
	 * @param int   $post_type  Post type of linked post.
	 * @param array $submission Submitted form data.
	 */
	public function handle_submission_by_post_type( $event_id, $linked_post_type, $submission ) {
		// If the submission isn't an array, bail.
		// This is here to avoid unexpected data.
		// And also to avoid errantly removing linked posts just because they were not part of the submission, in which case this will be `false` from `$this->get_linked_post_type_data()`.
		if ( ! is_array( $submission ) ) {
			return;
		}

		$linked_post_type_object   = get_post_type_object( $linked_post_type );
		$linked_post_type_id_field = $this->get_post_type_id_field_index( $linked_post_type );
		$post_ids_to_link          = [];
		$event_post_status         = get_post_status( $event_id );

		// Prevents Revisions from been Linked.
		if ( 'inherit' === $event_post_status ) {
			return;
		}

		$temp_submission = $submission;
		$submission      = [];

		// make sure all elements are arrays.
		foreach ( $temp_submission as $key => $value ) {
			$submission[ $key ] = is_array( $value ) ? $value : [ $value ];
		}

		// setup key(s) if all new post(s).
		if ( ! isset( $submission[ $linked_post_type_id_field ] ) ) {
			$first_item                               = current( $submission );
			$multiple_posts                           = is_array( $first_item ) ? count( $first_item ) - 1 : 0;
			$submission[ $linked_post_type_id_field ] = [];
			$post_count                               = 0;

			do {
				$submission[ $linked_post_type_id_field ][] = '';
				$post_count ++;
			} while ( $multiple_posts > $post_count );
		}

		$fields = array_keys( $submission );

		foreach ( $submission[ $linked_post_type_id_field ] as $key => $id ) {
			// Reset to 0 case of -1.
			if ( -1 === (int) $id ) {
				$id = null;
				$submission[ $linked_post_type_id_field ][ $key ] = $id;
			}

			if ( ! empty( $id ) ) {
				$post_ids_to_link[] = absint( $id );
				continue;
			}

			// If the user doesn't have permission to create this type of post, don't allow for creation.
			if (
				empty( $linked_post_type_object->cap->create_posts )
				|| ! current_user_can( $linked_post_type_object->cap->create_posts )
			) {
				continue;
			}

			$data = [];
			foreach ( $fields as $field_name ) {
				if ( is_array( $submission[ $field_name ] ) ) {
					// If allow_multiple is true then each submission field may be an array.
					$data[ $field_name ] = isset( $submission[ $field_name ][ $key ] ) ? $submission[ $field_name ][ $key ] : null;
				} else {
					// In other cases, such as if multiple is false each submission field will contain a single value.
					$data[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : null;
				}
			}

			// set the post status to the event post status.
			$post_status = $event_post_status;

			/**
			 * Filters the ID (default null) for creating posts from the event edit page.
			 *
			 * @param string $id               Post type ID index.
			 * @param array  $data             Data for submission.
			 * @param string $linked_post_type Post type.
			 * @param string $post_status      Post status.
			 * @param int    $event_id         Post ID of the Event the Linked Post is attached to.
			 */
			$id = apply_filters( 'tribe_events_linked_post_create_' . $linked_post_type, null, $data, $linked_post_type, $post_status, $event_id );

			/**
			 * Filters the ID (default null) for creating posts from the event edit page.
			 *
			 * @param string $id               Post type id index.
			 * @param array  $data             Data for submission.
			 * @param string $linked_post_type Post type.
			 * @param string $post_status      Post status.
			 * @param int    $event_id         Post ID of the Event the Linked Post is attached to.
			 */
			$id = apply_filters( 'tribe_events_linked_post_create', $id, $data, $linked_post_type, $post_status, $event_id );

			if ( ! empty( $id ) ) {
				$post_ids_to_link[] = $id;
			}
		}

		$post_ids_to_link = array_map( 'absint', $post_ids_to_link );
		$post_ids_to_link = array_filter( $post_ids_to_link );
		$post_ids_to_link = array_unique( $post_ids_to_link );

		// If we do not allow multiples for this post type, ignore all but the first.
		if (
			! $this->allow_multiple( $linked_post_type )
			&& 1 < count( $post_ids_to_link )
		) {
			$post_ids_to_link = [ $post_ids_to_link[0] ];
		}

		$prior_linked_posts = $this->get_linked_post_ids_by_post_type( $event_id, $linked_post_type );

		// If no pre-existing posts and no new posts to add, bail.
		if (
			empty( $prior_linked_posts )
			&& empty( $post_ids_to_link )
		) {
			return;
		}

		$post_ids_to_link = $this->maybe_get_new_order_from_blocks( $event_id, $linked_post_type, $post_ids_to_link );
		$this->maybe_reorder_linked_posts_ids( $event_id, $linked_post_type, $post_ids_to_link, $prior_linked_posts );
	}

	/**
	 * Re-orders linked posts if the order has changed.
	 *
	 * @since 6.2.0
	 *
	 * @param int    $event_id Event ID.
	 * @param string $linked_post_type The post type of the linked post.
	 * @param array  $new_order The new order of the linked posts.
	 * @param array  $old_order The old order of the linked posts.
	 *
	 * @return bool
	 */
	public function maybe_reorder_linked_posts_ids( int $event_id, string $linked_post_type, array $new_order = [], array $old_order = [] ): bool {
		// If the array values match both type and value and ordering, no need to touch postmeta.
		if ( $old_order === $new_order ) {
			return false;
		}

		$linked_post_type_meta_key = $this->get_meta_key( $linked_post_type );
		$temp_old_order            = $old_order;

		// Re-save postmeta if not matching all these conditions.
		$sorted_old = $old_order;
		sort( $sorted_old, SORT_NUMERIC );

		$sorted_new = $new_order;
		sort( $sorted_new, SORT_NUMERIC );

		if ( $sorted_old === $sorted_new ) {
			// If the post IDs are the same (none new nor removed) but not in the same order.

			// We do not run our own unlink/link methods because we are not doing that, just re-ordering via `meta_id` by removing all and re-adding in the desired order.
			delete_post_meta( $event_id, $linked_post_type_meta_key );

			foreach ( $new_order as $linked_post_id ) {
				add_post_meta( $event_id, $linked_post_type_meta_key, $linked_post_id );
			}
		} else {
			// We have different Linked Post IDs (adding and/or removing one or more) so possibly need to run through our own methods to trigger those hooks.
			$posts_to_remove = array_diff( $old_order, $new_order );

			foreach ( $posts_to_remove as $key => $unlinked_post_id ) {
				$this->unlink_post( $event_id, $unlinked_post_id );
				unset( $temp_old_order[ $key ] );
			}

			// Remove all pre-existing (and non-removed) linked posts to start fresh by re-adding below (for `meta_id` ordering purposes).
			if ( ! empty( $temp_old_order ) ) {
				delete_post_meta( $event_id, $linked_post_type_meta_key );
			}

			foreach ( $new_order as $linked_post_id ) {
				if ( in_array( $linked_post_id, $old_order ) ) {
					// Re-add pre-existing ones without our own method because we do not want to trigger those hooks.
					add_post_meta( $event_id, $linked_post_type_meta_key, $linked_post_id );
				} else {
					// Add newly-linked ones via our own method in order to trigger such hooks.
					$this->link_post( $event_id, $linked_post_id );
				}
			}
		}

		return true;
	}

	/**
	 * Reorder the meta keys to match the block order.
	 *
	 * @since 6.2.0
	 *
	 * @param int    $event_id         Event ID.
	 * @param string $linked_post_type The post type of the linked post.
	 * @param array  $original_order   The original IDs/order stored in meta.
	 *
	 * @return array The new order of blocks if modified.
	 */
	public function maybe_get_new_order_from_blocks( int $event_id, string $linked_post_type, array $original_order = [] ) {
		// If the post has blocks, we need to update sorting of the post ids to link so it matches block order.
		if ( ! has_blocks( $event_id ) ) {
			return $original_order;
		}

		$new_order = [];
		$blocks = parse_blocks( get_the_content( null, false, $event_id ) );

		$block_name = 'tribe/event-venue';
		$block_id_key = 'venue';
		if ( $linked_post_type === \Tribe__Events__Organizer::POSTTYPE ) {
			$block_name = 'tribe/event-organizer';
			$block_id_key = 'organizer';
		}

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === $block_name && isset( $block['attrs'][ $block_id_key ] ) ) {
				$new_order[] = $block['attrs'][ $block_id_key ];
			}
		}

		// To make sure we don't have data loss, let's prioritize blocks followed by the rest of the post ids and then remove duplicates.
		return array_map( 'absint', array_filter( array_unique( array_merge( $new_order, $original_order ) ) ) );
	}

	/**
	 * Renders the option passed in the param.
	 *
	 * @since 5.1.0
	 *
	 * @param array $option Array with the option values to render the HTML for Select Option.
	 *
	 * @return bool
	 */
	private function render_select_option( $option = [] ) {
		if ( empty( $option['text'] ) || empty( $option['id'] ) ) {
			return false;
		}

		if ( ! isset( $option['selected'] ) ) {
			$option['selected'] = false;
		}

		if ( ! isset( $option['edit'] ) ) {
			$option['edit'] = false;
		}


		?>
		<option
			<?php selected( $option['selected'] ); ?>
			value="<?php echo esc_attr( $option['id'] ); ?>"
			data-edit-link="<?php echo esc_url( $option['edit'] ); ?>"
			data-existing-post="1"
		>
			<?php echo esc_html( $option['text'] ); ?>
		</option>
		<?php
	}

	/**
	 * Helper function for displaying dropdowns for linked post types
	 *
	 * @param string $post_type Post type to display dropdown for.
	 * @param mixed  $current   The current saved linked post item.
	 */
	public function saved_linked_post_dropdown( $post_type, $current = null ) {
		$post_type_object           = get_post_type_object( $post_type );
		$linked_post_type_container = $this->get_post_type_container( $post_type );
		$linked_post_type_id_field  = $this->get_post_type_id_field_index( $post_type );
		$name                       = "{$linked_post_type_container}[{$linked_post_type_id_field}][]";
		$my_linked_post_ids         = [];
		$current_user               = wp_get_current_user();
		$can_edit_others_posts      = current_user_can( $post_type_object->cap->edit_others_posts );

		$plural_name             = $this->linked_post_types[ $post_type ]['name'];
		$singular_name           = ! empty( $this->linked_post_types[ $post_type ]['singular_name'] ) ? $this->linked_post_types[ $post_type ]['singular_name'] : $plural_name;
		$singular_name_lowercase = ! empty( $this->linked_post_types[ $post_type ]['singular_name_lowercase'] ) ? $this->linked_post_types[ $post_type ]['singular_name_lowercase'] : $singular_name;

		$options = (object) [
			'owned'     => [
				'text'     => sprintf( esc_html__( 'My %s', 'the-events-calendar' ), $plural_name ),
				'children' => [],
			],
			'available' => [
				'text'     => sprintf( esc_html__( 'Available %s', 'the-events-calendar' ), $plural_name ),
				'children' => [],
			],
		];

		// backwards compatibility with old organizer filter.
		if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
			/**
			 * Filters the linked organizer dropdown optgroup label that holds organizers that have
			 * been created by that user
			 *
			 * @deprecated 4.2
			 *
			 * @param string $my_optgroup_name Label of the optgroup for the "My Organizers" section
			 */
			$options->owned['text'] = apply_filters( 'tribe_events_saved_organizers_dropdown_my_optgroup', $options->owned['text'] );

			/**
			 * Filters the linked organizer dropdown optgroup label for saved organizers
			 *
			 * @deprecated 4.2
			 *
			 * @param string $my_optgroup_name Label of the optgroup for the "Available Organizers" section
			 */
			$options->available['text'] = apply_filters( 'tribe_events_saved_organizers_dropdown_optgroup', $options->available['text'] );
		}

		/**
		 * Filters the linked post dropdown optgroup label that holds organizers that have
		 * been created by that user
		 *
		 * @since  4.2
		 *
		 * @param string $my_optgroup_name Label of the optgroup for the "My X" section
		 * @param string $post_type Post type of the linked post
		 */
		$options->owned['text'] = apply_filters( 'tribe_events_saved_linked_post_dropdown_my_optgroup', $options->owned['text'], $post_type );

		/**
		 * Filters the linked post dropdown optgroup label that holds all published posts of the given type
		 *
		 * @since  4.2
		 *
		 * @param string $my_optgroup_name Label of the optgroup for the "Available X" section
		 * @param string $post_type Post type of the linked post
		 */
		$options->available['text'] = apply_filters( 'tribe_events_saved_linked_post_dropdown_optgroup', $options->available['text'], $post_type );

		add_filter( 'tribe_events_return_all_linked_posts_if_none', '__return_true' );

		$available_post_status = [
			'publish',
			'draft',
			'private',
			'pending',
		];

		/**
		 *  Filters the available post statuses that are used to retrieve `my posts`.
		 *
		 * @since 6.0.13
		 *
		 * @param array  $available_post_status Array of available post status. Example: publish, draft, private, pending
		 * @param string $post_type Post type of the linked post
		 */
		$my_posts_post_status = apply_filters( 'tec_events_linked_posts_my_posts_post_status', $available_post_status, $post_type );

		$my_linked_posts = $this->get_linked_post_info( $post_type, [
			'post_status' => $my_posts_post_status,
			'author'      => $current_user->ID,
		] );

		if ( ! empty( $my_linked_posts ) ) {
			foreach ( $my_linked_posts as $my_linked_post ) {
				$my_linked_post_ids[] = $my_linked_post->ID;

				$new_child = [
					'id'   => $my_linked_post->ID,
					'text' => wp_kses( get_the_title( $my_linked_post->ID ), [] ),
				];

				$new_child['selected'] = ( (int) $current === (int) $my_linked_post->ID );

				$edit_link = get_edit_post_link( $my_linked_post );

				if ( ! empty( $edit_link ) ) {
					$new_child['edit'] = $edit_link;
				}

				$options->owned['children'][] = $new_child;
			}
		}

		if ( $can_edit_others_posts ) {

			/**
			 *  Filters the available post statuses that are used to retrieve ` posts`.
			 *
			 * @since 6.0.13
			 *
			 * @param array  $available_post_status Array of available post status. Example: publish, draft, private, pending
			 * @param string $post_type Post type of the linked post
			 */
			$all_posts_post_status = apply_filters( 'tec_events_linked_posts_all_posts_post_status', $available_post_status, $post_type );


			$linked_posts = $this->get_linked_post_info( $post_type, [
				'post_status'  => $all_posts_post_status,
				'post__not_in' => $my_linked_post_ids,
			] );
		} else {
			$linked_posts = $this->get_linked_post_info(
				$post_type,
				[
					'post_status'  => 'publish',
					'post__not_in' => $my_linked_post_ids,
				]
			);
		}

		remove_filter( 'tribe_events_return_all_linked_posts_if_none', '__return_true' );

		if ( $linked_posts ) {
			foreach ( $linked_posts as $linked_post ) {
				$new_child = [
					'id'   => $linked_post->ID,
					'text' => wp_kses( get_the_title( $linked_post->ID ), [] ),
				];

				$new_child['selected'] = ( (int) $current === (int) $linked_post->ID );

				$edit_link = get_edit_post_link( $linked_post );

				if ( ! empty( $edit_link ) ) {
					$new_child['edit'] = $edit_link;
				}

				$options->available['children'][] = $new_child;
			}
		}

		// Clean Both Options.
		$options->owned['children']     = array_filter( $options->owned['children'] );
		$options->available['children'] = array_filter( $options->available['children'] );

		if ( empty( $options->owned['children'] ) ) {
			// When Owned is empty, we only use Available.
			$data = $options->available['children'];
		} elseif ( empty( $options->available['children'] ) ) {
			// When Available is empty, we only use Owned.
			$data = $options->owned['children'];
		} else {
			// If we have both we make it an array.
			$data = array_values( (array) $options );
		}

		$user_can_create  = ( ! empty( $post_type_object->cap->create_posts ) && current_user_can( $post_type_object->cap->create_posts ) );
		$allowed_creation = ( ! empty( $this->linked_post_types[ $post_type ]['allow_creation'] ) && $this->linked_post_types[ $post_type ]['allow_creation'] );

		/**
		 * Controls whether the UI to create new linked posts should be displayed.
		 *
		 * @since 4.5.7
		 *
		 * @param bool $enabled
		 * @param string $post_type
		 * @param Tribe__Events__Linked_Posts
		 */
		$creation_enabled = apply_filters( 'tribe_events_linked_posts_dropdown_enable_creation', $user_can_create && $allowed_creation, $post_type, $this );

		// Get the label to use in placeholder attrs.
		$label = $this->get_create_or_find_labels( $post_type, $creation_enabled );

		if ( $linked_posts || $my_linked_posts ) {
			?>
			<select
				class="tribe-dropdown linked-post-dropdown hide-before-select2-init"
				name="<?php echo esc_attr( $name ); ?>"
				id="saved_<?php echo esc_attr( $post_type ); ?>"
				data-post-type="<?php echo esc_attr( $post_type ); ?>"
				data-placeholder="<?php echo esc_attr( $label ); ?>"
				data-search-placeholder="<?php echo esc_attr( $label ); ?>"
				<?php if ( $creation_enabled ) : ?>
				data-freeform
				data-sticky-search
				data-create-choice-template="<?php echo __( 'Create: <%= term %>', 'the-events-calendar' ); ?>"
				data-allow-html
				data-force-search
				<?php endif; ?>
			>
				<option value="-1" <?php selected( empty( $current ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
				<?php if ( ! empty( $data[0]['children'] ) ) : ?>
					<?php foreach ( $data as $group ) : ?>
						<optgroup label="<?php echo esc_attr( $group['text'] ); ?>">
							<?php foreach ( $group['children'] as $value ) : ?>
								<?php $this->render_select_option( $value ); ?>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ( $data as $value ) : ?>
						<?php $this->render_select_option( $value ); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<?php
		} else {
			echo '<p class="nosaved">' . sprintf( esc_attr__( 'No saved %s exists.', 'the-events-calendar' ), $singular_name_lowercase ) . '</p>';
			printf( '<input type="hidden" name="%s" value="%d"/>', esc_attr( $name ), 0 );
		}
	}

	/**
	 * Outputs the metabox form sections for our linked posttypes.
	 *
	 * @param $event
	 */
	public function render_meta_box_sections( $event ) {
		/**
		 * A filter to control which linked posts will automatically render a metabox inside the editor.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string> List of post types that are linked to the main tribe post type.
		 *
		 * @returns array<string> The list of post types we should render metaboxes for via the default means.
		 */
		$linked_post_types = apply_filters( 'tribe_events_linked_posts_should_render_meta_box', $this->linked_post_types );

		foreach ( $linked_post_types as $linked_post_type => $linked_post_type_data ) {
			$template = apply_filters( 'tribe_events_linked_post_meta_box_section', $this->main->plugin_path . 'src/admin-views/linked-post-section.php', $linked_post_type );
			include $template;
		}
	}

	/**
	 * A several-step process that prints the "Create or Find {Linked Post Type Name}" labels.
	 *
	 * Numerous steps and caveats are covered in this method so that we can make these labels, which
	 * are rather important, as translation-friendly as possible.
	 *
	 * @since 4.6.3
	 *
	 * @param object $post_type The linked post type whose label is being rendered.
	 * @param boolean $creation_enabled Whether the current user can create post types. If false, they can only add existing ones.
	 *
	 * @return string
	 */
	public function get_create_or_find_labels( $post_type, $creation_enabled  ) {

		$plural_name             = $this->linked_post_types[ $post_type ]['name'];
		$singular_name           = ! empty( $this->linked_post_types[ $post_type ]['singular_name'] ) ? $this->linked_post_types[ $post_type ]['singular_name'] : $plural_name;
		$singular_name_lowercase = ! empty( $this->linked_post_types[ $post_type ]['singular_name_lowercase'] ) ? $this->linked_post_types[ $post_type ]['singular_name_lowercase'] : $singular_name;

		// First, determine what indefinite article we should use for the post type (important for English and several other languages).
		$indefinite_article = _x( 'a', 'Indefinite article for the phrase "Find a {post type name}. Will be replaced with "an" if the {post type name} starts with a vowel.', 'the-events-calendar' );

		$post_type_starts_with = substr( $singular_name, 0, 1 );
		$post_type_starts_with = strtolower( $post_type_starts_with );
		$english_vowels        = [ 'a', 'e', 'i', 'o', 'u' ];

		if ( in_array( $post_type_starts_with, $english_vowels ) ) {
			$indefinite_article = _x( 'an', 'Indefinite article for the phrase "Find a {post type name}" when the {post type name} starts with a vowel, e.g. "Find an Organizer".', 'the-events-calendar' );
		}

		// Here we render the main label string. The "core" linked post types (venue and organizer) are explicitly named to make.
		// translation a bit easier for the many languages where the words *around* the post type name may need to be different.
		// based on the specific post type name. For non-"core" post types, we just dynamically populate the post type name.
		switch ( $post_type ) {

			// Organizers.
			case Tribe__Events__Organizer::POSTTYPE :

				if ( tribe_is_organizer_label_customized() ) {
					$label = esc_attr(
						sprintf(
							_x( 'Find %1$s %2$s', '"Find an Organizer", but when the word "Organizer" is customized to something else.', 'the-events-calendar' ),
							$indefinite_article,
							$singular_name
						)
					);

					if ( $creation_enabled ) {
						$label = esc_attr(
							sprintf(
								_x( 'Create or Find %s', '"Create or Find Organizer", but when the word "Organizer" is customized to something else.', 'the-events-calendar' ),
								$singular_name
							)
						);
					}
				} else {
					$label = $creation_enabled
						? esc_attr__( 'Create or Find an Organizer', 'the-events-calendar' )
						: esc_attr__( 'Find an Organizer', 'the-events-calendar' );
				}

				break;

			// Venues.
			case Tribe__Events__Venue::POSTTYPE :

				if ( tribe_is_venue_label_customized() ) {
					$label = esc_attr(
						sprintf(
							_x( 'Find %1$s %2$s', '"Find a Venue", but when the word "Venue" is customized to something else.', 'the-events-calendar' ),
							$indefinite_article,
							$singular_name
						)
					);

					if ( $creation_enabled ) {
						$label = esc_attr(
							sprintf(
								_x( 'Create or Find %s', '"Create or Find Venue", but when the word "Venue" is customized to something else.', 'the-events-calendar' ),
								$singular_name
							)
						);
					}
				} else {
					$label = $creation_enabled
						? esc_attr__( 'Create or Find a Venue', 'the-events-calendar' )
						: esc_attr__( 'Find a Venue', 'the-events-calendar' );
				}

				break;

			// Any other potential Linked Post types.
			default :
				$label = esc_attr(
					sprintf(
						_x( 'Find %1$s %2$s', 'The "Find a {post type name}" label for custom linked post types that are *not* Venues or Organizers', 'the-events-calendar' ),
						$indefinite_article,
						$singular_name
					)
				);

				if ( $creation_enabled ) {
					$label = esc_attr(
						sprintf(
							_x( 'Create or Find %s', 'The "Create or Find {post type name}" label for custom linked post types that are *not* Venues or Organizers', 'the-events-calendar' ),
							$singular_name
						)
					);
				}

				break;
		}

		return $label;
	}

	/**
	 * Get the data from a submission that is specific to a single linked post type.
	 *
	 * @param $submission
	 * @param $linked_post_type
	 *
	 * @return bool|array False if linked post type is not part of this submission but linked posts exist prior to this
	 *                    submission. Else an array of the data specific to this linked post type, which may be empty.
	 */
	private function get_linked_post_type_data( $submission, $linked_post_type ) {
		$linked_post_type_container = $this->get_post_type_container( $linked_post_type );

		// Allow for the post type container to have first letter in uppercase form.
		// e.g. `venue` and `Venue` should both be valid.
		$linked_post_type_containers_candidates = [
			$linked_post_type_container,
			ucfirst( $linked_post_type_container ),
		];

		$post_type_container = false;

		foreach ( $linked_post_type_containers_candidates as $candidate_post_type_container ) {
			if ( isset( $submission[ $candidate_post_type_container ] ) ) {
				$post_type_container = $candidate_post_type_container;
				break;
			}
		}

		if ( false === $post_type_container ) {
			$data = [];
		} else {
			// may be an empty array.
			$data = $submission[ $post_type_container ];
		}

		// If the reason for the empty array is because this linked post type is not part of the submission.
		// Which is possible even if `$post_type_container` is not `false`.
		if ( empty( $data ) ) {
			if ( ! empty( $submission['ID'] ) ) {
				$existing_posts = $this->get_linked_posts_by_post_type( $submission['ID'], $linked_post_type );
			}

			if ( ! empty( $existing_posts ) ) {
				/**
				 * False signals to `$this->handle_submission_by_post_type()` that this linked post type is not part of
				 * the submission but existing linked posts exist, and we shouldn't drop them, which is what would
				 * happen if we passed an empty array.
				 * Example: We shouldn't remove all pre-existing Organizers from an event just because editing
				 * Organizers is available in the wp-admin event edit screen but not available in the Community Events form.
				 */
				$data = false;
			}
		}

		return $data;
	}
}
