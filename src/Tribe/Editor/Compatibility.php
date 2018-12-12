<?php
/**
 * Events Editor Compatibility
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Compatibility
extends Tribe__Template {

	/**
	 * Configure the Template instance
	 *
	 * @since  TBD
	 *
	 * @return  void
	 */
	public function __construct() {
		$this->set_template_origin( Tribe__Events__Main::instance() );
		$this->set_template_folder( 'src/admin-views/editor/compatibility' );
	}

	/**
	 * Any hooking any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'admin_init', array( $this, 'deactivate_gutenberg_extension_plugin' ) );
		add_action( 'add_meta_boxes', array( $this, 'maybe_add_purge_blocks_metabox' ) );
		tribe_notice(
			'events-editor-compatibility-purge-blocks',
			array( $this, 'purge_blocks_notice' ),
			array(
				'type'    => 'success',
				'dismiss' => 1,
				'wrap'    => 'p',
			),
			array( $this, 'should_display_purge_blocks_notice' )
		);
	}

	/**
	 * What is the notice when we purge blocks
	 *
	 * @since  TBD
	 *
	 * @return string
	 */
	public function purge_blocks_notice() {
		return esc_html__( 'Clean up complete. This Event is now optimized for the classic editor.', 'the-events-calendar' );
	}

	/**
	 * Check if we need to display the warning that we updated the Blocks to classic
	 *
	 * @since  TBD
	 *
	 * @return bool
	 */
	public function should_display_purge_blocks_notice() {
		$action = tribe_get_request_var( 'tribe-action', false );

		// Bail if incorrect action
		if ( 'purge-event-blocks' !== $action ) {
			return false;
		}
		$post = absint( tribe_get_request_var( 'post' ) );

		return $this->purge_blocks( $post );
	}

	/**
	 * Do the actual purgin  of data.
	 *
	 * @since  TBD
	 *
	 * @param int $post Which post we will purge
	 *
	 * @return bool
	 */
	public function purge_blocks( $post ) {
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

		// Bail if not an event (might need to be removed later)
		if ( ! tribe_is_event( $post ) ) {
			return false;
		}

		// Bail if it doesn't have Blocks
		if ( ! has_blocks( $post ) ) {
			return false;
		}

		// Fetch WP_Post
		$post = get_post( $post );

		$post_content = preg_replace( '/\<\!\-\- \/?wp\:.*\/?-->/i', '', $post->post_content );
		$update_args = array(
			'ID' => $post->ID,
			'post_content' => $post_content,
		);

		$status = wp_update_post( $update_args );

		return is_numeric( $status ) && $status;
	}

	/**
	 * Adds the Metabox into Events when needed
	 *
	 * @since  TBD
	 *
	 * @return  bool
	 */
	public function maybe_add_purge_blocks_metabox() {
		// When the Blocks editor is on we bail
		if ( $this->is_blocks_editor_toggled_on() ) {
			return false;
		}

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

		// Bail if it doesn't have Blocks
		if ( ! has_blocks( $post ) ) {
			return false;
		}

		// Now add metabox, only to events as of right now
		add_meta_box(
			'tribe-events-editor-compatibility-purge-blocks',
			__( 'Classic Editor Cleanup Tool', 'the-events-calendar' ),
			array( $this, 'render_purge_blocks_metabox' ),
			Tribe__Events__Main::POSTTYPE,
			'side',
			'low'
		);

		// Succeded to add metabox we return true
		return true;
	}

	/**
	 * Calls the template for the Purge Blocks metabox
	 *
	 * @since  TBD
	 *
	 * @param  WP_Post $post which post the metabox is attached to
	 *
	 * @return string
	 */
	public function render_purge_blocks_metabox( $post ) {
		return $this->template( 'metabox-purge-blocks', array( 'post' => $post ) );
	}

	/**
	 * On any administration page that we see the Gutenberg Extension plugin we deactivate and redirect
	 * to the Plugins page so the user can't do anything weird.
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function deactivate_gutenberg_extension_plugin() {
		if ( ! class_exists( 'Tribe__Gutenberg__Plugin' ) ) {
			return false;
		}

		$needs_redirect = true;

		if ( isset( $GLOBALS['__tribe_events_gutenberg_plugin'] ) ) {
			$gutenberg_ext_instance = $GLOBALS['__tribe_events_gutenberg_plugin'];
			$needs_redirect = false;
		} else {
			$gutenberg_ext_instance = tribe( 'gutenberg' );
		}

		$gutenberg_extension_plugin = plugin_basename( $gutenberg_ext_instance->plugin_file );

		deactivate_plugins( $gutenberg_extension_plugin, true );

		if ( $needs_redirect ) {
			wp_safe_redirect( admin_url( 'plugins.php' ) );
			tribe_exit();
		}
	}

	/**
	 * Gets if user toggled blocks editor on the settings
	 *
	 * @since 4.7
	 *
	 * @return bool
	 */
	public function is_blocks_editor_toggled_on() {
		$option = tribe_get_option( $this->get_toggle_blocks_editor_key(), false );

		return tribe_is_truthy( $option );
	}

	/**
	 * Gets the option key for toggling Blocks Editor active
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_key() {
		return 'toggle_blocks_editor';
	}

	/**
	 * Gets the option key for the Hidden Field of toggling blocks editor
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_hidden_key() {
		return 'toggle_blocks_editor_hidden_field';
	}

	/**
	 * Inserts the Toggle and Hidden Field for the Activation of Blocks Editor
	 *
	 * @since 4.7
	 *
	 * @param array $fields Fields from Options General
	 *
	 * @return array
	 */
	public function insert_toggle_blocks_editor_field( $fields = array() ) {
		if ( ! tribe( 'editor' )->is_wp_version() ) {
			return $fields;
		}

		$read_more_url = 'https://theeventscalendar.com/gutenberg-block-editor-news/?utm_source=tec&utm_medium=eventscalendarapp&utm_term=adminnotice&utm_campaign=gutenbergrelease&utm_content=ebook-gutenberg&cid=tec_eventscalendarapp_adminnotice_gutenbergrelease_ebook-gutenberg';
		$read_more_link = sprintf( ' <a href="%2$s" target="_blank">%1$s</a>.', esc_html__( 'Read more', 'the-events-calendar' ), esc_url( $read_more_url ) );

		$insert_after = 'liveFiltersUpdate';
		$insert_data = array(
			$this->get_toggle_blocks_editor_key() => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Activate Block Editor for Events', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable early access to the new Gutenberg block editor interface for creating events.', 'the-events-calendar' ) . $read_more_link,
				'default'         => false,
				'validation_type' => 'boolean',
				'attributes'      => array( 'id' => 'tribe-blocks-editor-toggle-field' ),
			),
			$this->get_toggle_blocks_editor_hidden_key() => array(
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Hidden Blocks Editor Config', 'the-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
				'attributes'      => array( 'id' => 'tribe-blocks-editor-toggle-hidden-field' ),
			),
		);

		return Tribe__Main::array_insert_after_key( $insert_after, $fields, $insert_data );
	}

}
