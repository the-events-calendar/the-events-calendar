<?php
/**
 * Handles the venue step of the onboarding wizard.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe__Events__API;

/**
 * Class Venue
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Venue implements Step_Interface {
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
		$processed = $this->process( $params['venue'] ?? false );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Optin processed successfully.', 'the-events-calendar' )
			: __( 'Failed to process optin.', 'the-events-calendar' );

		$response->set_data( [
			'success' => $processed,
			'message' => array_merge( $data['message'], [ $new_message ] ),
		] );

		$response->set_status( $processed ? $response->get_status : 500 );

		return $response;
	}

	/**
	 * Process the venue data.
	 *
	 * @since TBD
	 *
	 * @param bool $venue The venue data.
	 */
	public function process( $venue ): bool {
		if ( ! $venue ) {
			return true;
		}

		// Massage the data a bit.
		$venue[ 'Venue' ]         = $venue[ 'name' ];
		$venue[ '_VenueAddress' ] = $venue[ 'address' ];
		$venue[ '_VenueCity' ]    = $venue[ 'city' ];
		$venue[ '_VenueState' ]   = $venue[ 'state' ];
		$venue[ '_VenueZip' ]     = $venue[ 'zip' ];
		$venue[ '_VenueCountry' ] = $venue[ 'country' ];
		$venue[ '_VenuePhone' ]   = $venue[ 'phone' ];
		$venue[ '_VenueWebsite' ] = $venue[ 'website' ];

		$postId = Tribe__Events__API::createVenue( $venue );

		if ( ! $postId ) {
			return false;
		}

		return true;
	}
}
