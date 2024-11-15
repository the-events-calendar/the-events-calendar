<?php
/**
 * Handles the optin step of the onboarding wizard.
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use TEC\Common\Telemetry\Telemetry as Common_Telemetry;

/**
 * Class Optin
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Optin implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.2
	 *
	 * @var int
	 */
	public const tabNumber = 0;

	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 6.8.1
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return \WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): \WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params = $request->get_params();

		// If the current tab is less than this tab, we don't need to do anything yet.
		if ( $params['currentTab'] < self::tabNumber ) {
			return $response;
		}

		if ( ! isset( $params['optin'] ) ) {
			return $response;
		}

		$processed = self::process( tribe_is_truthy( $params['optin'] ) );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Optin processed successfully.', 'the-events-calendar' )
			: __( 'Failed to process optin.', 'the-events-calendar' );

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
	 * Process the optin data.
	 *
	 * @since 6.8.1
	 *
	 * @param bool $optin The optin request data.
	 */
	public static function process( $optin ): bool {
		$current_optin = tribe_get_option( 'opt-in-status', false );
		if ( $current_optin === $optin ) {
			return true;
		}

		// Save the option.
		$option = tribe_update_option( 'opt-in-status', $optin );

		// Tell Telemetry to update.
		tribe( Common_Telemetry::class )->register_tec_telemetry_plugins( $optin );

		return $option;
	}
}
