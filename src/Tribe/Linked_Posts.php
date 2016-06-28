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
	 * @var Tribe__Events__Main Singleton
	 */
	public $main;

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
	protected function __construct() {
		$this->main = Tribe__Events__Main::instance();
		$this->register_default_linked_post_types();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_localize_script( 'jquery', 'tribe_events_linked_posts', $this->get_post_type_container_data() );
	}

	/**
	 * Generates post_type => container key value pairs of linked post types for use on the front end
	 */
	public function get_post_type_container_data() {
		$post_types = array_keys( $this->linked_post_types );
		$data = array(
			'post_types' => array(),
		);

		foreach ( $post_types as $post_type ) {
			$data['post_types'][ $post_type ] = $this->get_post_type_container( $post_type );
		}

		return $data;
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
	 * - labels['name']
	 * - labels['singular_name']
	 * - allow_multiple (default: true) specifies how many of the post type can be linked with an event
	 * - allow_creation (default: false) specifies whether or not post creation should be allowed
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

		$default_args = array(
			'name'           => $post_type_object->labels->name,
			'singular_name'  => $post_type_object->labels->singular_name,
			'singular_name_lowercase' => $post_type_object->labels->singular_name_lowercase,
			'allow_multiple' => true,
			'allow_creation' => false,
		);

		$args = wp_parse_args( $args, $default_args );

		/**
		 * Filters the post type arguments before adding them to the collection of linked post types
		 *
		 * @since 4.2
		 *
		 * @param array Array of arguments for the post type
		 * @param string Post type slug
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
	 * Returns the post type's form field container name
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
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
	 * Returns the post type's ID field name
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

		$args = array(
			'p' => $post_id,
			'post_type' => $post_id_post_type,
			'meta_query' => array(),
		);

		if ( Tribe__Events__Main::POSTTYPE === $post_id_post_type ) {
			// if the post type that we're looking at is an event, we'll need to find all linked post types
			foreach ( $post_types as $post_type => $post_type_data ) {
				$args['meta_query'][] = array(
					'key'     => $this->get_meta_key( $post_type ),
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
			$result = $this->get_linked_post_info( $post_type, array(), $linked_post_ids );
		}

		/**
		 * Filters the linked posts of a given type for the given post
		 *
		 * @since 4.2
		 *
		 * @param array Linked posts for the given post by the given post type
		 * @param int Post ID being looked at
		 * @param string Post type of linked posts
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
	 * @param string $linked_post_type Post type of linked post
	 * @param array $args
	 * @param int $linked_post_id post id
	 *
	 * @return array
	 */
	public function get_linked_post_info( $linked_post_type, $args = array(), $linked_post_ids = null ) {
		$defaults = array(
			'post_type'            => $linked_post_type,
			'post_status'          => array(
				'publish',
				'draft',
				'private',
				'pending',
			),
			'orderby'              => 'title',
			'order'                => 'ASC',
			'ignore_sticky_posts ' => true,
			'nopaging'             => true,
		);

		if ( is_array( $linked_post_ids ) ) {
			$defaults['post__in'] = $linked_post_ids;
		} else {
			$defaults['p'] = $linked_post_ids;
		}

		$args = wp_parse_args( $args, $defaults );
		$result = new WP_Query( $args );
		if ( $result->have_posts() ) {
			return $result->posts;
		}

		return array();
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
			 * @param int Post ID of post to add linked post to
			 * @param int Post ID of post to add as a linked post to the target
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

		$linked_post_type_object   = get_post_type_object( $linked_post_type );
		$linked_post_type_id_field = $this->get_post_type_id_field_index( $linked_post_type );
		$linked_posts              = array();
		$event_post_status         = get_post_status( $event_id );

		if ( ! isset( $submission[ $linked_post_type_id_field ] ) ) {
			$submission[ $linked_post_type_id_field ] = array( 0 );
		}

		$temp_submission = $submission;
		$submission = array();

		// make sure all elements are arrays
		foreach ( $temp_submission as $key => $value ) {
			$submission[ $key ] = is_array( $value ) ? $value : array( $value );
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
				// If allow_multiple := true then each submission field may be an array
				if ( is_array( $submission[ $field_name ] ) ) {
					$data[ $field_name ] = isset( $submission[ $field_name ][ $key ] ) ? $submission[ $field_name ][ $key ] : null;
				}
				// In other cases, such as if multiple := false each submission field will contain a single value
				else {
					$data[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : null;
				}
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

	/**
	 * Helper function for displaying dropdowns for linked post types
	 *
	 * @param string $post_type Post type to display dropdown for
	 * @param mixed  $current the current saved linked post item
	 */
	public function saved_linked_post_dropdown( $post_type, $current = null ) {
		$linked_post_type_container = $this->get_post_type_container( $post_type );
		$linked_post_type_id_field  = $this->get_post_type_id_field_index( $post_type );
		$name                       = "{$linked_post_type_container}[{$linked_post_type_id_field}][]";
		$my_linked_post_ids         = array();
		$current_user               = wp_get_current_user();
		$my_linked_posts            = false;
		$my_linked_post_options     = '';

		if ( 0 != $current_user->ID ) {
			$my_linked_posts = $this->get_linked_post_info(
				$post_type,
				array(
					'post_status' => array(
						'publish',
						'draft',
						'private',
						'pending',
					),
					'author' => $current_user->ID,
				)
			);

			if ( ! empty( $my_linked_posts ) ) {
				foreach ( $my_linked_posts as $my_linked_post ) {
					$my_linked_post_ids[] = $my_linked_post->ID;
					$linked_post_title    = wp_kses( get_the_title( $my_linked_post->ID ), array() );
					$my_linked_post_options .= '<option value="' . esc_attr( $my_linked_post->ID ) . '"';
					$my_linked_post_options .= selected( $current, $my_linked_post->ID, false );
					$my_linked_post_options .= '>' . $linked_post_title . '</option>';
				}
			}
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$linked_posts = $this->get_linked_post_info(
				$post_type,
				array(
					'post_status' => array(
						'publish',
						'draft',
						'private',
						'pending',
					),
					'post__not_in' => $my_linked_post_ids,
				)
			);
		} else {
			$linked_posts = $this->get_linked_post_info(
				$post_type,
				array(
					'post_status'  => 'publish',
					'post__not_in' => $my_linked_post_ids,
				)
			);
		}

		$plural_name = $this->linked_post_types[ $post_type ]['name'];
		$singular_name = ! empty( $this->linked_post_types[ $post_type ]['singular_name'] ) ? $this->linked_post_types[ $post_type ]['singular_name'] : $plural_name;
		$singular_name_lowercase = ! empty( $this->linked_post_types[ $post_type ]['singular_name_lowercase'] ) ? $this->linked_post_types[ $post_type ]['singular_name_lowercase'] : $singular_name;

		if ( $linked_posts || $my_linked_posts ) {
			$linked_post_pto = get_post_type_object( $post_type );
			echo '<select class="chosen linked-post-dropdown" name="' . esc_attr( $name ) . '" id="saved_' . esc_attr( $post_type ) . '">';
			if (
				! empty( $linked_post_pto->cap->create_posts )
				&& current_user_can( $linked_post_pto->cap->create_posts )
				&& ! empty( $this->linked_post_types[ $post_type ]['allow_creation'] )
			) {
				echo '<option value="0">' . sprintf( esc_html__( 'Use New %s', 'the-events-calendar' ), $singular_name ) . '</option>';
			}

			if ( $my_linked_posts ) {
				$my_optgroup_name = sprintf( esc_html__( 'My %s', 'the-events-calendar' ), $plural_name );

				// backwards compatibility with old organizer filter
				if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
					/**
					 * Filters the linked organizer dropdown optgroup label that holds organizers that have
					 * been created by that user
					 *
					 * @deprecated 4.2
					 *
					 * @param string $my_optgroup_name Label of the optgroup for the "My Organizers" section
					 */
					$my_optgroup_name = apply_filters( 'tribe_events_saved_organizers_dropdown_my_optgroup', $my_optgroup_name );
				}

				/**
				 * Filters the linked post dropdown optgroup label that holds organizers that have
				 * been created by that user
				 *
				 * @param string $my_optgroup_name Label of the optgroup for the "My X" section
				 * @param string $post_type Post type of the linked post
				 */
				$my_optgroup_name = apply_filters( 'tribe_events_saved_linked_post_dropdown_my_optgroup', $my_optgroup_name, $post_type );

				echo $linked_posts ? '<optgroup label="' . esc_attr( $my_optgroup_name ) . '">' : '';
				echo $my_linked_post_options;
				echo $linked_posts ? '</optgroup>' : '';
			}

			if ( $linked_posts ) {
				$optgroup_name = sprintf( esc_html__( 'Available %s', 'the-events-calendar' ), $plural_name );

				// backwards compatibility with old organizer filter
				if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
					/**
					 * Filters the linked organizer dropdown optgroup label for saved organizers
					 *
					 * @deprecated 4.2
					 *
					 * @param string $my_optgroup_name Label of the optgroup for the "Available Organizers" section
					 */
					$optgroup_name = apply_filters( 'tribe_events_saved_organizers_dropdown_optgroup', $optgroup_name );
				}

				/**
				 * Filters the linked post dropdown optgroup label that holds all published posts of the given type
				 *
				 * @param string $my_optgroup_name Label of the optgroup for the "Available X" section
				 * @param string $post_type Post type of the linked post
				 */
				$optgroup_name = apply_filters( 'tribe_events_saved_linked_post_dropdown_optgroup', $optgroup_name, $post_type );

				echo $my_linked_posts ? '<optgroup label="' . esc_attr( $optgroup_name ) . '">' : '';
				foreach ( $linked_posts as $linked_post ) {
					$linked_post_title = wp_kses( get_the_title( $linked_post->ID ), array() );
					echo '<option value="' . esc_attr( $linked_post->ID ) . '"';
					selected( $current == $linked_post->ID );
					echo '>' . $linked_post_title . '</option>';
				}
				echo $my_linked_posts ? '</optgroup>' : '';
			}
			echo '</select>';
		} else {
			echo '<p class="nosaved">' . sprintf( esc_html__( 'No saved %s exists.', 'the-events-calendar' ), $singular_name_lowercase ) . '</p>';
			printf( '<input type="hidden" name="%s" value="%d"/>', esc_attr( $name ), 0 );
		}
	}

	public function render_meta_box_sections( $event ) {
		foreach ( $this->linked_post_types as $linked_post_type => $linked_post_type_data ) {
			$template = apply_filters( 'tribe_events_linked_post_meta_box_section', $this->main->plugin_path . 'src/admin-views/linked-post-section.php', $linked_post_type );
			include $template;
		}
	}

	/**
	 * @param $submission
	 * @param $linked_post_type
	 *
	 * @return array
	 */
	private function get_linked_post_type_data( $submission, $linked_post_type ) {
		$linked_post_type_container = $this->get_post_type_container( $linked_post_type );

		// Allow for the post type container to have first letter in uppercase form.
		// e.g. `venue` and `Venue` should both be valid.
		$linked_post_type_containers_candidates = array( $linked_post_type_container, ucfirst( $linked_post_type_container ) );

		$post_type_container = false;

		foreach ( $linked_post_type_containers_candidates as $candidate_post_type_container ) {
			if ( isset( $submission[ $candidate_post_type_container ] ) ) {
				$post_type_container = $candidate_post_type_container;
				break;
			}
		}

		if ( false === $post_type_container ) {
			$data = array();
		} else {
			$data = $submission[ $post_type_container ];
		}

		return $data;
	}
}
