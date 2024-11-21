<?php
/**
 * Handles the organizer step of the onboarding wizard.
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Organizer
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Organizer extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.2
	 *
	 * @var int
	 */
	public static $step_number = 3;

	/**
	 * Get the data for the step.
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public static function get_data(): array {
		return [
			'step_number'  => self::$step_number,
			'has_options'  => false,
			'has_settings' => false,
			'has_venue'    => false,
			'is_install'   => false,
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
		$data['organizer'] = $this->get_organizer_data();

		return $data;
	}

	/**
	 * Get the organizer data.
	 * Looks for a single existing organizer and returns the data.
	 *
	 * @since 7.0.0
	 */
	protected function get_organizer_data(): array {
		$organizer_id = tribe( 'events.organizer-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $organizer_id ) ) {
			return [];
		}

		return [
			'id'                => $organizer_id,
			'Organizer'         => get_the_title( $organizer_id ),
			'_OrganizerEmail'   => get_post_meta( $organizer_id, '_OrganizerEmail', true ),
			'_OrganizerPhone'   => get_post_meta( $organizer_id, '_OrganizerPhone', true ),
			'_OrganizerWebsite' => get_post_meta( $organizer_id, '_OrganizerWebsite', true ),
		];
	}
}
