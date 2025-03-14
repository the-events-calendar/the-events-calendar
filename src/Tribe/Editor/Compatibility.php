<?php
/**
 * Events Editor Compatibility
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Compatibility {

	/**
	 * Key we store the toggle under in the tribe_events_calendar_options array.
	 *
	 * @since 5.14.0
	 *
	 * @var string
	 */
	public static $blocks_editor_key = 'toggle_blocks_editor';

	/**
	 * Key we store the toggle under in the tribe_events_calendar_options array.
	 *
	 * @since 15.4.0
	 * @since 6.0.1
	 *
	 * @deprecated Using the \Tribe__Cache object instead of caching locally.
	 *
	 * @var string
	 */
	public static $blocks_editor_value = null;

	/**
	 * Key for the Hidden Field of toggling blocks editor.
	 *
	 * @since 5.14.0
	 * @deprecated 6.0.5
	 *
	 * @var string
	 */
	public static $blocks_editor_hidden_field_key = 'toggle_blocks_editor_hidden_field';

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
		add_filter( 'tribe_editor_should_load_blocks', [ $this, 'filter_tribe_editor_should_load_blocks' ], 100 );
		add_filter( 'classic_editor_enabled_editors_for_post_type', [ $this, 'filter_classic_editor_enabled_editors_for_post_type' ], 10, 2 );
		add_filter( 'tribe_general_settings_editing_section', [ $this, 'insert_toggle_blocks_editor_field' ] );
	}

	/**
	 * Gets if user toggled blocks editor on the settings
	 *
	 * @since 4.7
	 *
	 * @return bool
	 */
	public function is_blocks_editor_toggled_on() {
		$cache     = tribe_cache();
		$cache_key = 'tec_editor_compatibility_' . static::$blocks_editor_key;

		$is_on = $cache->get( $cache_key, '', null );
		if ( $is_on !== '' && $is_on !== null ) {
			return tribe_is_truthy( $is_on );
		}

		$is_on = tribe_get_option( static::$blocks_editor_key, false );

		/**
		 * Filters whether the Blocks Editor is on or not.
		 *
		 * @since 5.1.1
		 *
		 * @param bool $is_on Whether the Blocks Editor is on or not.
		 */
		$is_on = (bool) apply_filters( 'tribe_events_blocks_editor_is_on', $is_on );

		$cache->set( $cache_key, (int) $is_on, \Tribe__Cache::NON_PERSISTENT );

		return tribe_is_truthy( $is_on );
	}

	/**
	 * Filters tribe_editor_should_load_blocks to disable blocks if the admin toggle is off.
	 *
	 * @since 5.14.0
	 *
	 * @param boolean $should_load_blocks Whether the editor should use the classic or blocks UI.
	 *
	 * @return boolean $should_load_blocks Whether the editor should use the classic or blocks UI.
	 */
	public function filter_tribe_editor_should_load_blocks( $should_load_blocks ) {
		if ( ! $this->is_blocks_editor_toggled_on() ) {
			return false;
		}

		return $should_load_blocks;
	}

	/**
	 * Compatibility specific to the Classic Editor plugin.
	 * This ensures we allow blocks when default is classic but user switching is on.
	 *
	 * @since 5.14.0
	 *
	 * @param array<string|boolean> $editors   An array of editors and if they are enabled.
	 * @param string                $post_type The post type we are checking against.
	 *
	 * @return array<string|boolean> $editors   AThe modified array of editors and if they are enabled.
	 */
	public function filter_classic_editor_enabled_editors_for_post_type( $editors, $post_type ) {
		if ( Tribe__Events__Main::POSTTYPE !== $post_type ) {
			return $editors;
		}

		$editors['block_editor'] = $this->is_blocks_editor_toggled_on();

		return $editors;
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
	public function insert_toggle_blocks_editor_field( $fields ) {
		if ( ! tribe( 'editor' )->is_wp_version() ) {
			return $fields;
		}

		$read_more_url  = 'https://theeventscalendar.com/gutenberg-block-editor-news/?utm_source=tec&utm_medium=eventscalendarapp&utm_term=adminnotice&utm_campaign=gutenbergrelease&utm_content=ebook-gutenberg&cid=tec_eventscalendarapp_adminnotice_gutenbergrelease_ebook-gutenberg';
		$read_more_link = sprintf( ' <a href="%2$s" target="_blank">%1$s</a>.', esc_html__( 'Read more', 'the-events-calendar' ), esc_url( $read_more_url ) );

		$insert_data = [
			static::$blocks_editor_key        => [
				'type'            => 'checkbox_bool',
				'label'           => esc_html__( 'Activate Block Editor for Events', 'the-events-calendar' ),
				'tooltip'         => esc_html__( 'Enable the Gutenberg block editor interface for creating events.', 'the-events-calendar' ) . $read_more_link,
				'default'         => false,
				'validation_type' => 'boolean',
				'attributes'      => [ 'id' => 'tribe-blocks-editor-toggle-field' ],
			],
		];

		return Tribe__Main::array_insert_before_key(
			'disable_metabox_custom_fields',
			$fields,
			$insert_data
		);
	}

	/* DEPRECATED */

	/**
	 * Gets the option key for toggling Blocks Editor active
	 *
	 * @since 4.7
	 * @deprecated 5.14.0
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_key() {
		_deprecated_function( __METHOD__, '5.14.0', 'use static::$blocks_editor_key' );
		return static::$blocks_editor_key;
	}

	/**
	 * Gets the option key for the Hidden Field of toggling blocks editor
	 *
	 * @since 4.7
	 * @deprecated 5.14.0
	 *
	 * @return string
	 */
	public function get_toggle_blocks_editor_hidden_key() {
		_deprecated_function( __METHOD__, '5.14.0', 'use static::$blocks_editor_hidden_field_key' );
		return 'toggle_blocks_editor_hidden_field';
	}

	/**
	 * On any administration page that we see the Gutenberg Extension plugin we deactivate and redirect
	 * to the Plugins page so the user can't do anything weird.
	 *
	 * @since 4.7
	 *
	 * @deprecated 5.14.0
	 *
	 * @return void
	 */
	public function deactivate_gutenberg_extension_plugin() {
		_deprecated_function( __METHOD__, '5.14.0', 'This extension has been integrated into TEC/Common' );
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
		_deprecated_function( __METHOD__, '5.14.0', 'See Tribe__Editor->should_load_blocks()' );
		// TEC blocks are off, return true == classic editor.
		if ( ! $this->is_blocks_editor_toggled_on() ) {
			return true;
		}

		return $is_classic_editor;
	}
}
