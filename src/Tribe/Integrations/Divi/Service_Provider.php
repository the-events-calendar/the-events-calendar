<?php
/**
 * Handles The Events Calendar integration with the Divi theme builder.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\Divi
 */

namespace Tribe\Events\Integrations\Divi;

/**
 * Class Service_Provider
 *
 * @since   5.7.0
 *
 * @package Tribe\Events\Integrations\Divi
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public function register() {
		$theme = wp_get_theme();
		if ( 'divi' !== strtolower( $theme->template ) && 'divi' !== strtolower( $theme->parent->template ) ) {
			return;
		}

		$this->hooks();
	}

	public function hooks() {
		add_filter(
			'tribe_post_id',
			[
				$this,
				'filter_tribe_post_id'
			]
		);
	}

	public function filter_tribe_post_id( $event_id ) {
		// try the "normal" way first.
		if ( empty( $event_id ) ) {
			$event_id = get_the_ID();
		}

		// look for a post
		if ( ! tribe_is_event( $event_id ) && isset( $_POST['et_post_id'] ) ) {
			$event_id = $_POST['et_post_id'];
		}

		if ( ! tribe_is_event( $event_id ) ) {
			wp_reset_postdata();

			$event_id = get_queried_object_id();
		}

		return $event_id;
	}
}
