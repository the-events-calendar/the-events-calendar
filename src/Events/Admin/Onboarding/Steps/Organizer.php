<?php
/**
 * Handles the organizer step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;

/**
 * Class Organizer
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Organizer implements Contracts\Step_Interface {
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
	public static function handle( $response, $request, $wizard ): \WP_REST_Response {
		if ( $response->is_error() ) {
			return $response;
		}

		$params    = $request->get_params();
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
	 * @since TBD
	 *
	 * @param bool $organizer The organizer request data.
	 */
	public static function process( $organizer ): bool {
		// No data to process, bail out.
		if ( ! $organizer) {
			return true;
		}

		// If we already have an organizer, we're not editing it here.
		if ( ! empty( $organizer['id'] ) ) {
			return true;
		}

		$organizer['Organizer' ]         = $organizer['name' ];
		$organizer['_OrganizerPhone' ]   = $organizer['phone' ];
		$organizer['_OrganizerWebsite' ] = $organizer['website' ];
		$organizer['_OrganizerEmail' ]   = $organizer['email' ];

		$postId = Tribe__Events__API::createOrganizer( $organizer );

		if ( ! $postId ) {
			return false;
		}

		return true;
	}
}
