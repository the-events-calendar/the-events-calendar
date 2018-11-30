<?php
/**
 * Initialize template overwrite for block single pages
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Template__Overwrite {

	/**
	 * Hook into the Events Template single page to allow Blocks to be properly reordered
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function hook() {
		/**
		 * @todo remove filter if WP 5.0 patches this function and filter
		 */
		if ( ! function_exists( 'gutenberg_disable_editor_settings_wpautop' ) ) {
			add_filter( 'wp_editor_settings', array( $this, 'disable_editor_settings_wpautop' ), 10, 2 );
		}

		/**
		 * @todo remove filter if WP 5.0 patches this function and filter
		 */
		if ( ! function_exists( 'gutenberg_wpautop' ) ) {
			remove_filter( 'the_content', 'wpautop' );
			add_filter( 'the_content', array( $this, 'wpautop' ), 6 );
		}

		add_filter( 'tribe_events_template_single-event.php', array( $this, 'silence' ) );
		add_action( 'tribe_events_before_view', array( $this, 'include_blocks' ), 1, PHP_INT_MAX );
	}

	/**
	 * Gets the file path in Gutenberg Ext
	 *
 	 * @since 4.7
	 *
	 * @param  array|string  $slug  Which file we want to include
	 *
	 * @return string
	 */
	public function get_path( $slug ) {
		$slug = (array) $slug;

		$file = implode( '/', array_map( 'sanitize_file_name', $slug ) ) . '.php';
		/**
		 * @todo replace with real plugin template, once is moved out of the extension
		 */
		$directory = 'src/views/';
		return tribe( 'tec.main' )->plugin_path . $directory . $file;
	}

	/**
	 * Silence the actual templating and lets use an action to prevent Old Stuff to have any sort of interactions
	 * with what we are constructing here.
	 *
	 * @since 4.7
	 *
	 * @param string $file Which file would be loaded
	 *
	 * @return string
	 */
	public function silence( $file ) {
		$post_id = get_the_ID();

		// Prevent overwrite for posts that doens't have Blocks
		if ( ! has_blocks( $post_id ) ) {
			return $file;
		}

		return $this->get_path( 'silence' );
	}

	/**
	 * After `tribe_events_before_view` we will include the blocks template for Single Events
	 *
	 * @since 4.7
	 *
	 * @param string $silence Unused file path, since it's always the same for Blocks editor
	 *
	 * @return string
	 */
	public function include_blocks( $silence ) {
		// Prevent printing anything for events that don't have silence
		if ( $silence !== $this->get_path( 'silence' ) ) {
			return false;
		}

		$post_id = get_the_ID();

		// Prevent printing for posts that doesn't have Blocks
		if ( ! has_blocks( $post_id ) ) {
			return $silence;
		}

		$args = array(
			'post_id' => $post_id,
			'post' => get_post( $post_id ),
		);

		// Set globals to allow better usage of render method for each block
		tribe( 'events.editor.template' )->add_template_globals( $args );

		return tribe( 'events.editor.template' )->template( 'single-event-blocks' );
	}

	/**
	 * If function gutenberg_disable_editor_settings_wpautop() does not exist, use this to
	 * disable wpautop in classic editor if blocks exist.
	 *
	 * @todo This function is a copy of gutenberg_disable_editor_settings_wpautop() from the
	 * gutenberg plugin. If WP 5.0 patches this, this function should be removed.
	 *
	 * @since 4.7
	 *
	 * @param  array  $settings  Original editor settings.
	 * @param  string $editor_id ID for the editor instance.
	 *
	 * @return array             Filtered settings.
	 */
	public function disable_editor_settings_wpautop( $settings, $editor_id ) {
		$post = get_post();
		if ( 'content' === $editor_id && is_object( $post ) && has_blocks( $post ) ) {
			$settings['wpautop'] = false;
		}
		return $settings;
	}

	/**
	 * If function gutengerg_wpautop() does not exist, use this to disable wpautop.
	 *
	 * @todo This function is a copy of gutenberg_wpautop() from the gutenberg plugin.
	 * If WP 5.0 patches this, this function should be removed.
	 *
	 * @param  string $content Post content.
	 * @return string          Paragraph-converted text if non-block content.
	 */
	public function wpautop( $content ) {
		if ( has_blocks( $content ) ) {
			return $content;
		}
		return wpautop( $content );
	}

}

