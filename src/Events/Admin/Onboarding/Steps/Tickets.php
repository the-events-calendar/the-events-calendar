<?php
/**
 * Handles the tickets step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Tickets
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets extends Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 5;

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
			'has_venue'     => false,
			'is_install'    => true,
			'plugins'       => [
				[
					'plugin'   => 'event-tickets',
					'required' => false
				]
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
		$data['tickets'] = $this->is_installed( $this->get_plugin() );

		return $data;
	}

	/**
	 * Sugar function to get the plugin slug.
	 *
	 * @since 7.0.0
	 *
	 * @return string
	 */
	public function get_plugin(): string {
		return 'event-tickets';
	}

	/**
	 * Check if a plugin is installed.
	 *
	 * @since 7.0.0
	 *
	 * @param array $plugin The plugin data.
	 *
	 * @return array
	 */
	protected function is_installed( $plugin ) {
		// Check if get_plugins() function exists.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		$plugins = array_filter(
			$plugins,
			function ( $key ) use ( $plugin ) {
				return false !== strpos( $key, $plugin['plugin'] );
			},
			ARRAY_FILTER_USE_KEY
		);

		return array_keys( $plugins );
	}
}
