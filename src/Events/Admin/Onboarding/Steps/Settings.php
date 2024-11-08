<?php
/**
 * Handles the settings step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

/**
 * Class Settings
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Settings implements Step_Interface {
	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since TBD
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return \WP_REST_Response
	 */
	public function handle( $response, $request, $wizard ): \WP_REST_Response {
		if ( ! $response->is_error() ) {
			return $response;
		}

		$params    = $request->get_params();
		$processed = $this->process( $params );
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

		$response->set_status( $processed ? $response->get_status : 500 );

		return $response;
	}

	/**
	 * Process the settings data.
	 *
	 * @since TBD
	 *
	 * @param bool $params The request params.
	 */
	public function process( $params ): bool {
		$settings = [
			'defaultCurrencyCode' => $params['defaultCurrency'] ?? false,
			'dateWithYearFormat'  => $params['defaultDateFormat'] ?? false,
			'timezone_string'     => $params['defaultTimezone'] ?? false,
			'start_of_week'       => $params['defaultWeekStart'] ?? false,
			'tribeEnableViews'    => $params['activeViews'] ?? false,
		];

		foreach ( $settings as $key => $value ) {
			// Don't save a false here, as we don't want to override any defaults.
			if ( empty( $value ) ) {
				continue;
			}

			$updated = false;

			if ( 'start_of_week' !== $key ) {
				$updated = tribe_update_option( $key, $value );
			} else {
				$updated = update_option( $key, $value );
			}

			// If we failed, bail out now and return false.
			if ( ! $updated ) {
				return false;
			}
		}

		return true;
	}


}
