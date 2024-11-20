<?php
/**
 * Contract for Wizard step processors..
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps\Contracts;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Step_Interface
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
interface Step_Interface {
	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 * @param Wizard           $wizard   The wizard object.
	 *
	 * @return WP_REST_Response
	 */
	public function handle( $response, $request, $wizard ): WP_REST_Response;

	/**
	 * Process the step data.
	 *
	 * @since 7.0.0
	 */
	public function process(): self;
}
