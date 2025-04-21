<?php
/**
 * Handles the settings step of the onboarding wizard.
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use TEC\Common\Admin\Onboarding\Steps\Abstract_Step;
use WP_REST_Response;
use WP_REST_Request;
use TEC\Events\Admin\Onboarding\Data;

/**
 * Class Settings
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Settings extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.4
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 1;

	/**
	 * Process the settings data.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function process( $response, $request ): WP_REST_Response {
		$params        = $request->get_params();
		$enabled_views = $params['tribeEnableViews'] ?? false;

		// Don't try to save "all".
		if ( $enabled_views && in_array( 'all', $enabled_views ) ) {
			$enabled_views = array_filter(
				$enabled_views,
				function ( $view ) {
					return 'all' !== $view;
				}
			);
		}

		$currency_key    = $params['currency'] ?? '';
		$currencies      = tribe( Data::class )->get_currency_list();
		$currency_symbol = $currencies[ $currency_key ]['symbol'] ?? '';
		$currency_code   = $currencies[ $currency_key ]['code'] ?? '';

		// Convert code to symbol.
		if ( ! empty( $currency_key ) ) {

			$currency = $currencies[ $currency_code ]['entity'] ?? '';
		}

		$settings = [
			'defaultCurrencyCode'   => $currency_code,
			'defaultCurrencySymbol' => $currency_symbol,
			'date_format'           => $params['date_format'] ?? false,
			'timezone_string'       => $params['timezone_string'] ?? false,
			'start_of_week'         => $params['start_of_week'] ?? false,
			'tribeEnableViews'      => $enabled_views,
		];

		foreach ( $settings as $key => $value ) {
			// Don't save a falsy value here, as we don't want to override any defaults.
			// And values should all be strings/ints!
			if ( empty( $value ) ) {
				self::add_message(
					$response,
					sprintf(
						/* translators: %s: the key of the setting */
						__( 'Did not attempt saving option %s.', 'the-events-calendar' ),
						$key
					)
				);
				continue;
			}

			$updated = false;

			// Start of week and timezone and date_format are WP options, the rest are TEC settings.
			if ( in_array( $key, [ 'start_of_week', 'timezone_string', 'date_format' ] ) ) {
				$temp = get_option( $key );
				if ( $temp === $value ) {
					self::add_message(
						$response,
						sprintf(
							/* translators: %s: the key of the setting */
							__( 'The %s option is already set to the requested value.', 'the-events-calendar' ),
							$key
						),
					);
					continue;
				} else {
					$updated = update_option( $key, $value );

					if ( ! $updated ) {
						return self::add_fail_message(
							$response,
							sprintf(
								/* translators: %s: the key of the setting */
								__( 'Failed to save option %s.', 'the-events-calendar' ),
								$key
							)
						);
					} else {
						self::add_message(
							$response,
							sprintf(
								/* translators: %s: the key of the setting */
								__( 'Successfully saved option %s.', 'the-events-calendar' ),
								$key
							)
						);
					}
				}
			} else {
				$temp = tribe_get_option( $key );
				if ( $temp === $value ) {
					self::add_message(
						$response,
						sprintf(
							/* translators: %s: the key of the setting */
							__( 'The %s setting is already set to the requested value.', 'the-events-calendar' ),
							$key
						)
					);
					continue;
				} else {
					$updated = tribe_update_option( $key, $value );

					if ( ! $updated ) {
						return self::add_fail_message(
							$response,
							sprintf(
								/* translators: %s: the key of the setting */
								__( 'Failed to save setting %s.', 'the-events-calendar' ),
								$key
							)
						);
					} else {
						self::add_message(
							$response,
							sprintf(
								/* translators: %s: the key of the setting */
								__( 'Successfully saved setting %s.', 'the-events-calendar' ),
								$key
							)
						);
					}
				}
			}
		}

		return self::add_message( $response, __( 'Successfully saved settings.', 'the-events-calendar' ) );
	}
}
