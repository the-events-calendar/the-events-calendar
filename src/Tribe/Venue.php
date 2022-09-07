<?php

class Tribe__Events__Venue extends Tribe__Events__Linked_Posts__Base {
	const POSTTYPE = 'tribe_venue';

	/**
	 * Args for venue post type
	 * @var array
	 */
	public $post_type_args = [
		'public'              => false,
		'rewrite'             => [ 'slug' => 'venue', 'with_front' => false ],
		'show_ui'             => true,
		'show_in_menu'        => false,
		'supports'            => [ 'title', 'editor' ],
		'capability_type'     => [ 'tribe_venue', 'tribe_venues' ],
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	];

	/**
	 * @var string
	 */
	protected $meta_prefix = '_Venue';

	/**
	 * @var string The meta key relating a post of the type managed by the class to events.
	 */
	protected $event_meta_key = '_EventVenueID';

	/**
	 * @var array A list of all the valid Venue keys, post fields and custom fields
	 */
	public static $valid_venue_keys = [
		'Venue',
		'Address',
		'City',
		'Province',
		'State',
		'StateProvince',
		'Province',
		'Zip',
		'Phone',
	];

	/**
	 * @var array A list of the valid meta keys for this linked post.
	 */
	public static $meta_keys = [
		'Address',
		'City',
		'Province',
		'State',
		'StateProvince',
		'Province',
		'Zip',
		'Phone',
	];



	public $venueTags = [
		'_VenueCountry',
		'_VenueAddress',
		'_VenueCity',
		'_VenueStateProvince',
		'_VenueState',
		'_VenueProvince',
		'_VenueZip',
		'_VenuePhone',
		'_VenueURL',
		'_VenueShowMap',
		'_VenueShowMapLink',
	];

	/**
	 * @var string
	 */
	public $singular_venue_label;

	/**
	 * @var string
	 */
	public $plural_venue_label;

	/**
	 * @var Tribe__Events__Venue
	 */
	protected static $instance;

	/**
	 * Returns a singleton of this class
	 *
	 * @return Tribe__Events__Venue
	 */
	public static function instance() {
		return tribe( 'tec.linked-posts.venue' );
	}

