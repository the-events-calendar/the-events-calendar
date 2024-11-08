<?php
/**
 * Handles the optin step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Events\Telemetry\Telemetry;

/**
 * Class Optin
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Optin implements Step_Interface {
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
		$processed = $this->process( $params['optin'] ?? false );
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

		$response->set_status( $processed ? $response->get_status : 500 );

		return $response;
	}

	/**
	 * Process the optin data.
	 *
	 * @since TBD
	 *
	 * @param bool $optin The optin request data.
	 */
	public function process( $optin ): bool {
		try {
			tribe( Telemetry::class )->save_opt_in_setting_field( $optin );
		} catch ( \Exception $e ) {
			return false;
		}

		// There's no return here, so let's just assume it succeeded.
		return true;
	}


}
