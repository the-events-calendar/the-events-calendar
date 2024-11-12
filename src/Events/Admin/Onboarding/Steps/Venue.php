<?php
/**
 * Handles the venue step of the onboarding wizard.
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;

/**
 * Class Venue
 *
 * @since 6.8.1
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Venue implements Contracts\Step_Interface {
	/**
	 * Handles extracting and processing the pertinent data
	 * for this step from the wizard request.
	 *
	 * @since 6.8.1
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
		$processed = self::process( $params['venue'] ?? false );
		$data      = $response->get_data();

		$new_message = $processed ?
			__( 'Venue created successfully.', 'the-events-calendar' )
			: __( 'Failed to create venue.', 'the-events-calendar' );

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
	 * Process the venue data.
	 *
	 * @since 6.8.1
	 *
	 * @param bool $venue The venue data.
	 */
	public static function process( $venue ): bool {
		// No data to process, bail out.
		if ( ! $venue ) {
			return true;
		}

		// If we already have a venue, we're not editing it here.
		if ( ! empty( $venue['id'] ) ) {
			return true;
		}

		// Massage the data a bit.
		$new_venue['Venue' ]         = $venue['name' ];
		$new_venue['_VenueAddress' ] = $venue['address' ];
		$new_venue['_VenueCity' ]    = $venue['city' ];
		$new_venue['_VenueState' ]   = $venue['state' ];
		$new_venue['_VenueZip' ]     = $venue['zip' ];
		$new_venue['_VenueCountry' ] = $venue['country' ];
		$new_venue['_VenuePhone' ]   = $venue['phone' ];
		$new_venue['_VenueWebsite' ] = $venue['website' ];

		$post_id = Tribe__Events__API::createVenue( $new_venue );

		if ( ! $post_id ) {
			return false;
		}

		return true;
	}
}
