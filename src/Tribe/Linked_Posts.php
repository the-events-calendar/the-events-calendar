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
	public function register_default_linked_post_types() {
		$default_post_types = array(
			Tribe__Events__Main::VENUE_POST_TYPE,
			Tribe__Events__Main::ORGANIZER_POST_TYPE,
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
	 * Notable arguments that can be passed/filtered while registering linked post types:
	 * - All of the fields returned by get_post_type_object()
	 * - allow_multiple (default: true) specifies how many of the post type can be linked with an event
   * - add_form (defalt: null) an array of information on the add form that will appear on the event
   *     editor page. Elements are:
   *       - template: path to the template to use as the "add" form
   *       - handler: callable used to handle add form submissions
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 * @param array $args Arguments for the linked post type - note: gets merged with get_post_type_object data
	 *
	 * @return boolean
	 */
	public function register_linked_post_type( $post_type, $args = array() ) {
		if ( $this->is_linked_post_type( $post_type ) ) {
			return false;
		}

		if ( ! $post_type_object = get_post_type_object( $post_type ) ) {
			return false;
		}

		$args = wp_parse_args( $args, tribe_object_to_array( $post_type_object ) );

		// default to allowing multiple
		$args['allow_multiple'] = true;
		$args['add_form']       = null;

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
	 * Returns the meta key for the given post type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post Type
	 *
	 * @return string
	 */
	public function get_meta_key( $post_type ) {
		if ( 'tribe_venue' === $post_type ) {
			return '_EventVenueID';
		}

		if ( 'tribe_organizer' === $post_type ) {
			return '_EventOrganizerID';
		}

		return self::META_KEY_PREFIX . $post_type;
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

		$args = array(
			'p' => $post_id,
			'post_type' => $post_id_post_type,
			'meta_query' => array(),
		);

		if ( Tribe__Events__Main::POSTTYPE === $post_id_post_type ) {
			// if the post type that we're looking at is an event, we'll need to find all linked post types
			foreach ( $post_types as $post_type => $post_type_data ) {
				$args['meta_query'][] = array(
					'key'     => $this->get_meta_Key( $post_type ),
					'compare' => 'EXISTS',
				);
			}
		} else {
			// if the post type is NOT an event post type, we just want to find the associated event posts
			$args['meta_query'][] = array(
				'key'     => $this->get_meta_key( Tribe__Events__Main::POSTTYPE ),
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

		if ( $linked_post_ids = get_post_meta( $post_id, $this->get_meta_key( $post_type ) ) ) {
			$args = array(
				'post_type'   => $post_type,
				'post__in'    => $linked_post_ids,
				'post_status' => 'publish',
				'order'       => 'ASC',
				'orderby'     => 'title',
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
	 * @return boolean
	 */
	public function link_post( $target_post_id, $subject_post_id ) {
		$linked_posts      = false;
		$target_post_type  = get_post_type( $target_post_id );
		$subject_post_type = get_post_type( $subject_post_id );

		if (
			Tribe__Events__Main::POSTTYPE !== $target_post_type
			&& Tribe__Events__Main::POSTTYPE === $subject_post_type
		) {
			// swap the post IDs and post types around so we are assigning in the correct direction
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

		$subject_meta_key   = $this->get_meta_key( $subject_post_type );

		$target_link_posts  = get_post_meta( $target_post_id, $subject_meta_key );

		// if the subject isn't in the target's linked posts, add it
		if ( ! in_array( $subject_post_id, $target_link_posts ) ) {
			// if multiples are not allowed, make sure we remove all linked posts of that type before we
			// link the new one
			if ( ! $this->allow_multiple( $subject_post_type ) ) {
				foreach ( $target_link_posts as $attached_post ) {
					$this->unlink_post( $target_post_id, $attached_post );
				}
			}

			// add the subject to the target
			$linked_posts = add_post_meta( $target_post_id, $subject_meta_key, $subject_post_id );
		}

		if ( $linked_posts ) {
			/**
			 * Fired after two posts have been linked
			 *
			 * @var int Post ID of post to add linked post to
			 * @var int Post ID of post to add as a linked post to the target
			 */
			do_action( 'tribe_events_link_post', $target_post_id, $subject_post_id );
		}

		return $linked_posts;
	}

	/**
	 * Unlinks two posts from eachother
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
			// swap the post IDs and post types around so we are assigning in the correct direction
			$temp_post_id    = $target_post_id;
			$target_post_id  = $subject_post_id;
			$subject_post_id = $temp_post_id;

			$temp_post_type    = $target_post_type;
			$target_post_type  = $subject_post_type;
			$subject_post_type = $temp_post_type;
		}

		$subject_meta_key  = $this->get_meta_key( $subject_post_type );

		delete_post_meta( $target_post_id, $subject_meta_key, $subject_post_id );

		/**
		 * Fired after two posts have been unlinked
		 *
		 * @since 4.2
		 *
		 * @var int Post ID of post to add linked post to
		 * @var int Post ID of post to add as a linked post to the target
		 */
		do_action( 'tribe_events_unlink_post', $target_post_id, $subject_post_id );
	}

	/**
	 * Sets the "add" form template and submission handler for the given post type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post Type
	 * @param string $template Template path for the "add" form
	 * @param string $handler Form parsing handler
	 *
	 * @return boolean
	 */
	public function set_add_form( $post_type, $template, $handler ) {
		if ( ! $this->is_linked_post_type( $post_type ) ) {
			return false;
		}

		if ( ! is_callable( $handler ) ) {
			return false;
		}

		$this->linked_post_types[ $post_type ]['add_form']             = array();
		$this->linked_post_types[ $post_type ]['add_form']['template'] = $template;
		$this->linked_post_types[ $post_type ]['add_form']['handler']  = $handler;

		return true;
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
			/**
			 * Filters the array element that contains the post type data in the $_POST object
			 *
			 * @var string Post type index
			 * @var string Post type
			 */
			$linked_post_type_container = apply_filters( 'tribe_events_linked_post_type_container', "linked_{$linked_post_type}", $linked_post_type );

			if ( ! isset( $submission[ $linked_post_type_container ] ) ) {
				$submission[ $linked_post_type_container ] = array();
			}

			$this->handle_submission_by_post_type( $event_id, $linked_post_type, $submission[ $linked_post_type_container ] );
		}
	}

	/**
	 * Handles the submission of linked post data
	 *
	 * @since 4.2
	 *
	 * @param int $event_id Submitted Event ID
	 * @param int $post_type Post type of linked post
	 * @param array $submission Submitted form data
	 */
	public function handle_submission_by_post_type( $event_id, $linked_post_type, $submission ) {
		// if the submission isn't an array, bail
		if ( ! is_array( $submission ) ) {
			return;
		}

		$linked_post_types = $this->get_linked_post_types();
		$linked_post_type_object = get_post_type_object( $linked_post_type );

		/**
		 * Filters the array index that contains the post type ID in the $_POST object
		 *
		 * @param string $id Post type id index
		 * @param string $linked_post_type Post type
		 */
		$linked_post_type_id_field = apply_filters( 'tribe_events_linked_post_id_field', 'id', $linked_post_type );
		$linked_posts       = array();
		$event_post_status  = get_post_status( $event_id );

		if ( ! isset( $submission[ $linked_post_type_id_field ] ) ) {
			$submission[ $linked_post_type_id_field ] = array();
		}

		// if multiple post types are not supported, ensure that the submission array is set up appropriately
		if ( ! $this->allow_multiple( $linked_post_type ) && ! is_array( $submission[ $linked_post_type_id_field ] ) ) {
			$temp_submission = $submission;
			$submission = array();

			foreach ( $temp_submission as $key => $value ) {
				$submission[ $key ] = array( $value );
			}
		}

		$fields = array_keys( $submission );

		foreach ( $submission[ $linked_post_type_id_field ] as $key => $id ) {
			if ( ! empty( $id ) ) {
				$linked_posts[] = intval( $id );
				continue;
			}

			// if the user doesn't have permission to create this type of post, don't allow for creation
			if (
				empty( $linked_post_type_object->cap->create_posts )
				|| ! current_user_can( $linked_post_type_object->cap->create_posts )
			) {
				continue;
			}

			$data = array();
			foreach ( $fields as $field_name ) {
				$data[ $field_name ] = isset( $submission[ $field_name ][ $key ] ) ? $submission[ $field_name ][ $key ] : null;
			}

			// set the post status to the event post status
			$post_status = $event_post_status;

			/**
			 * Filters the ID (default null) for creating posts from the event edit page
			 *
			 * @param string $id Post type id index
			 * @param array $data Data for submission
			 * @param string $linked_post_type Post type
			 * @param string $post_status Post status
			 * @param int $event_id Post ID of the post the post type is attached to
			 */
			$id = apply_filters( 'tribe_events_linked_post_create_' . $linked_post_type, null, $data, $linked_post_type, $post_status, $event_id );

			/**
			 * Filters the ID (default null) for creating posts from the event edit page
			 *
			 * @param string $id Post type id index
			 * @param array $data Data for submission
			 * @param string $linked_post_type Post type
			 * @param string $post_status Post status
			 * @param int $event_id Post ID of the post the post type is attached to
			 */
			$id = apply_filters( 'tribe_events_linked_post_create', $id, $data, $linked_post_type, $post_status, $event_id );

			if ( $id ) {
				$linked_posts[] = $id;
			}
		}

		// if we don't allow multiples, make sure there's only 1
		if ( ! $this->allow_multiple( $linked_post_type ) && count( $linked_posts ) > 1 ) {
			$linked_posts = array( $linked_posts[0] );
		}

		$currently_linked_posts = $this->get_linked_posts_by_post_type( $event_id, $linked_post_type );
		$currently_linked_posts = wp_list_pluck( $currently_linked_posts, 'ID' );

		$posts_to_add = array_diff( $linked_posts, $currently_linked_posts );
		$posts_to_remove = array_diff( $currently_linked_posts, $linked_posts );

		foreach ( $posts_to_remove as $linked_post_id ) {
			$this->unlink_post( $event_id, $linked_post_id );
		}

		foreach ( $posts_to_add as $linked_post_id ) {
			$this->link_post( $event_id, $linked_post_id );
		}
	}
}
