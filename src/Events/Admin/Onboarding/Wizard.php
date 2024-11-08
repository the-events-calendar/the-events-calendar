<?php
/**
 * The data handler for the Onboarding Wizard.
 * Cleverly named...Wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding
 */
namespace TEC\Events\Admin\Onboarding;

use WP_REST_Request as Request;
use WP_REST_Server as Server;

use Tribe__Events__API;
use WP_Error;
use WP_REST_Response;

/**
 * Class Wizard
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding
 */
class Wizard {

	/**
	 * The action for this nonce.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const NONCE_ACTION = '_wizard';

	/**
	 * The field name for the primary nonce.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const NONCE_KEY = '_tec_wizard_nonce';

	/**
	 * Rest Endpoint namespace
	 *
	 * @since TBD
	 *
	 * @var  string
	 */
	protected const ROOT_NAMESPACE = 'tec/v2/onboarding';

	/**
	 * Register the endpoint.
	 *
	 * @since TBD
	 *
	 *
	 * @return boolean If we registered the endpoint.
	 */
	public function register() {
		return register_rest_route(
			self::ROOT_NAMESPACE,
			'/wizard',
			[
				'methods'  => Server::CREATABLE,
				'callback' => [ $this, 'handle'],
				'args'     => [
					'nonce' => [
						'type'        => 'string',
						'description' => __( 'The nonce for the request.', 'the-events-calendar' ),
						'required'    => true,
						'validate_callback' => [ $this, 'check_nonce'],
					],
				],
				'permissions_callback' => [ $this, 'check_permissions'],
			]
		);
	}

	/**
	 * Check the nonce.
	 *
	 * @since TBD
	 *
	 * @param string $nonce The nonce.
	 *
	 * @return boolean|WP_Error True if the nonce is valid, WP_Error if not.
	 */
	public function check_nonce( $nonce ) {
		$verified = wp_verify_nonce( $nonce, self::NONCE_ACTION );

		if ( ! $verified ) {
			return new WP_Error(
				'tec_invalid_nonce',
				__( 'Invalid nonce.', 'the-events-calendar' ),
				['status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Check the permissions.
	 *
	 * @since TBD
	 *
	 * @return boolean If the user has the correct permissions.
	 */
	public function check_permissions() {
		$required_permission = 'manage_options';

		/**
		 * Filter the required permission for the onboarding wizard.
		 *
		 * @since TBD
		 *
		 * @param string $required_permission The required permission.
		 * @param Wizard $wizard The wizard object.
		 *
		 * @return string The required permission.
		 */
		$required_permission = (string) apply_filters( 'tec_events_onboarding_wizard_permissions', $required_permission, $this );

		return current_user_can( $required_permission );
	}

	/**
	 * Handle the request.
	 *
	 * @since TBD
	 *
	 * @param Request $request The request object.
	 *
	 * @return array The response.
	 */
	public function handle( Request $request ) {
		$response = new WP_REST_Response(
			[
				'success' => true,
				'message' => [__( 'Onboarding wizard completed successfully.', 'the-events-calendar' )]
			],
			200
		);

		/**
		 * Each step hooks in here and potentially modifies the response.
		 *
		 * @since TBD
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param Request          $request  The request object.
		 * @param Wizard           $wizard   The wizard object.
		 */
		return apply_filters( 'tec_events_onboarding_wizard_handle', $response, $request, $this );

	}
}
