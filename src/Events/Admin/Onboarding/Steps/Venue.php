<?php
/**
 * Handles the venue step of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use Tribe__Events__API;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Venue
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Venue extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 4;

	/**
	 * Process the venue data.
	 *
	 * @since 7.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function process( $response, $request ): WP_REST_Response {
		$params = $request->get_params();
		// No data to process, bail out.
		if ( ! $params['venue'] ) {
			return $response;
		}

		$venue = $params['venue'];

		// If we already have a venue, we're not editing it here.
		if ( ! empty( $venue['id'] ) ) {
			return $response;
		}

		// Massage the data a bit.
		$new_venue['Venue']         = $venue['name'];
		$new_venue['_VenueAddress'] = $venue['_VenueAddress'];
		$new_venue['_VenueCity']    = $venue['_VenueCity'];
		$new_venue['_VenueState']   = $venue['_VenueState'];
		$new_venue['_VenueZip']     = $venue['_VenueZip'];
		$new_venue['_VenueCountry'] = $venue['_VenueCountry'];
		$new_venue['_VenuePhone']   = $venue['_VenuePhone'];
		$new_venue['_VenueWebsite'] = $venue['_VenueWebsite'];

		$post_id = Tribe__Events__API::createVenue( $new_venue );

		if ( ! $post_id ) {
			return self::add_fail_message( $response, __( 'Failed to create venue.', 'the-events-calendar' ) );
		}

		return $response;
	}
}
