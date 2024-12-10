<?php
/**
 * The REST API handler for the Onboarding Wizard.
 * Cleverly named...API.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding
 */

namespace TEC\Events\Admin\Onboarding;

use WP_REST_Request as Request;
use WP_REST_Server as Server;
use WP_Error;
use WP_REST_Response;
use TEC\Events\Admin\Onboarding\Data;

/**
 * Class API
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding
 */
class API {

	/**
	 * The action for this nonce.
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public const NONCE_ACTION = '_tec_wizard';

	/**
	 * Rest Endpoint namespace
	 *
	 * @since 7.0.0
	 *
	 * @var  string
	 */
	protected const ROOT_NAMESPACE = 'tec/v2/onboarding';

	/**
	 * Register the endpoint.
	 *
	 * @since 7.0.0
	 *
	 * @return bool If we registered the endpoint.
	 */
	public function register(): bool {
		return register_rest_route(
			self::ROOT_NAMESPACE,
			'/wizard',
			[
				'methods'  => [ Server::CREATABLE ],
				'callback' => [ $this, 'handle' ],
				'args'     => [
					'permissions_callback' => [ $this, 'check_permissions' ],
					'action_nonce'         => [
						'type'              => 'string',
						'description'       => __( 'The action nonce for the request.', 'the-events-calendar' ),
						'required'          => true,
						'validate_callback' => [ $this, 'check_nonce' ],
					],
				],
			]
		);
	}

	/**
	 * Check the nonce.
	 *
	 * @since 7.0.0
	 *
	 * @param string $nonce The nonce.
	 *
	 * @return bool|WP_Error True if the nonce is valid, WP_Error if not.
	 */
	public function check_nonce( $nonce ) {
		$verified = wp_verify_nonce( $nonce, self::NONCE_ACTION );

		if ( $verified ) {
			return true;
		}

		return new WP_Error(
			'tec_invalid_nonce',
			__( 'Invalid nonce.', 'the-events-calendar' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * Check the permissions.
	 *
	 * @since 7.0.0
	 *
	 * @return bool If the user has the correct permissions.
	 */
	public function check_permissions(): bool {
		$required_permission = 'manage_options';

		/**
		 * Filter the required permission for the onboarding wizard.
		 *
		 * @since 7.0.0
		 *
		 * @param string $required_permission The required permission.
		 * @param API    $api The api object.
		 *
		 * @return string The required permission.
		 */
		$required_permission = (string) apply_filters( 'tec_events_onboarding_wizard_permissions', $required_permission, $this );

		return current_user_can( $required_permission );
	}

	/**
	 * Handle the request.
	 *
	 * @since 7.0.0
	 *
	 * @param Request $request The request object.
	 *
	 * @return WP_REST_Response The response.
	 */
	public function handle( Request $request ): WP_REST_Response {
		$response = new WP_REST_Response(
			[
				'success' => true,
				'message' => [ __( 'Onboarding wizard step completed successfully.', 'the-events-calendar' ) ],
			],
			200
		);

		// Save our state in case we need to return to it.
		$this->set_tab_records( $request, $response );

		/**
		 * Each step hooks in here and potentially modifies the response.
		 *
		 * @since 7.0.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param Request          $request  The request object.
		 * @param API              $api      The api object.
		 */
		return apply_filters( 'tec_events_onboarding_wizard_handle', $response, $request, $this );
	}

	/**
	 * Passes the request and data to the handler.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function set_tab_records( $request, $response ): void {
		$params   = $request->get_params();
		$settings = tribe(Data::class)->get_wizard_settings();
		$begun    = $settings['begun'] ?? false;
		$finished = $settings['finished'] ?? false;
		$skipped  = $params['skippedTabs'] ?? [];
		$complete = $params['completedTabs'] ?? [];

		if ( $begun ) {
			$complete = array_push( $complete, 0 );
		}

		if ( $finished ) {
			$begun = true;
		}


		// Set up our data for a single save.
		$settings['begun']          = $begun;
		$settings['current_tab']    = $params['currentTab'] ?? 0;
		$settings['finished']       = $finished;
		$settings['completed_tabs'] = $this->normalize_tabs( $complete );
		$settings['skipped_tabs']   = $this->normalize_tabs( $skipped );

		// Stuff we don't want/need to store in the settings.
		unset(
			$params['timezones'],
			$params['countries'],
			$params['currencies'],
			$params['action_nonce'],
			$params['_wpnonce']
		);

		// Add a snapshot of the data from the last request.
		$settings['last_send'] = $params;

		// Update the option.
		tribe( Data::class )->update_wizard_settings( $settings );
	}

	/**
	 * Normalize the tabs. Remove duplicates
	 *
	 * @since TBD
	 *
	 * @param [type] $tabs
	 * @return array
	 */
	protected function normalize_tabs( $tabs ): array {
		// Filter out dupes.
		$tabs = array_unique( $tabs, SORT_NUMERIC );

		// Reindex the array.
		return array_values( $tabs );
	}
}
