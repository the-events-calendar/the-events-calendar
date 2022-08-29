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
			'tec_events_single_event_id',
			[
				$this,
				'filter_tec_events_single_event_id'
			]
		);
	}

	public function filter_tec_events_single_event_id( $event_id ) {
		// try the "normal" way first.
		if ( empty( $event_id ) ) {
			$event_id = get_the_ID();
		}

		// look for a post
		if ( isset( $_POST['et_post_id'] ) ) {
			$event_id = $_POST['et_post_id'];
		}

		if ( empty( $event_id ) ) {
			// maybe?
			// wp_reset_postdata();

			$event_id = get_queried_object_id();
		}

		return $event_id;
	}
}
