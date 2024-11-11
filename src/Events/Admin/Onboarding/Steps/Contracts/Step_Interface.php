<?php
/**
 * Contract for Wizard step processors..
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps\Contracts;

/**
 * Class Step_Interface
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
interface Step_Interface {
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
	public static function handle( $response, $request, $wizard ): \WP_REST_Response;

	/**
	 * Process the request data applicable to this step.
	 *
	 * @since TBD
	 *
	 * @param bool $params The request data.
	 */
	public static function process( $params ): bool;
}
