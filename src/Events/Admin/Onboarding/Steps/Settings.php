<?php
/**
 * Handles the settings step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

/**
 * Class Settings
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Settings extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 2;

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
			'has_organizer' => false,
			'has_venue'     => false,
			'is_install'    => false,
			'settings'      => [
				[
					'plugin' => 'the-events-calendar',
					'key'   => 'defaultCurrencySymbol',
					'value' => tribe_get_option( 'defaultCurrencySymbol', null ),
				],
				[
					'plugin' => 'the-events-calendar',
					'key'   => 'defaultDateFormat',
					'value' => tribe_get_option( 'dateWithYearFormat', null ),
				],
			],
			'options'       => [
				[
					'key'   => 'timezone_string',
					'value' => get_option( 'timezone_string', null ),
				],
				[
					'key'   => 'start_of_week',
					'value' => get_option( 'start_of_week', null ),
				],
			]
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
		$data['defaultCurrencySymbol'] = tribe_get_option( 'defaultCurrencySymbol', null );
		$data['defaultDateFormat'] = tribe_get_option( 'dateWithYearFormat', null );
		$data['timezone_string'] = get_option( 'timezone_string', null );
		$data['start_of_week'] = get_option( 'start_of_week', null );

		return $data;
	}
}
