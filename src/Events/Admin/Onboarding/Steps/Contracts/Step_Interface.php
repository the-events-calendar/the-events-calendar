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

	/**
	 * Get the step data.
	 *
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public static function get_data(): array;

	/**
	 * Get the step number.
	 *
	 * @since 7.0.0
	 *
	 * @return int
	 */
	public function get_step_number(): int;

	/**
	 * Add data the step data to the array for consumption by the wizard.
	 *
	 * @since 7.0.0
	 *
	 * @param array $data The data to add.
	 */
	public function add_data( array $data ): array;
}
