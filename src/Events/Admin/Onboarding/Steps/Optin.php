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
class Optin extends Abstract_Step {
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
	 * In the format:
	 * [
	 *    'step_number' => int, required
	 *    'options' => [],
	 *    'settings' => [],
	 *    'plugins' => [],
	 * ]
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return [
			'step_number' => self::$step_number,
			'has_options' => false,
			'is_install'  => false,
			'settings'     => [
				'plugin' => 'the-events-calendar',
				'key'    => 'opt-in-status',
				'value'  => tribe_get_option( 'opt-in-status', false ),
			],
		];
	}
}
