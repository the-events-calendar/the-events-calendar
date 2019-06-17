<?php
/**
 * Events Editor Compatibility
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Compatibility {

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
		add_action( 'tribe_editor_classic_is_active', array( $this, 'filter_is_classic_editor' ) );
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
	 * Filter when we are in the classic editor page
	 *
	 * @since  4.7.4
	 *
	 * @param  boolean $pre
	 *
	 * @return boolean
	 */
	public function filter_is_classic_editor( $is_classic_editor = false ) {
		if ( ! $this->is_blocks_editor_toggled_on() ) {
			return true;
		}

		return $is_classic_editor;
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
				'tooltip'         => esc_html__( 'Enable the Gutenberg block editor interface for creating events.', 'the-events-calendar' ) . $read_more_link,
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
