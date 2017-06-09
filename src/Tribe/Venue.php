<?php

class Tribe__Events__Venue {
	const POSTTYPE = 'tribe_venue';

	/**
	 * Args for venue post type
	 * @var array
	 */
	public $post_type_args = array(
		'public'              => false,
		'rewrite'             => array( 'slug' => 'venue', 'with_front' => false ),
		'show_ui'             => true,
		'show_in_menu'        => 0,
		'supports'            => array( 'title', 'editor' ),
		'capability_type'     => array( 'tribe_venue', 'tribe_venues' ),
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	);

	public static $valid_venue_keys = array(
		'Venue',
		'Address',
		'City',
		'Province',
		'State',
		'StateProvince',
		'Province',
		'Zip',
		'Phone',
	);


	public $singular_venue_label;
	public $plural_venue_label;

	protected static $instance;

	/**
	 * Returns a singleton of this class
	 *
	 * @return Tribe__Events__Venue
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
		$rewrite = Tribe__Events__Rewrite::instance();

		$this->singular_venue_label                = $this->get_venue_label_singular();
		$this->singular_venue_label_lowercase      = $this->get_venue_label_singular_lowercase();
		$this->plural_venue_label                  = $this->get_venue_label_plural();
		$this->plural_venue_label_lowercase        = $this->get_venue_label_plural_lowercase();

		$this->post_type_args['rewrite']['slug']   = $rewrite->prepare_slug( $this->singular_venue_label, self::POSTTYPE, false );
		$this->post_type_args['show_in_nav_menus'] = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
		$this->post_type_args['public']            = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;

		/**
		 * Provides an opportunity to modify the labels used for the venue post type.
		 *
		 * @param array $args Array of arguments for register_post_type labels
		 */
		$this->post_type_args['labels'] = apply_filters( 'tribe_events_register_venue_post_type_labels', array(
			'name'                    => $this->plural_venue_label,
			'singular_name'           => $this->singular_venue_label,
			'singular_name_lowercase' => $this->singular_venue_label_lowercase,
			'plural_name_lowercase'   => $this->plural_venue_label_lowercase,
			'add_new'                 => esc_html__( 'Add New', 'the-events-calendar' ),
			'add_new_item'            => sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'edit_item'               => sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'new_item'                => sprintf( esc_html__( 'New %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'view_item'               => sprintf( esc_html__( 'View %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'search_items'            => sprintf( esc_html__( 'Search %s', 'the-events-calendar' ), $this->plural_venue_label ),
			'not_found'               => sprintf( esc_html__( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
			'not_found_in_trash'      => sprintf( esc_html__( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
		) );

		$this->register_post_type();

		add_filter( 'tribe_events_linked_post_type_args', array( $this, 'filter_linked_post_type_args' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_id_field_index', array( $this, 'linked_post_id_field_index' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_name_field_index', array( $this, 'linked_post_name_field_index' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_type_container', array( $this, 'linked_post_type_container' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_create_' . self::POSTTYPE, array( $this, 'save' ), 10, 5 );
		add_filter( 'tribe_events_linked_post_meta_box_title', array( $this, 'meta_box_title' ), 5, 2 );
		add_filter( 'tribe_events_linked_post_default', array( $this, 'linked_post_default' ), 10, 2 );
		add_action( 'tribe_events_linked_post_new_form', array( $this, 'linked_post_new_form' ) );
	}

	/**
	 * Registers the post type
	 */
	public function register_post_type() {
		register_post_type(
			self::POSTTYPE,
			apply_filters( 'tribe_events_register_venue_type_args', $this->post_type_args )
		);
	}

	/**
	 * Filters the post type args for the venue post type
	 *
	 * @since 4.2
	 *
	 * @param array $args Array of linked post type arguments
	 * @param string $post_type Linked post type
	 *
	 * @return array
	 */
	public function filter_linked_post_type_args( $args, $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return $args;
		}

		$args['allow_multiple'] = false;
		$args['allow_creation'] = true;

		return $args;
	}

	/**
	 * Allow users to specify their own singular label for Venues
	 * @return string
	 */
	public function get_venue_label_singular() {
		return apply_filters( 'tribe_venue_label_singular', esc_html__( 'Venue', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own plural label for Venues
	 *
	 * @return string
	 */
	public function get_venue_label_plural() {
		return apply_filters( 'tribe_venue_label_plural', esc_html__( 'Venues', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own lowercase singular label for Venues
	 * @return string
	 */
	public function get_venue_label_singular_lowercase() {
		return apply_filters( 'tribe_venue_label_singular_lowercase', esc_html__( 'venue', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own lowercase plural label for Venues
	 *
	 * @return string
	 */
	public function get_venue_label_plural_lowercase() {
		return apply_filters( 'tribe_venue_label_plural_lowercase', esc_html__( 'venues', 'the-events-calendar' ) );
	}


	/**
	 * Filters the linked post id field
	 *
	 * @since 4.2
	 *
	 * @param string $id_field Field name of the field that will hold the ID
	 * @param string $post_type Post type of linked post
	 */
	public function linked_post_id_field_index( $id_field, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'VenueID';
		}

		return $id_field;
	}

	/**
	 * Filters the linked post name field
	 *
	 * @since 4.2
	 *
	 * @param string $name_field Field name of the field that will hold the name
	 * @param string $post_type Post type of linked post
	 *
	 * @return string
	 */
	public function linked_post_name_field_index( $name_field, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'Venue';
		}

		return $name_field;
	}

	public function meta_box_title( $title, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return _x( 'Location', 'Metabox title', 'the-events-calendar' );
		}

		return $title;
	}

	/**
	 * Filters the index that contains the linked post type data during form submission
	 *
	 * @since 4.2
	 *
	 * @param string $container Container index that holds submitted data
	 * @param string $post_type Post type of linked post
	 *
	 * @return string
	 */
	public function linked_post_type_container( $container, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'venue';
		}

		return $container;
	}

	/**
	 * Saves the event venue information passed via an event
	 *
	 * @param int|null $id ID of event venue
	 * @param array  $data The venue data.
	 * @param string $post_type Venue Post Type
	 * @param string $post_status The intended post status.
	 *
	 * @return mixed
	 */
	public function save( $id, $data, $post_type, $post_status ) {
		if ( isset( $data['VenueID'] ) && $data['VenueID'] > 0 ) {
			if ( count( $data ) == 1 ) {
				// Only an ID was passed and we should do nothing.
				return $data['VenueID'];
			}

			$show_map            = get_post_meta( $data['VenueID'], '_VenueShowMap', true );
			$show_map_link       = get_post_meta( $data['VenueID'], '_VenueShowMapLink', true );
			$data['ShowMap']     = $show_map ? $show_map : 'false';
			$data['ShowMapLink'] = $show_map_link ? $show_map_link : 'false';
			$this->update( $data['VenueID'], $data );

			return $data['VenueID'];
		}

		// Remove a zero-value venue ID, if set, before creating the new venue
		if ( isset( $data['VenueID'] ) && 0 == $data['VenueID'] ) {
			unset( $data['VenueID'] );
		}

		return $this->create( $data, $post_status );
	}

	/**
	 * Saves venue meta
	 *
	 * @param int   $venue_id The venue ID.
	 * @param array $data    The venue data.
	 *
	 */
	public function save_meta( $venue_id, $data ) {
		// TODO: We should probably do away with 'StateProvince' and stick to 'State' and 'Province'.
		if ( ! isset( $data['StateProvince'] ) || $data['StateProvince'] == '' ) {
			if (
				isset( $data['State'] ) && $data['State'] != ''
				&& (
					empty( $data['Country'] )
					|| $data['Country'] == 'US'
					|| $data['Country'] == esc_html__( 'United States', 'the-events-calendar' )
				)
			) {
				$data['StateProvince'] = $data['State'];
			} else {
				if ( isset( $data['Province'] ) && $data['Province'] != '' ) {
					$data['StateProvince'] = $data['Province'];
				} else {
					$data['StateProvince'] = '';
				}
			}
		}

		update_post_meta( $venue_id, '_EventShowMapLink', isset( $data['EventShowMapLink'] ) );
		update_post_meta( $venue_id, '_EventShowMap', isset( $data['EventShowMap'] ) );
		unset( $data['EventShowMapLink'] );
		unset( $data['EventShowMap'] );

		if ( isset( $data['FeaturedImage'] ) && ! empty( $data['FeaturedImage'] ) ) {
			update_post_meta( $venue_id, '_thumbnail_id', $data['FeaturedImage'] );
			unset( $data['FeaturedImage'] );
		}

		unset( $data['Venue'] );

		foreach ( $data as $key => $var ) {
			update_post_meta( $venue_id, '_Venue' . $key, sanitize_text_field( $var ) );
		}
	}

	/**
	 * Creates a new venue
	 *
	 * @param array  $data        The venue data.
	 * @param string $post_status the intended post status.
	 *
	 * @return int
	 */
	public function create( $data, $post_status = 'publish' ) {

		if ( ( isset( $data['Venue'] ) && $data['Venue'] ) || $this->has_venue_data( $data ) ) {
			$title   = isset( $data['Venue'] ) ? $data['Venue'] : esc_html__( 'Unnamed Venue', 'the-events-calendar' );
			$content = isset( $data['Description'] ) ? $data['Description'] : '';
			$slug    = sanitize_title( $title );

			$postdata = array(
				'post_title'  => $title,
				'post_content' => $content,
				'post_name'   => $slug,
				'post_type'   => self::POSTTYPE,
				'post_status' => $post_status,
			);

			$venue_id = wp_insert_post( $postdata, true );

			// By default, the show map and show map link options should be on
			$data['ShowMap'] = isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'true';
			$data['ShowMapLink'] = isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'true';

			if ( ! is_wp_error( $venue_id ) ) {
				$this->save_meta( $venue_id, $data );
				do_action( 'tribe_events_venue_created', $venue_id, $data );

				return $venue_id;
			}
		}

		// if the venue is blank, let's save the value as 0 instead
		return 0;
	}

	/**
	 * Check to see if any venue data set
	 *
	 * @param array $data the venue data.
	 *
	 * @return bool If there is ANY venue data set, return true.
	 */
	public function has_venue_data( $data ) {
		foreach ( self::$valid_venue_keys as $key ) {
			if ( isset( $data[ $key ] ) && $data[ $key ] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Updates an venue
	 *
	 * @param int   $venue_id The venue ID to update.
	 * @param array $data    The venue data.
	 *
	 */
	public function update( $venue_id, $data ) {
		$data['ShowMap']     = isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'false';
		$data['ShowMapLink'] = isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'false';

		$args = array(
			'ID' => $venue_id,
		);

		if ( isset( $data['post_title'] ) ) {
			$args['post_title'] = $data['post_title'];
		}

		if ( isset( $data['Venue'] ) ) {
			$args['post_title'] = $data['Venue'];
			unset( $data['Venue'] );
		}

		if ( isset( $data['post_content'] ) ) {
			$args['post_content'] = $data['post_content'];
		}

		if ( isset( $data['Description'] ) ) {
			$args['post_content'] = $data['Description'];
			unset( $data['Description'] );
		}

		if ( isset( $data['post_excerpt'] ) ) {
			$args['post_excerpt'] = $data['post_excerpt'];
		}

		if ( isset( $data['Excerpt'] ) ) {
			$args['post_excerpt'] = $data['Excerpt'];
			unset( $data['Excerpt'] );
		}

		if ( count( $args ) > 1 ) {
			wp_update_post( $args );
		}

		$this->save_meta( $venue_id, $data );
		do_action( 'tribe_events_venue_updated', $venue_id, $data );
	}

	/**
	 * Deletes a venue
	 *
	 * @param int  $venue_id      The venue ID to delete.
	 * @param bool $force_delete  Whether or not to bypass the trash when deleting the venue (see wp_delete_post's $force_delete param)
	 *
	 */
	public function delete( $venue_id, $force_delete = false ) {
		wp_delete_post( $venue_id, $force_delete );
	}

	/**
	 * Returns the default venue
	 *
	 * @since 4.2.4
	 *
	 * @param int $default Default venue ID
	 * @param string $post_type Post type of form being output
	 */
	public function linked_post_default( $default, $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return $default;
		}

		return Tribe__Events__Main::instance()->defaults()->venue_id();
	}

	public function linked_post_new_form( $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return;
		}

		$template = Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/create-venue-fields.php';

		/**
		 * Filters the template path of the template that holds the venue form fields
		 *
		 * @param string $template Template path
		 */
		include apply_filters( 'tribe_events_tribe_venue_new_form_fields', $template );
	}
}
