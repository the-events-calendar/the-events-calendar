<?php

/**
 * Initialize Gutenberg editor blocks and styles
 *
 * @since 4.7
 */
class Tribe__Events__Editor extends Tribe__Editor {

	/**
	 * Hooks actions from the editor into the correct places
	 *
	 * @since 4.7
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

		/**
		 * @todo Move away from the generic to the new filter once it's introduced
		 *       See: https://core.trac.wordpress.org/ticket/45275
		 *
		 *       E.g.: `use_block_editor_for_{post_type}`
		 */
		add_filter( 'use_block_editor_for_post_type', array( $this, 'deactivate_blocks_editor_venue' ), 10, 2 );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'deactivate_blocks_editor_organizer' ), 10, 2 );
	}

	/**
	 * For now we dont use Blocks editor on the Post Type for Organizers
	 *
	 * @todo  see https://core.trac.wordpress.org/ticket/45275
	 *
	 * @since  4.7
	 *
	 * @param  boolean $is_enabled
	 * @param  string  $post_type
	 *
	 * @return boolean
	 */
	public function deactivate_blocks_editor_organizer( $is_enabled, $post_type ) {
		if ( Tribe__Events__Organizer::POSTTYPE === $post_type ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * For now we dont use Blocks editor on the Post Type for Venues
	 *
	 * @todo  see https://core.trac.wordpress.org/ticket/45275
	 *
	 * @since  4.7
	 *
	 * @param  boolean $is_enabled
	 * @param  string  $post_type
	 *
	 * @return boolean
	 */
	public function deactivate_blocks_editor_venue( $is_enabled, $post_type ) {
		if ( Tribe__Events__Venue::POSTTYPE === $post_type ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * When Gutenberg is active do not care about custom-fields as a metabox, but as part o the Rest API
	 *
	 * Code is located at: https://github.com/moderntribe/the-events-calendar/blob/f8af49bc41048e8632372fc8da77202d9cb98d86/src/Tribe/Admin/Event_Meta_Box.php#L345
	 *
	 * @todo  Block that option once the user has Gutenberg active
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function add_event_custom_field_support() {
		add_post_type_support( Tribe__Events__Main::POSTTYPE, 'custom-fields' );
	}

	/**
	 * When initially loading a post in gutenberg flags if came from classic editor
	 *
	 * @since 4.7
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

		/** @var Tribe__Editor $editor */
		$editor = tribe( 'editor' );

		// Bail if in classic editor
		if ( $editor->is_classic_editor() ) {
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
			 * @since 4.7
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
	 * @since 4.7
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
			/**
			 * Add an opportunity to set the default params of a block when migrating from classic into
			 * blocks editor.
			 *
			 * @since 4.7
			 *
			 * @param mixed $params Either array if set to values or slug string
			 * @param string $slug Name of the block edited
			 * @param WP_Post $post Post that is being affected
			 */
			$params = apply_filters( 'tribe_blocks_editor_update_classic_content_params', end( $block_param ), $slug, $post );
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
		 * @since 4.7
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
	 * @since 4.7
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
		 * @since 4.7
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
	 * @since 4.7
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
		 * @since 4.7
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
	 * @since 4.7
	 *
	 * @return void
	 */
	public function register_blocks() {
		/**
		 * Internal Action used to register blocks for Events
		 *
		 * @since 4.7
		 */
		do_action( 'tribe_events_editor_register_blocks' );
	}

	/**
	 * Check if current admin page is post type `tribe_events`
	 *
	 * @since 4.7
	 *
	 * @return bool
	 */
	public function is_events_post_type() {
		return Tribe__Admin__Helpers::instance()->is_post_type_screen( Tribe__Events__Main::POSTTYPE );
	}

	/**
	 * @todo   Move this into the Block PHP files
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function assets() {
		$plugin = tribe( 'tec.main' );

		/**
		 * Allows for filtering the embedded Google Maps API URL.
		 *
		 * @since 4.7
		 *
		 * @param string $api_url The Google Maps API URL.
		 */
		$gmaps_api_key = tribe_get_option( 'google_maps_js_api_key' );
		$gmaps_api_url = 'https://maps.googleapis.com/maps/api/js';

		if ( ! empty( $gmaps_api_key ) && is_string( $gmaps_api_key ) ) {
			$gmaps_api_url = add_query_arg( array( 'key' => $gmaps_api_key ), $gmaps_api_url );
		}

		/**
		 * Allows for filtering the embedded Google Maps API URL.
		 *
		 * @since 4.7
		 *
		 * @param string $api_url The Google Maps API URL.
		 */
		$gmaps_api_url = apply_filters( 'tribe_events_google_maps_api', $gmaps_api_url );

		tribe_asset(
			$plugin,
			'tribe-events-editor-blocks-gmaps-api',
			$gmaps_api_url,
			array(),
			'enqueue_block_editor_assets',
			array(
				'type'         => 'js',
				'in_footer'    => false,
				'localize'     => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority' => 1
			)
		);

		tribe_asset(
			$plugin,
			'tribe-the-events-calendar-data',
			'app/data.js',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'conditionals' => array( $this, 'is_events_post_type' ),
				'priority'  => 101,
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
	 * @since 4.7
	 */
	public function deregister_scripts() {
		wp_deregister_script( 'tribe_events_google_maps_api' );
	}

	/**
	 * Add "Event Blocks" category to the editor
	 *
	 * @since 4.7
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
	 * @since 4.7
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
	 * @since 4.7
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
