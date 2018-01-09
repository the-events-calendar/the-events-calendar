<?php

/**
 * Initialize Gutenberg editor blocks and styles
 *
 * @since TBD
 */
class Tribe__Events__Gutenberg {
	public function hook() {

		$this->assets();
	}

	private function assets() {
		$plugin = Tribe__Events__Main::instance();

		tribe_asset(
			$plugin,
			'tribe-gutenberg-block-lite-events',
			'block/lite-events.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			'enqueue_block_editor_assets'
		);

		tribe_asset(
			$plugin,
			'tribe-gutenberg-block-lite-events-style',
			'block-lite-events.css',
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
