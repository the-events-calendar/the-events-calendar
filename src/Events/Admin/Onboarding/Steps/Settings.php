<?php
/**
 * Handles the settings step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Settings
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Settings extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 1;

	/**
	 * Process the settings data.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function process( $response, $request ): WP_REST_Response {
		$enabled_views = $params['activeViews'] ?? false;

		// Don't try to save "all".
		if ( $enabled_views && in_array( 'all', $enabled_views ) ) {
			$enabled_views = array_filter(
				$enabled_views,
				function ( $view ) {
					return 'all' !== $view;
				}
			);
		}

		$settings = [
			'defaultCurrencySymbol' => $params['defaultCurrencySymbol'] ?? false,
			'dateWithYearFormat'    => $params['defaultDateFormat'] ?? false,
			'timezone_string'       => $params['defaultTimezone'] ?? false,
			'start_of_week'         => $params['defaultWeekStart'] ?? false,
			'tribeEnableViews'      => $enabled_views,
		];

		foreach ( $settings as $key => $value ) {
			// Don't save a falsy value here, as we don't want to override any defaults.
			// And values should all be strings/ints!
			if ( empty( $value ) || ( 'start_of_week' === $key && $value === 0 ) ) {
				continue;
			}

			$updated = false;

			// Start of week and timezone are WP options, the rest are TEC settings.
			if ( 'start_of_week' === $key || 'timezone_string' === $key ) {
				$temp = get_option( $key, $value );
				if ( $temp === $value ) {
					continue;
				} else {
					$updated = update_option( $key, $value );

					if ( ! $updated ) {
						return self::add_fail_message( $response, __( 'Failed to save option.', 'the-events-calendar' ) );
					}
				}
			} else {
				$temp = tribe_get_option( $key, $value );
				if ( $temp === $value ) {
					continue;
				} else {
					$updated = tribe_update_option( $key, $value );

					if ( ! $updated ) {
						return self::add_fail_message( $response, __( 'Failed to save setting.', 'the-events-calendar' ) );
					}
				}
			}
		}

		return $response;
	}
}
