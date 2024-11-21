<?php
/**
 * Handles the venue step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Venue
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Venue extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 4;

	/**
	 * Get the data for the step.
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public static function get_data(): array {
		return [
			'step_number'   => self::$step_number,
			'has_options'   => false,
			'has_organizer' => false,
			'has_settings'  => false,
			'has_venue'     => true,
			'is_install'    => false,
		];
	}

	/**
	 * Add data to the wizard for the step.
	 *
	 * @since 7.0.0
	 *
	 * @param array $data The data for the step.
	 *
	 * @return array
	 */
	public function add_data( array $data ): array {
		$data['venue'] = $this->get_venue_data();

		return $data;
	}

	/**
	 * Get the venue data.
	 * Looks for a single existing venue and returns the data.
	 *
	 * @since 7.0.0
	 */
	protected function get_venue_data(): array {
		$venue_id = tribe( 'events.venue-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $venue_id ) ) {
			return [];
		}

		return [
			'id'            => $venue_id,
			'Venue'         => get_the_title( $venue_id ),
			'_VenueAddress' => get_post_meta( $venue_id, '_VenueAddress', true ),
			'_VenueCity'    => get_post_meta( $venue_id, '_VenueCity', true ),
			'_VenueCountry' => get_post_meta( $venue_id, '_VenueCountry', true ),
			'_VenuePhone'   => get_post_meta( $venue_id, '_VenuePhone', true ),
			'_VenueState'   => get_post_meta( $venue_id, '_VenueState', true ),
			'_VenueWebsite' => get_post_meta( $venue_id, '_VenueWebsite', true ),
			'_VenueZip'     => get_post_meta( $venue_id, '_VenueZip', true ),
		];
	}
}
