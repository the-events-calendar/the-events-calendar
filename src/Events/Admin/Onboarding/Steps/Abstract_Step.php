<?php
/**
 * Abstract step-handler class for the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Optin
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
abstract class Abstract_Step implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const tabNumber = 0;

	/**
	 * Passes the request and data to the handler.
	 *
	 * @since 7.0.0
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_REST_Request  $request  The request object.
	 * @param Wizard            $wizard   The wizard object.
	 *
	 * @return \WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): \WP_REST_Response {
		// If it's already an error, bail.
		if ( $response->is_error() ) {
			return $response;
		}

		// Ensure we should be processing this step.
		if ( ! static::tab_check( $request ) ) {
			return $response;
		}

		$response = Static::process( $response, $request );

		return $response;
	}

	/**
	 * Check if the current tab is the one we should be processing.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function tab_check( $request ) {
		$params = $request->get_params();
		// If the current tab is less than this tab, we don't need to do anything yet.
		return isset( $params['currentTab'] ) && $params['currentTab'] <= self::tabNumber;
	}

	/**
	 * Add a message to the response.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param string           $message  The message to add.
	 *
	 * @return WP_REST_Response
	 */
	public function add_message( $response, $message ) {
		$data = $response->get_data();
		$data['message'] = array_merge( (array) $data['message'], [ $message ] );
		$response->set_data( $data );

		return $response;
	}
}
