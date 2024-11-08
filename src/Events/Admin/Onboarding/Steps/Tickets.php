<?php
/**
 * Handles the tickets step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

/**
 * Class Tickets
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Tickets implements Step_Interface {
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
		$processed = $this->process( $params['eventTickets'] ?? false );
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
	 * Process the tickets data.
	 *
	 * @since TBD
	 *
	 * @param bool $tickets The tickets data.
	 */
	public function process( $tickets ): bool {
		// no-op for now.

		return true;
	}
}