	/**
	 * Tribe__Events__Venue constructor.
	 */
	public function __construct() {
		$rewrite = Tribe__Events__Rewrite::instance();

		$this->post_type                      = self::POSTTYPE;
		$this->singular_venue_label           = $this->get_venue_label_singular();
		$this->singular_venue_label_lowercase = $this->get_venue_label_singular_lowercase();
		$this->plural_venue_label             = $this->get_venue_label_plural();
		$this->plural_venue_label_lowercase   = $this->get_venue_label_plural_lowercase();

		$this->post_type_args['rewrite']['slug']   = $rewrite->prepare_slug( $this->singular_venue_label, self::POSTTYPE, false );
		$this->post_type_args['show_in_nav_menus'] = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
		$this->post_type_args['public']            = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
		$this->post_type_args['show_in_rest']      = class_exists( 'Tribe__Events__Pro__Main' ) && current_user_can( 'manage_options' );

		/**
		 * Provides an opportunity to modify the labels used for the venue post type.
		 *
		 * @param array $args Array of arguments for register_post_type labels
		 */
		$this->post_type_args['labels'] = apply_filters( 'tribe_events_register_venue_post_type_labels', [
			'name'                     => $this->plural_venue_label,
			'singular_name'            => $this->singular_venue_label,
			'singular_name_lowercase'  => $this->singular_venue_label_lowercase,
			'plural_name_lowercase'    => $this->plural_venue_label_lowercase,
			'add_new'                  => esc_html__( 'Add New', 'the-events-calendar' ),
			'add_new_item'             => sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'edit_item'                => sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'new_item'                 => sprintf( esc_html__( 'New %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'view_item'                => sprintf( esc_html__( 'View %s', 'the-events-calendar' ), $this->singular_venue_label ),
			'search_items'             => sprintf( esc_html__( 'Search %s', 'the-events-calendar' ), $this->plural_venue_label ),
			'not_found'                => sprintf( esc_html__( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
			'not_found_in_trash'       => sprintf( esc_html__( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
			'item_published'           => sprintf( esc_html__( '%s published.', 'the-events-calendar' ), $this->singular_venue_label ),
			'item_published_privately' => sprintf( esc_html__( '%s published privately.', 'the-events-calendar' ), $this->singular_venue_label ),
			'item_reverted_to_draft'   => sprintf( esc_html__( '%s reverted to draft.', 'the-events-calendar' ), $this->singular_venue_label ),
			'item_scheduled'           => sprintf( esc_html__( '%s scheduled.', 'the-events-calendar' ), $this->singular_venue_label ),
			'item_updated'             => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_venue_label ),
			'item_link'                => sprintf(
				// Translators: %s: Venue singular.
				esc_html__( '%s Link', 'the-events-calendar' ), $this->singular_venue_label
			),
			'item_link_description'    => sprintf(
				// Translators: %s: Venue singular.
				esc_html__( 'A link to a particular %s.', 'the-events-calendar' ), $this->singular_venue_label
			),
		] );

		$this->register_post_type();

		add_filter( 'tribe_events_linked_post_type_args', [ $this, 'filter_linked_post_type_args' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_id_field_index', [ $this, 'linked_post_id_field_index' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_name_field_index', [ $this, 'linked_post_name_field_index' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_type_container', [ $this, 'linked_post_type_container' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_create_' . self::POSTTYPE, [ $this, 'save' ], 10, 4 );
		add_filter( 'tribe_events_linked_post_meta_box_title', [ $this, 'meta_box_title' ], 5, 2 );
		add_filter( 'tribe_events_linked_post_default', [ $this, 'linked_post_default' ], 10, 2 );
		add_action( 'tribe_events_linked_post_new_form', [ $this, 'linked_post_new_form' ] );
		add_action( 'admin_bar_menu', [ $this, 'edit_venue_admin_bar_menu_link' ], 80 );
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
	 * @param array  $args      Array of linked post type arguments
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
	 * @param string $id_field  Field name of the field that will hold the ID
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
	 * @param string $post_type  Post type of linked post
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
	 * Include the venue editor meta box.
	 *
	 * @since 5.14.0
	 */
	public static function add_post_type_metabox() {

		if ( ! Tribe__Admin__Helpers::instance()->is_post_type_screen( self::POSTTYPE ) ) {
			return;
		}

		add_meta_box(
			'tribe_events_venue_details',
			sprintf( esc_html__( '%s Information', 'the-events-calendar' ), tribe( 'tec.linked-posts.venue' )->get_venue_label_singular() ),
			[ static::class, 'render_meta_box' ],
			static::POSTTYPE,
			'normal',
			'high'
		);

	}

	/**
	 * Adds a venue chooser to the write post page
	 *
	 * @since 6.0.0
	 */
	public static function render_meta_box() {
		global $post;
		$options = '';
		$style   = '';
		$event   = $post;

		if ( $post->post_type == static::POSTTYPE ) {

			if ( ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] ) || ( ! is_admin() && isset( $event->ID ) ) ) {
				$saved = true;
			}

			$is_saved = $event->ID && isset( $saved ) && $saved;

			if ( $is_saved ) {
				$venue_title = apply_filters( 'the_title', $post->post_title, $post->ID );
			}

			foreach ( Tribe__Events__Main::instance()->venueTags as $tag ) {
				if ( metadata_exists( 'post', $event->ID, $tag ) ) {
					$$tag = esc_html( get_post_meta( $event->ID, $tag, true ) );
				} else {
					$cleaned_tag = str_replace( '_Venue', '', $tag );
					$$tag = call_user_func( [ Tribe__Events__Main::instance()->defaults(), $cleaned_tag ] );
				}
			}
		}

		?>
		<div id='venue-details' class="inside eventForm venue-form">
			<table cellspacing="0" cellpadding="0" id="venue-info" class="venue-info">
				<?php
				$venue_meta_box_template = apply_filters( 'tribe_events_venue_meta_box_template', Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/venue-meta-box.php' );
				if ( ! empty( $venue_meta_box_template ) ) {
					include( $venue_meta_box_template );
				}
				?>
			</table>
		</div>
	<?php
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
	 * @param int|null $id          ID of event venue
	 * @param array    $data        The venue data.
	 * @param string   $post_type   Venue Post Type
	 * @param string   $post_status The intended post status.
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

		return $this->create( $data, $post_status, true );
	}

	/**
	 * Saves venue meta
	 *
	 * @param int   $venue_id The venue ID.
	 * @param array $data     The venue data.
	 *
	 */
	public function save_meta( $venue_id, $data ) {
		$venue = get_post( $venue_id );

		/**
		 * Allow hooking in prior to updating meta fields.
		 *
		 * @param int     $venue_id The venue ID we are modifying meta for.
		 * @param array   $data     The meta fields we want saved.
		 * @param WP_Post $venue    The venue itself.
		 *
		 * @since 4.6.9
		 */
		do_action( 'tribe_events_venue_save', $venue_id, $data, $venue );

		// @todo [BTRIA-609]: We should probably do away with 'StateProvince' and stick to 'State' and 'Province'.
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

		update_post_meta( $venue_id, '_EventShowMapLink', isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'false' );
		update_post_meta( $venue_id, '_EventShowMap', isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'false' );
		update_post_meta( $venue_id, '_VenueShowMapLink', isset( $data['ShowMapLink'] ) ? $data['ShowMapLink'] : 'false' );
		update_post_meta( $venue_id, '_VenueShowMap', isset( $data['ShowMap'] ) ? $data['ShowMap'] : 'false' );
		unset( $data['ShowMapLink'] );
		unset( $data['ShowMap'] );

		if ( isset( $data['FeaturedImage'] ) ) {
			if ( empty( $data['FeaturedImage'] ) ) {
				delete_post_meta( $venue_id, '_thumbnail_id' );
			} else {
				update_post_meta( $venue_id, '_thumbnail_id', $data['FeaturedImage'] );
			}
			unset( $data['FeaturedImage'] );
		}

		unset( $data['Venue'] );

		foreach ( $data as $key => $var ) {
			// Prevent these WP_Post object fields from ending up in the meta.
			if ( in_array( $key, [ 'post_title', 'post_excerpt', 'post_content', 'post_status' ] ) ) {
				continue;
			}

			update_post_meta( $venue_id, '_Venue' . $key, sanitize_text_field( $var ) );
		}
	}

	/**
	 * Creates a new venue
	 *
	 * @param array  $data             The venue data.
	 * @param string $post_status      the intended post status.
	 * @param bool   $avoid_duplicates Whether a check to avoid the insertion of a duplicate venue
	 *                                 should be made (`true`) or not (`false`).
	 *
	 * @return int
	 */
	public function create( $data, $post_status = 'publish', $avoid_duplicates = false ) {
		/**
		 * Filters the ID of the generated venue before the class creates it.
		 *
		 * If a non `null` value is returned that will be returned and the venue creation process will bail.
		 *
		 * @param mixed  $check       Whether the venue insertion process should proceed or not.
		 * @param array  $data        The data provided to create the venue.
		 * @param string $post_status The post status that should be applied to the created venue.
		 *
		 * @since 4.6
		 */
		$check = apply_filters( 'tribe_events_tribe_venue_create', null, $data, $post_status );

		if ( null !== $check ) {
			return $check;
		}

		if ( ( isset( $data['Venue'] ) && $data['Venue'] ) || $this->has_venue_data( $data ) ) {
			$title   = isset( $data['Venue'] ) ? $data['Venue'] : esc_html__( 'Unnamed Venue', 'the-events-calendar' );
			$content = isset( $data['Description'] ) ? $data['Description'] : '';
			$slug    = sanitize_title( $title );

			$data = new Tribe__Data( $data, false );

			$postdata = [
				'post_title'    => $title,
				'post_content'  => $content,
				'post_name'     => $slug,
				'post_type'     => self::POSTTYPE,
				'post_status'   => Tribe__Utils__Array::get( $data, 'post_status', $post_status ),
				'post_author'   => $data['post_author'],
				'post_date'     => $data['post_date'],
				'post_date_gmt' => $data['post_date_gmt'],
			];

			$found = false;
			if ( $avoid_duplicates ) {
				/** @var Tribe__Duplicate__Post $duplicates */
				$duplicates = tribe( 'post-duplicate' );
				$duplicates->set_post_type( Tribe__Events__Main::VENUE_POST_TYPE );
				$duplicates->use_post_fields( $this->get_duplicate_post_fields() );
				$duplicates->use_custom_fields( $this->get_duplicate_custom_fields() );

				// for the purpose of finding duplicates we skip empty fields
				$candidate_data = array_filter( $postdata );

				// Set the $post_title to avoid returning false for lack of a post title.
				if ( ! isset( $candidate_data['post_title'] ) ) {
					$candidate_data['post_title'] = '';
				}

				$candidate_data = array_combine(
					array_map( [ $this, 'prefix_key' ], array_keys( $candidate_data ) ),
					array_values( $candidate_data )
				);

				$found = $duplicates->find_for( $candidate_data );
			}

			$venue_id = false === $found
				? wp_insert_post( array_filter( $postdata ), true )
				: $found;

			/**
			 * Filters the default value to be set on the creation of a new venue.
			 *
			 * Useful as this might be fired or required by a third party plugin like community events that would like
			 * to change the default value for the map fields.
			 *
			 * @param mixed $default_value    The default value to be applied on creation.
			 *
			 * @since 4.6.10
			 */
			$default_value = apply_filters( 'tribe_events_venue_created_map_default', true );

			// By default, the show map and show map link options should be on
			if ( isset( $data['ShowMap'] ) && ! tribe_is_truthy( $data['ShowMap'] ) ) {
				unset( $data['ShowMap'] );
			} else {
				$data['ShowMap'] = $default_value;
			}

			if ( isset( $data['ShowMapLink'] ) && ! tribe_is_truthy( $data['ShowMapLink'] ) ) {
				unset( $data['ShowMapLink'] );
			} else {
				$data['ShowMapLink'] = $default_value;
			}

			if ( ! is_wp_error( $venue_id ) ) {

				$this->save_meta( $venue_id, $data );

				/**
				 * Fires immediately after a venue has been created.
				 *
				 * @param int   $venue_id The updated venue post ID.
				 * @param array $data     The data used to update the venue.
				 */
				do_action( 'tribe_events_venue_created', $venue_id, $data->to_array() );

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
	 * @param array $data     The venue data.
	 *
	 * @return int The updated venue post ID
	 */
	public function update( $venue_id, $data ) {
		/**
		 * Filters the ID of the venue before the class updates it.
		 *
		 * If a non `null` value is returned that will be returned and the venue update process will bail.
		 *
		 * @param mixed $check    Whether the venue update process should proceed or not.
		 * @param int   $venue_id The post ID of the venue that should be updated
		 * @param array $data     The data provided to update the venue.
		 *
		 * @since 4.6
		 */
		$check = apply_filters( 'tribe_events_tribe_venue_update', null, $venue_id, $data );

		if ( null !== $check ) {
			return $check;
		}

		$data = new Tribe__Data( $data, '' );

		unset( $data['VenueID'] );

		$args = array_filter( [
			'ID'            => $venue_id,
			'post_title'    => Tribe__Utils__Array::get( $data, 'post_title', $data['Venue'] ),
			'post_content'  => Tribe__Utils__Array::get( $data, 'post_content', $data['Description'] ),
			'post_excerpt'  => Tribe__Utils__Array::get( $data, 'post_excerpt', $data['Excerpt'] ),
			'post_author'   => $data['post_author'],
			'post_date'     => $data['post_date'],
			'post_date_gmt' => $data['post_date_gmt'],
			'post_status'   => $data['post_status'],
		] );

		if ( count( $args ) > 1 ) {
			$post_type = Tribe__Events__Main::VENUE_POST_TYPE;
			$tag       = "save_post_{$post_type}";
			remove_action( $tag, [ tribe( 'tec.main' ), 'save_venue_data' ], 16 );
			wp_update_post( $args );
			add_action( $tag, [ tribe( 'tec.main' ), 'save_venue_data' ], 16, 2 );
		}

		if ( isset( $data['ShowMap'] ) && ! tribe_is_truthy( $data['ShowMap'] ) ) {
			$data['ShowMap'] = 'false';
		} else {
			$data['ShowMap'] = true;
		}
		if ( isset( $data['ShowMapLink'] ) && ! tribe_is_truthy( $data['ShowMapLink'] ) ) {
			$data['ShowMapLink'] = 'false';
		} else {
			$data['ShowMapLink'] = true;
		}

		$post_fields = array_merge( Tribe__Duplicate__Post::$post_table_columns, [
			'Venue',
			'Description',
			'Excerpt',
		] );
		$meta        = array_diff_key( $data->to_array(), array_combine( $post_fields, $post_fields ) );

		$this->save_meta( $venue_id, $meta );

		/**
		 * Fires immediately after a venue has been updated.
		 *
		 * @param int   $venue_id The updated venue post ID.
		 * @param array $data     The data used to update the venue.
		 */
		do_action( 'tribe_events_venue_updated', $venue_id, $data->to_array() );

		return $venue_id;
	}

	/**
	 * Deletes a venue
	 *
	 * @param int  $venue_id     The venue ID to delete.
	 * @param bool $force_delete Whether or not to bypass the trash when deleting the venue (see wp_delete_post's
	 *                           $force_delete param)
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
	 * @param int    $default   Default venue ID
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

	/**
	 * Returns an array of post fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <post_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see Tribe__Duplicate__Strategy_Factory for supported strategies
	 */
	protected function get_duplicate_post_fields() {
		$fields = [
			'post_title'   => [ 'match' => 'same' ],
			'post_content' => [ 'match' => 'same' ],
			'post_excerpt' => [ 'match' => 'same' ],
		];

		/**
		 * Filters the post fields that should be used to search for a venue duplicate.
		 *
		 * @param array $fields An array associating the custom field meta key to the strategy definition.
		 *
		 * @see   Tribe__Duplicate__Strategy_Factory
		 *
		 * @since 4.6
		 */
		return apply_filters( 'tribe_event_venue_duplicate_post_fields', $fields );
	}

	/**
	 * Returns an array of post custom fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <custom_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see Tribe__Duplicate__Strategy_Factory for supported strategies
	 */
	protected function get_duplicate_custom_fields() {
		$fields = [
			'_VenueAddress'       => [ 'match' => 'like' ],
			'_VenueCity'          => [ 'match' => 'same' ],
			'_VenueProvince'      => [ 'match' => 'same' ],
			'_VenueState'         => [ 'match' => 'same' ],
			'_VenueStateProvince' => [ 'match' => 'same' ],
			'_VenueZip'           => [ 'match' => 'same' ],
			'_VenuePhone'         => [ 'match' => 'same' ],
		];

		/**
		 * Filters the custom fields that should be used to search for a venue duplicate.
		 *
		 * @param array $fields An array associating the custom field meta key to the strategy definition.
		 *
		 * @see   Tribe__Duplicate__Strategy_Factory
		 *
		 * @since 4.6
		 */
		return apply_filters( 'tribe_event_venue_duplicate_custom_fields', $fields );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fetch_callback( $event ) {
		$event = Tribe__Main::post_id_helper( $event );

		/**
		 * Filters the closure that will fetch an Event Venues.
		 *
		 * Returning a non `null` value here will skip the default logic.
		 *
		 * @since 4.9.7
		 *
		 * @param callable|null The fetch callback.
		 * @param int $event The event post ID.
		 *
		 */
		$callback = apply_filters( 'tribe_events_venues_fetch_callback', null, $event );

		if ( null !== $callback ) {
			return $callback;
		}

		return static function () use ( $event ) {
			$venue_ids = array_filter(
				array_map(
					'absint',
					(array) get_post_meta( $event, '_EventVenueID' )
				)
			);

			$venues    = ! empty( $venue_ids )
				? array_map( 'tribe_get_venue_object', $venue_ids )
				: [];

			return array_filter( $venues );
		};
	}

	/**
	 *  Returns a string version of the full address of an event
	 *
	 * @param int|WP_Post The post object or post id.
	 *
	 * @return string The event's address.
	 */
	public static function get_address_full_string( $post_id = null ) {
		$address = '';
		if ( tribe_get_venue( $post_id ) ) {
			$address .= tribe_get_venue( $post_id );
		}

		if ( tribe_get_address( $post_id ) ) {
			if ( $address != '' ) {
				$address .= ', ';
			}
			$address .= tribe_get_address( $post_id );
		}

		if ( tribe_get_city( $post_id ) ) {
			if ( $address != '' ) {
				$address .= ', ';
			}
			$address .= tribe_get_city( $post_id );
		}

		if ( tribe_get_region( $post_id ) ) {
			if ( $address != '' ) {
				$address .= ', ';
			}
			$address .= tribe_get_region( $post_id );
		}

		if ( tribe_get_zip( $post_id ) ) {
			if ( $address != '' ) {
				$address .= ', ';
			}
			$address .= tribe_get_zip( $post_id );
		}

		if ( tribe_get_country( $post_id ) ) {
			if ( $address != '' ) {
				$address .= ', ';
			}
			$address .= tribe_get_country( $post_id );
		}

		return $address;
	}

	/**
	 * Returns a string version of the full address of an event.
	 *
	 * @since 5.16.0
	 *
	 * @see Tribe__Events__Main->fullAddressString()
	 *
	 * @param int|WP_Post|null $event The post object or post id.
	 *
	 * @return string The event venue's address. Empty string if the event or venue isn't found.
	 */
	public static function generate_string_address( $event = null ) {
		if ( empty( $event ) ) {
			$event = get_the_ID();
		}

		if ( is_integer( $event ) ) {
			$event = tribe_get_event( $event );
		}

		// Not an event? Bail.
		if ( ! tribe_is_event( $event ) ) {
			return '';
		}

		if ( ! tribe_has_venue( $event ) ) {
			return '';
		}

		$address = Tribe__Events__Venue::get_address_full_string( $event );
		// The above includes the venue name.

		return $address;
	}

	/**
	 * Add edit link to admin bar when viewing the tribe_venue post type archive.
	 *
	 * @since 5.16.3
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
	 */
	public function edit_venue_admin_bar_menu_link( $wp_admin_bar ) {
		global $wp_query;

		if ( ! is_admin() && $wp_query->tribe_is_event_venue ) {

			$title = sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label );

			$wp_admin_bar->add_menu([
				'id'    => 'edit',
				'title' => $title,
				'href'  => admin_url( 'post.php?post=' . $wp_query->queried_object->ID . '&action=edit' ),
			]);
		}
	}
}
