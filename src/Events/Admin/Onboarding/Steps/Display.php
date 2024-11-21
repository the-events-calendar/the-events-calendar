<?php
/**
 * Handles the display step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

/**
 * Class Display
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Display extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 1;

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
					'key'    => 'tribeEnableViews',
					'value'  => self::get_active_views(),
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
		$data['tribeEnableViews'] = $this->get_active_views();
		$data['available_views']  = $this->get_available_views();

		return $data;
	}

	protected static function get_active_views() {
		$view_manager    = tribe( \Tribe\Events\Views\V2\Manager::class );
		return array_keys( $view_manager->get_publicly_visible_views() );
	}

	/**
	 * Get the available views.
	 *
	 * @since 7.0.0
	 */
	protected function get_available_views(): array {
		$view_manager    = tribe( \Tribe\Events\Views\V2\Manager::class );
		$available_views = array_keys( $view_manager->get_registered_views() );
		$remove          = [
			'all',
			'latest-past',
			'organizer',
			'reflector',
			'venue',
			'widget-countdown',
			'widget-events-list',
			'widget-featured-venue',
			'widget-week',
		];

		$cleaned_views = array_flip( array_diff_key( array_flip( $available_views ), array_flip( $remove ) ) );

		return array_values( $cleaned_views );
	}
}
