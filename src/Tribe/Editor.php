<?php

/**
 * Initialize Gutenberg editor blocks and styles
 *
 * @since TBD
 */
class Tribe__Events__Editor {
	/**
	 * Checks if we have Gutenberg Project online, only useful while
	 * its a externa plugin
	 *
	 * @todo   Revise when Gutenberg is merged into core
	 *
	 * @since  TBD
	 *
	 * @return boolean
	 */
	public function is_gutenberg_active() {
		return function_exists( 'the_gutenberg_project' );
	}

	/**
	 * Adds the required fields into the Events Post Type so that we can use Block Editor
	 *
	 * @since  TBD
	 *
	 * @param  array $args Arguments used to setup the CPT
	 *
	 * @return array
	 */
	public function add_support( $args = array() ) {
		// Blocks Editor requires REST support
		$args['show_in_rest'] = true;

		// Make sure we have the Support argument and it's an array
		if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
			$args['supports'] = array();
		}

		// Add Editor Support
		if ( ! in_array( 'editor', $args['supports'] ) ) {
			$args['supports'][] = 'editor';
		}

		return $args;
	}

	/**
	 * Prevents us from using `init` to register our own blocks, allows us to move
	 * it when the proper place shows up
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function register_blocks() {
		do_action( 'tribe_events_editor_register_blocks' );
	}

	/**
	 * @todo   Move this into the Block PHP files
	 * @return void
	 */
	public function assets() {
		$plugin = Tribe__Events__Main::instance();

		tribe_asset(
			$plugin,
			'tribe-block-editor-styles',
			'block-editor-styles.css',
			array( 'wp-edit-blocks' ),
			'enqueue_block_editor_assets'
		);

		tribe_asset(
			$plugin,
			'tribe-gutenberg-block-lite-events-frontend-style',
			'block-lite-events.css',
			array( 'wp-blocks' ),
			'enqueue_block_assets'
		);
	}
}

