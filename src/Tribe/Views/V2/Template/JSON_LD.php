<?php
/**
 * Handles JSON-LD for V2.
 *
 * @since 5.0.3
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;

/**
 * Class JSON_LD
 *
 * @since 5.0.3
 *
 * @package Tribe\Events\Views\V2\Template
 */
class JSON_LD {

	/**
	 * Fires to Print JSON LD to Single Event.
	 *
	 * @since 5.0.3
	 */
	public function print_single_json_ld() {

		// Check if we are in a single page.
		if ( ! is_singular( \Tribe__Events__Main::POSTTYPE ) ) {
			return;
		}

		$context = tribe_context();

		// One more check for our Post Type.
		if ( ! $context->is( 'tec_post_type' ) ) {
			return;
		}

		// Bail when that action already exists.
		if ( has_action( 'wp_head', [ \Tribe__Events__JSON_LD__Event::instance(), 'markup' ] ) ) {
			return;
		}

		// Print JSON-LD markup on the`wp_head`.
		add_action( 'wp_head', [ \Tribe__Events__JSON_LD__Event::instance(), 'markup' ] );
	}
}
