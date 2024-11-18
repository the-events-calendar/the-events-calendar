<?php
/**
 * Handles the organizer step of the onboarding wizard.
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Organizer
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Organizer implements Contracts\Step_Interface {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.2
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 3;

	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 6.8.1
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 * @param Wizard           $wizard   The wizard object.
	 *
	 * @return WP_REST_Response
	 */
	public static function handle( $response, $request, $wizard ): WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params = $request->get_params();

		// If the current tab is less than this tab, we don't need to do anything yet.
		if ( $params['currentTab'] < self::TAB_NUMBER ) {
			return $response;
		}

		if ( ! isset( $params['organizer'] ) ) {
			return $response;
		}

		$processed = self::process( $params['organizer'] ?? false );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Organizer created successfully.', 'the-events-calendar' )
			: __( 'Failed to create organizer.', 'the-events-calendar' );

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
	 * Process the organizer data.
	 *
	 * @since 6.8.1
	 *
	 * @param bool $organizer The organizer request data.
	 */
	public static function process( $organizer ): bool {
		// No data to process, bail out.
		if ( ! $organizer ) {
			return true;
		}

		// If we already have an organizer, we're not editing it here.
		if ( ! empty( $organizer['id'] ) ) {
			return true;
		}

		$organizer['Organizer']         = $organizer['name'];
		$organizer['_OrganizerPhone']   = $organizer['phone'];
		$organizer['_OrganizerWebsite'] = $organizer['website'];
		$organizer['_OrganizerEmail']   = $organizer['email'];

		$post_id = Tribe__Events__API::createOrganizer( $organizer );

		if ( ! $post_id ) {
			return false;
		}

		return true;
	}
}
