<?php
/**
 * Handles the venue step of the onboarding wizard.
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use TEC\Common\Admin\Onboarding\Steps\Abstract_Step;
use Tribe__Events__API;
use WP_REST_Response;
use WP_REST_Request;
use TEC\Common\Lists\Country;

/**
 * Class Venue
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Venue extends Abstract_Step {
	/**
	 * The tab number for this step.
	 * Note: this is set to the same as the Tickets tab as we don't want to process a venue until the end.
	 *
	 * @since 6.8.4
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 5;

	/**
	 * Process the venue data.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function process( $response, $request ): WP_REST_Response {
		$params = $request->get_params();
		// No data to process, bail out.
		if ( empty( $params['venue'] ) ) {
			return $this->add_message( $response, __( 'No venue to save. Step skipped', 'the-events-calendar' ) );
		}

		$venue = $params['venue'];

		// If we already have a venue, we're not editing it here.
		if ( ! empty( $venue['venueId'] ) ) {
			return $this->add_message( $response, __( 'Existing venue. Step skipped.', 'the-events-calendar' ) );
		}

		$country = $venue['country'] ?? '';
		$country = tribe( Country::class )->find_country_by_key( $country );

		// Massage the data a bit.
		$new_venue['Origin']        = 'tec-onboarding';
		$new_venue['Venue']         = $venue['name'];
		$new_venue['Address']       = $venue['address'];
		$new_venue['City']          = $venue['city'];
		$new_venue['StateProvince'] = $venue['state'];
		$new_venue['State']         = $venue['state'];
		$new_venue['Province']      = $venue['state'];
		$new_venue['Zip']           = $venue['zip'];
		$new_venue['Country']       = $country;
		$new_venue['Phone']         = $venue['phone'];
		$new_venue['URL']           = $venue['website'];

		$post_id = Tribe__Events__API::createVenue( $new_venue );

		if ( ! $post_id ) {
			return $this->add_fail_message( $response, __( 'Failed to create venue.', 'the-events-calendar' ) );
		} else {
			$response->data['venue_id'] = $post_id;
		}

		return $this->add_message( $response, __( 'Venue created.', 'the-events-calendar' ) );
	}
}
