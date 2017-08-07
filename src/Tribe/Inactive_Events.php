<?php
/**
 * Sets up and helps to manage inactive events.
 */
class Tribe__Events__Inactive_Events {
	/**
	 * Slug for the Post Type
	 *
	 * @since  TBD
	 */
	const POST_TYPE = 'tribe_inactive_event';

	/**
	 * A instance of the Registered Post Type
	 *
	 * @since TBD
	 *
	 * @var   WP_Post_Type
	 */
	public $obj;

	/**
	 * A unique menu position so we can actually remove it later on in code.
	 *
	 * @since TBD
	 *
	 * @var   integer
	 */
	public $menu_position = 1337;

	/**
	 * Register the Post Type in WordPress
	 *
	 * @since  TBD
	 *
	 * @return WP_Post_Type|WP_Error
	 */
	public function register() {
		$this->obj = register_post_type( self::POST_TYPE, $this->get_type_args() );

		return $this->obj;
	}

	/**
	 * Gets the Post Type Singular Label
	 *
	 * @since  TBD
	 *
	 * @param  bool  $lowercase  Make the Label lowercase
	 *
	 * @return string
	 */
	public function get_type_plural_label( $lowercase = false ) {
		$label = esc_html__( 'Inactive Events', 'the-events-calendar' );

		if ( $lowercase ) {
			$label = strtolower( $label );
		}

		/**
		 * Allow users to filter Inactive Events plural label
		 *
		 * @since  TBD
		 *
		 * @param  string  $label      Label for Inactive Events plural
		 * @param  bool    $lowercase  If the label should be used in lowercase
		 */
		return apply_filters( 'tribe_events_inactive_event_post_type_plural_label', $label, $lowercase );
	}


	/**
	 * Gets the Post Type Singular Label
	 *
	 * @since  TBD
	 *
	 * @param  bool  $lowercase  Make the Label lowercase
	 *
	 * @return string
	 */
	public function get_type_singular_label( $lowercase = false ) {
		$label = esc_html__( 'Inactive Event', 'the-events-calendar' );

		if ( $lowercase ) {
			$label = strtolower( $label );
		}

		/**
		 * Allow users to filter Inactive Events singular label
		 *
		 * @since  TBD
		 *
		 * @param  string  $label      Label for Inactive Events singular
		 * @param  bool    $lowercase  If the label should be used in lowercase
		 */
		return apply_filters( 'tribe_events_inactive_event_post_type_singular_label', $label, $lowercase );
	}


