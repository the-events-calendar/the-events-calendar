<?php
/**
 * Handles the optin step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use TEC\Common\Telemetry\Telemetry as Common_Telemetry;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Optin
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Optin extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 0;

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
			'has_venue'     => false,
			'is_install'    => false,
			'settings'      => [
				[
					'plugin' => 'the-events-calendar',
					'key'    => 'opt-in-status',
					'value'  => tribe_get_option( 'opt-in-status', false ),
				],
			],
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
		$data['opt-in-status'] = tribe_get_option( 'opt-in-status', null );

		return $data;
	}
}
