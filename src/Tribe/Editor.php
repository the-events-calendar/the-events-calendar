<?php

/**
 * Initialize Gutenberg editor blocks and styles
 *
 * @since TBD
 */
class Tribe__Events__Editor extends Tribe__Editor {

	/**
	 * Hooks actions from the editor into the correct places
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function hook() {
		add_filter( 'tribe_events_register_event_type_args', array( $this, 'add_event_template_blocks' ) );

		// Add Rest API support
		add_filter( 'tribe_events_register_event_type_args', array( $this, 'add_rest_support' ) );
		add_filter( 'tribe_events_register_venue_type_args', array( $this, 'add_rest_support' ) );
		add_filter( 'tribe_events_register_organizer_type_args', array( $this, 'add_rest_support' ) );

		// Maybe add flag from classic editor
		add_action( 'load-post.php', array( $this, 'flag_post_from_classic_editor' ), 0 );

		// Update Post content to use blocks
		add_action( 'tribe_blocks_editor_flag_post_classic_editor', array( $this, 'update_post_content_to_blocks' ) );

		// Remove assets that are not relevant for Gutenberg Editor
		add_action( 'wp_print_scripts', array( $this, 'deregister_scripts' ) );

		// Setup the registration of Blocks
		add_action( 'init', array( $this, 'register_blocks' ), 20 );

		// Load assets of the blocks
		add_action( 'admin_init', array( $this, 'assets' ) );

		// Add Block Categories to Editor
		add_action( 'block_categories', array( $this, 'block_categories' ), 10, 2 );

		// Make sure Events supports 'custom-fields'
		add_action( 'init', array( $this, 'add_event_custom_field_support' ), 11 );
	}

	/**
	 * When Gutenberg is active do not care about custom-fields as a metabox, but as part o the Rest API
	 *
	 * Code is located at: https://github.com/moderntribe/the-events-calendar/blob/f8af49bc41048e8632372fc8da77202d9cb98d86/src/Tribe/Admin/Event_Meta_Box.php#L345
	 *
	 * @todo  Block that option once the user has Gutenberg active
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_event_custom_field_support() {
		add_post_type_support( Tribe__Events__Main::POSTTYPE, 'custom-fields' );
	}

	/**
	 * When initially loading a post in gutenberg flags if came from classic editor
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function flag_post_from_classic_editor() {
		$post = absint( tribe_get_request_var( 'post' ) );

		// Bail on empty post
		if ( empty( $post ) ) {
			return false;
		}

		// Bail on non numeric post
		if ( ! is_numeric( $post ) ) {
			return false;
		}

		$on_classic_editor_page = tribe_get_request_var( 'classic-editor', false );

		// Bail if in classic editor
		if ( '' === $on_classic_editor_page || $on_classic_editor_page ) {
			return false;
		}

		// Bail if not an event (might need to be removed later)
		if ( ! tribe_is_event( $post ) ) {
			return false;
		}

		// Bail if it already has Blocks
		if ( has_blocks( $post ) ) {
			return false;
		}

		$has_flag_classic_editor = metadata_exists( 'post', $post, $this->key_flag_classic_editor );

		// If we already have a flag we bail
		if ( $has_flag_classic_editor ) {
			return false;
		}

		// Update with the flag for the update process
		$status = update_post_meta( $post, $this->key_flag_classic_editor, 1 );

		// Only trigger when we actually have the correct post
		if ( $status ) {
			/**
			 * Flags when we are coming from a Classic editor into Blocks
			 *
			 * @since TBD
			 *
			 * @param  int $post Which post is getting updated
			 */
			do_action( 'tribe_blocks_editor_flag_post_classic_editor', $post );
		}

		return $status;
	}

	/**
	 * Making sure we have correct post content for blocks after going into Gutenberg
	 *
	 * @since TBD
	 *
	 * @param  int $post Which post we will migrate
	 *
	 * @return bool
	 */
	public function update_post_content_to_blocks( $post ) {
		$post    = get_post( $post );

		$blocks  = $this->get_classic_template();
		$content = array();

		foreach ( $blocks as $key => $block_param ) {
			$slug = reset( $block_param );
			$params = end( $block_param );
			$json_param = false;

			// Checks for Params to attach to the tag
			if ( is_array( $params ) ) {
				$json_param = json_encode( $params );
			}

			$block_tag = "<!-- wp:{$slug} {$json_param} /-->";

			if ( 'core/paragraph' === $slug ) {
				if ( '' === $post->post_content ) {
					continue;
				}
				$content[] = '<!-- wp:freeform -->';
				$content[] = $post->post_content;
				$content[] = '<!-- /wp:freeform -->';
			} else {
				$content[] = $block_tag;
			}
		}

		$content = implode( "\n\r", $content );

		/**
		 * Allow filtering of the Content updated
		 *
		 * @since TBD
		 *
		 * @param  string  $content Content that will be updated
		 * @param  WP_Post $post    Which post we will migrate
		 * @param  array   $blocks  Which blocks we are updating with
		 */
		$content = apply_filters( 'tribe_blocks_editor_update_classic_content', $content, $post, $blocks );

		$status = wp_update_post( array(
			'ID' => $post->ID,
			'post_content' => $content,
		) );

		return $status;
	}

	/**
	 * Gets the classic template, used for migration and setup new events with classic look
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_classic_template() {
		$template = array();
		$template[] = array( 'tribe/event-datetime' );
		$template[] = array( 'tribe/featured-image' );
		$template[] = array(
			'core/paragraph',
			array(
				'placeholder' => __( 'Add Description...', 'the-events-calendar' ),
			),
		);
		$template[] = array( 'tribe/event-links' );
		$template[] = array( 'tribe/classic-event-details' );
		$template[] = array( 'tribe/event-venue' );

		/**
		 * Allow modifying the default classic template for Events
		 *
		 * @since TBD
		 *
		 * @param  array   $template   Array of all the templates used by default
		 *
		 */
		$template = apply_filters( 'tribe_events_editor_default_classic_template', $template );

		return $template;
	}

	/**
	 * Adds the required blocks into the Events Post Type
	 *
	 * @since TBD
	 *
	 * @param  array $args Arguments used to setup the CPT template
	 *
	 * @return array
	 */
	public function add_event_template_blocks( $args = array() ) {
		$template = array();

		$post = tribe_get_request_var( 'post' );
		$is_classic_editor = ! empty( $post ) && is_numeric( $post ) && ! has_blocks( $post );

		// Basically setups up a different template if is a classic event
		if ( $is_classic_editor ) {
			$template = $this->get_classic_template();
		} else {
			$template[] = array( 'tribe/event-datetime' );
			$template[] = array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Add Description...', 'the-events-calendar' ),
				),
			);
			$template[] = array( 'tribe/event-price' );
			$template[] = array( 'tribe/event-organizer' );
			$template[] = array( 'tribe/event-venue' );
			$template[] = array( 'tribe/event-website' );
			$template[] = array( 'tribe/event-links' );
		}

		/**
		 * Allow modifying the default template for Events
		 *
		 * @since TBD
		 *
		 * @param  array   $template   Array of all the templates used by default
		 * @param  string  $post_type  Which post type we are filtering
		 * @param  array   $args       Array of configurations for the post type
		 *
		 */
		$args['template'] = apply_filters( 'tribe_events_editor_default_template', $template, Tribe__Events__Main::POSTTYPE, $args );

 		return $args;
	}

	/**
	 * Prevents us from using `init` to register our own blocks, allows us to move
	 * it when the proper place shows up
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_blocks() {
		/**
		 * Internal Action used to register blocks for Events
		 *
		 * @since TBD
		 */
		do_action( 'tribe_events_editor_register_blocks' );
	}

	/**
	 * Check if current admin page is post type `tribe_events`
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_events_post_type() {
		return Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE );
	}

	/**
	 * @todo   Move this into the Block PHP files
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function assets() {
		$plugin = tribe( 'tec.main' );

		/**
		 * Allows for filtering the embedded Google Maps API URL.
		 *
		 * @since TBD
		 *
		 * @param string $api_url The Google Maps API URL.
		 */
		$gmaps_api_zoom = apply_filters( 'tribe_events_single_map_zoom_level', (int) tribe_get_option( 'embedGoogleMapsZoom', 8 ) );
		$gmaps_api_key = tribe_get_option( 'google_maps_js_api_key' );
		$gmaps_api_url = 'https://maps.googleapis.com/maps/api/js';

		if ( ! empty( $gmaps_api_key ) && is_string( $gmaps_api_key ) ) {
			$gmaps_api_url = add_query_arg( array( 'key' => $gmaps_api_key ), $gmaps_api_url );
		}

		/**
		 * Allows for filtering the embedded Google Maps API URL.
		 *
		 * @since TBD
		 *
		 * @param string $api_url The Google Maps API URL.
		 */
		$gmaps_api_url = apply_filters( 'tribe_events_google_maps_api', $gmaps_api_url );

		$js_config = array(
			'admin_url' => admin_url(),
			'timeZone' => array(
				'show_time_zone' => false,
				'label' => $this->get_timezone_label(),
			),
			'rest' => array(
				'url' => get_rest_url(),
				'nonce' => array(
					'wp_rest' => wp_create_nonce( 'wp_rest' ),
					'add_ticket_nonce' => wp_create_nonce( 'add_ticket_nonce' ),
					'edit_ticket_nonce' => wp_create_nonce( 'edit_ticket_nonce' ),
					'remove_ticket_nonce' => wp_create_nonce( 'remove_ticket_nonce' ),
				),
				'namespaces' => array(
					'core' => 'wp/v2',
				),
			),
		);

		$is_classic_editor = $this->post_is_from_classic_editor( tribe_get_request_var( 'post', 0 ) );

		/**
		 * @todo: Put js config into common
		 */
		tribe_asset(
			$plugin,
			'tribe-events-editor-blocks-gmaps-api',
			$gmaps_api_url,
			array(),
			'enqueue_block_editor_assets',
			array(
				'type'         => 'js',
				'in_footer'    => false,
				'localize'     => array(
					array(
						'name' => 'tribe_blocks_editor_google_maps_api',
						'data' => array(
							'zoom' => $gmaps_api_zoom,
							'key' => $gmaps_api_key,
						),
					),
					array(
						'name' => 'tribe_js_config',
						/**
						 * Array used to setup the FE with custom variables from the BE
						 *
						 * @since TBD
						 *
						 * @param array An array with the variables to be localized
						 */
						'data' => apply_filters( 'tribe_events_editor_js_config', $js_config ),
					),
					array(
						'name' => 'tribe_blocks_editor_settings',
						'data' => tribe( 'events.editor.settings' )->get_options(),
					),
					array(
						'name' => 'tribe_blocks_editor_timezone_html',
						'data' => tribe_events_timezone_choice( Tribe__Events__Timezones::get_event_timezone_string() ),
					),
					array(
						'name' => 'tribe_blocks_editor_price_settings',
						'data' => array(
							'default_currency_symbol'   => tribe_get_option( 'defaultCurrencySymbol', '$' ),
							'default_currency_position' => (
								tribe_get_option( 'reverseCurrencyPosition', false ) ? 'suffix' : 'prefix'
							),
							'is_new_event'              => tribe( 'context' )->is_new_post(),
						),
					),
					array(
						'name' => 'tribe_blocks_editor_constants',
						'data' => array(
							'hide_upsell' => ( defined( 'TRIBE_HIDE_UPSELL' ) && TRIBE_HIDE_UPSELL ) ? 'true' : 'false',
						),
					),
					array(
						'name' => 'tribe_blocks_editor',
						'data' => array(
							'is_classic' => $is_classic_editor,
						),
					),
					array(
						'name' => 'tribe_date_settings',
						'data' => array( $this, 'get_date_settings' ),
					),
					array(
						'name' => 'tribe_data_countries',
						'data' => tribe( 'languages.locations' )->get_countries(),
					),
					array(
						'name' => 'tribe_data_us_states',
						'data' => Tribe__View_Helpers::loadStates(),
					),
				),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority' => 1
			)
		);
		
		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-editor',
			'app/editor.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 102,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-icons',
			'app/icons.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 103,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-hoc',
			'app/hoc.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 104,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-elements',
			'app/elements.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 105,
			)
		);

		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-blocks',
			'app/blocks.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 106,
			)
		);

		tribe_asset(
			$plugin,
			'tribe-block-editor',
			'app/editor.css',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer'    => false,
				'conditionals' => array( $this, 'is_events_post_type' ),
			)
		);

		tribe_asset(
			$plugin,
			'tribe-block-editor-blocks',
			'app/blocks.css',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer'    => false,
				'conditionals' => array( $this, 'is_events_post_type' ),
			)
		);
	}

	/**
	 * Remove scripts that are not relevant for the Gutenberg editor or conflict with the scripts
	 * used on gutenberg
	 *
	 * @since TBD
	 */
	public function deregister_scripts() {
		wp_deregister_script( 'tribe_events_google_maps_api' );
	}

	/**
	 * Get Localization data for Date settings
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_date_settings() {
		global $wp_locale;
		return array(
			'l10n'     => array(
				'locale'        => get_user_locale(),
				'months'        => array_values( $wp_locale->month ),
				'monthsShort'   => array_values( $wp_locale->month_abbrev ),
				'weekdays'      => array_values( $wp_locale->weekday ),
				'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
				'meridiem'      => (object) $wp_locale->meridiem,
				'relative'      => array(
					/* translators: %s: duration */
					'future' => __( '%s from now', 'default' ),
					/* translators: %s: duration */
					'past'   => __( '%s ago', 'default' ),
				),
			),
			'formats'  => array(
				'time'       => get_option( 'time_format', __( 'g:i a', 'default' ) ),
				'date'       => get_option( 'date_format', __( 'F j, Y', 'default' ) ),
				'dateNoYear' => __( 'F j', 'default' ),
				'datetime'   => __( 'F j, Y g:i a', 'default' ),
			),
			'timezone' => array(
				'offset' => get_option( 'gmt_offset', 0 ),
				'string' => $this->get_timezone_label(),
			),
		);
	}

	/**
	 * Returns the site timezone as a string
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_timezone_label() {
		return class_exists( 'Tribe__Timezones' )
			? Tribe__Timezones::wp_timezone_string()
			: get_option( 'timezone_string', 'UTC' );
	}

	/**
	 * Add "Event Blocks" category to the editor
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function block_categories( $categories, $post ) {
		if ( Tribe__Events__Main::POSTTYPE !== $post->post_type ) {
			return $categories;
		}

		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'tribe-events',
					'title' => __( 'Event Blocks', 'the-events-calendar' ),
				),
			)
		);
	}

	/************************
	 *                      *
	 *  Deprecated Methods  *
	 *                      *
	 ************************/

	/**
	 * Adds the required blocks into the Events Post Type
	 *
	 * @since TBD
	 * @deprecated 0.1.3-alpha
	 *
	 * @param  array $args Arguments used to setup the CPT template
	 *
	 * @return array
	 */
	public function add_template_blocks( $args = array() ) {
		return $this->add_event_template_blocks( $args );
	}

	/**
	 * When the plugin loads the option is not set so the value is an empty string and when casting into a bool value
	 * this returns a `false` positive. As empty string indicates the value has not set already.
	 *
	 * This is something should be addressed on TEC as is affecting any new user installing the plugin.
	 *
	 * Code is located at: https://github.com/moderntribe/the-events-calendar/blob/f8af49bc41048e8632372fc8da77202d9cb98d86/src/Tribe/Admin/Event_Meta_Box.php#L345
	 *
	 * @since TBD
	 * @deprecated 0.3.2-alpha
	 *
	 * @param $value
	 * @param $name
	 *
	 * @return bool
	 */
	public function get_option( $value, $name ) {
		// If value is empty string indicates the value hasn't been set into the DB and should be true by default.
		if ( 'disable_metabox_custom_fields' === $name && '' === $value ) {
			return true;
		}

		return $value;
	}

}