	/**
	 * Arguments used to Setup the `register_post_type`, the labels for the Post Type
	 *
	 * @since  TBD
	 *
	 * @return array
	 */
	public function get_type_labels() {
		$labels = array(
			'name'                    => $this->get_type_plural_label(),
			'singular_name'           => $this->get_type_singular_label(),
			'singular_name_lowercase' => $this->get_type_singular_label( true ),
			'plural_name_lowercase'   => $this->get_type_plural_label( true ),
			'add_new'                 => esc_html__( 'Add New', 'the-events-calendar' ),
			'add_new_item'            => sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->get_type_singular_label() ),
			'edit_item'               => sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $this->get_type_singular_label() ),
			'new_item'                => sprintf( esc_html__( 'New %s', 'the-events-calendar' ), $this->get_type_singular_label() ),
			'view_item'               => sprintf( esc_html__( 'View %s', 'the-events-calendar' ), $this->get_type_singular_label() ),
			'search_items'            => sprintf( esc_html__( 'Search %s', 'the-events-calendar' ), $this->get_type_plural_label() ),
			'not_found'               => sprintf( esc_html__( 'No %s found', 'the-events-calendar' ), strtolower( $this->get_type_plural_label() ) ),
			'not_found_in_trash'      => sprintf( esc_html__( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->get_type_plural_label() ) ),
		);

		return apply_filters( 'tribe_events_inactive_event_post_type_labels', $labels );
	}

	/**
	 * Arguments used to Setup the `register_post_type`
	 *
	 * @since  TBD
	 *
	 * @return array
	 */
	public function get_type_args() {
		$supports = array_keys( get_all_post_type_supports( Tribe__Events__Main::POSTTYPE ) );

		$arguments = array(
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,

			// Keep in mind we need this to be able to hit the UI
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'menu_position'       => $this->menu_position,
			'supports'            => $supports,
			'taxonomies'          => array( Tribe__Events__Main::TAXONOMY, 'post_tag' ),
			'capability_type'     => array( 'tribe_event', 'tribe_events' ),
			'map_meta_cap'        => true,
			'labels'              => $this->get_type_labels(),
		);

		/**
		 * Filters the register_post_type arguments for Inactive Events
		 *
		 * @since  TBD
		 *
		 * @param  array  $arguments  Information to setup the Inactive Event Post Type
		 */
		return apply_filters( 'tribe_events_inactive_event_post_type_arguments', $arguments );
	}

	/**
	 * Intialize the Hooks and needed methods for the Inactive Events
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'current_screen', array( $this, 'kill_direct_access' ) );

		add_filter( 'tribe_events_event_details_meta_box_post_types', array( $this, 'add_type' ) );
		add_filter( 'tribe_events_event_options_meta_box_post_types', array( $this, 'add_type' ) );

		add_filter( 'tribe_is_post_type_screen_post_types', array( $this, 'add_type' ) );

		// Register Assets
		$this->register_assets();
	}

	/**
	 * Given an array of post types, adds the inactive events post type (if not already present).
	 *
	 * @since  TBD
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function add_type( array $post_types ) {
		$post_type = self::POST_TYPE;
		if ( ! in_array( $post_type, $post_types ) ) {
			$post_types[] = $post_type;
		}

		return $post_types;
	}

	/**
	 * Verifies if direct access to Inactive Events is allowed
	 *
	 * @since  TBD
	 *
	 * @return boolean
	 */
	public function is_direct_access_allowed() {
		/**
		 * Allows third-party to allow direct access to Inactive Events
		 *
		 * @since  TBD
		 *
		 * @param  bool  $direct_access  Can users Directly access a Inactive Event?
		 */
		return apply_filters( 'tribe_events_inactive_event_direct_access', false );
	}

	/**
	 * Kills Direct access with a graceful message
	 * This will prevent anything further due to `wp_die`
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function kill_direct_access() {
		// Bail if we are not on the correct screens
		if ( ! $this->is_screen() ) {
			return;
		}

		// If we have direct access than go for it!
		if ( $this->is_direct_access_allowed() ) {
			return;
		}

		$back_link = esc_url( add_query_arg( array( 'post_type' => Tribe__Events__Main::POSTTYPE ), admin_url( 'edit.php' ) ) );
		$html[] = sprintf( esc_html__( 'Direct access to %1$s in not available.', 'the-events-calendar' ), $this->get_type_plural_label() );
		$html[] = sprintf( '<a href="%1$s" class="">%2$s</a>', $back_link, esc_html__( '&laquo; Back', 'the-events-calendar' ) );

		wp_die(
			wpautop( implode( "\n\n", $html ) ),
			sprintf( esc_html__( 'Fail to load: %1$s', 'the-events-calendar' ), $this->get_type_singular_label() ),
			array(
				'response' => 403,
			)
		);
	}

	/**
	 * Register the Assets to modify the Inactive Events
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function register_assets() {
		tribe_asset(
			Tribe__Events__Main::instance(),
			'admin-inactive-events',
			'admin-inactive-events.js',
			array( 'jquery' ),
			'admin_enqueue_scripts',
			array(
				'localize' => array(
					'name' => 'tribe_events_inactive_event_post_type',
					'data' => self::POST_TYPE,
				),
				'conditionals' => array( $this, 'is_screen' ),
			)
		);
	}

	/**
	 * Checks if we are on the correct screen for the Inactive Events
	 *
	 * @since  TBD
	 *
	 * @return boolean
	 */
	public function is_screen() {
		return tribe( 'admin.helpers' )->is_post_type_screen( self::POST_TYPE )
			&& tribe( 'admin.helpers' )->is_base( array( 'edit', 'post' ) );
	}
}