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
class Settings implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const tabNumber = 1;
	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params = $request->get_params();

		// If the current tab is less than this tab, we don't need to do anything yet.
		if ( $params['currentTab'] < self::tabNumber ) {
			return $response;
		}

		$processed = self::process( $params );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Settings processed successfully.', 'the-events-calendar' )
			: __( 'Failed to process settings.', 'the-events-calendar' );

		$response->set_data(
			[
				'success' => $processed,
				'message' => array_merge( $data['message'], [ $new_message ] ),
			]
		);

		$response->set_status( $processed ? $response->get_status() : 500 );

		return $response;
	}

	/**
	 * Process the settings data.
	 *
	 * @since 7.0.0
	 *
	 * @param bool $params The request params.
	 */
	public static function process( $params ): bool {
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
					$updated = true;
				} else {
					$updated = update_option( $key, $value );
				}
			} else {
				$temp = tribe_get_option( $key, $value );
				if ( $temp === $value ) {
					$updated = true;
				} else {
					$updated = tribe_update_option( $key, $value );
				}
			}

			// If we failed, bail out immediately and return false.
			if ( ! $updated ) {
				return false;
			}
		}

		return true;
	}
}
