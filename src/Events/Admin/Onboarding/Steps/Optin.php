<?php
/**
 * Handles the optin step of the onboarding wizard.
 *
 * @since 6.8.4
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
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Optin extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.4
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 0;

	/**
	 * Process the optin data.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function process( $response, $request ): WP_REST_Response {
		$current_optin = tribe_get_option( 'opt-in-status', false );
		$optin         = $request->get_param( 'optin' );

		if ( $current_optin === $optin ) {
			return self::add_message( $response, __( 'Opt-in status is already set to the requested value.', 'the-events-calendar' ) );
		}

		// Save the option.
		$option = tribe_update_option( 'opt-in-status', $optin );

		if ( ! $option ) {
			return self::add_fail_message( $response, __( 'Failed to save opt-in status.', 'the-events-calendar' ) );
		}

		// Tell Telemetry to update.
		tribe( Common_Telemetry::class )->register_tec_telemetry_plugins( $optin );

		return self::add_message( $response, __( 'Successfully saved opt-in status.', 'the-events-calendar' ) );
	}
}
