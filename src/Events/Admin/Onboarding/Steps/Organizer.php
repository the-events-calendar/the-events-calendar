<?php
/**
 * Handles the organizer step of the onboarding wizard.
 *
 * @since 6.8.4
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
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Organizer extends Abstract_Step {
	/**
	 * The tab number for this step.
	 * Note: this is set to the same as the Tickets tab as we don't want to process an organizer until the end.
	 *
	 * @since 6.8.4
	 *
	 * @var int
	 */
	public const TAB_NUMBER = 5;

	/**
	 * Process the organizer data.
	 *
	 * @since 6.8.4
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function process( $response, $request ): WP_REST_Response {
		$params = $request->get_params();
		// No data to process, bail out.
		if ( empty( $params['organizer'] ) ) {
			return self::add_message( $response, __( 'No organizer to save. Step skipped', 'the-events-calendar' ) );
		}

		$organizer = $params['organizer'];

		// If we already have an organizer, we're not editing it here.
		if ( ! empty( $organizer['organizerId'] ) ) {
			return self::add_message( $response, __( 'Existing organizer. Step skipped.', 'the-events-calendar' ) );
		}

		$new_organizer['Organizer'] = $organizer['name'];
		$new_organizer['Phone']     = $organizer['phone'] ?? '';
		$new_organizer['Website']   = $organizer['website'] ?? '';
		$new_organizer['Email']     = $organizer['email'] ?? '';

		$post_id = Tribe__Events__API::createOrganizer( $new_organizer );

		if ( ! $post_id ) {
			return self::add_fail_message( $response, __( 'Failed to create organizer.', 'the-events-calendar' ) );
		} else {
			$response->data['organizer_id'] = $post_id;
		}

		return self::add_message( $response, __( 'Organizer created.', 'the-events-calendar' ) );
	}
}
