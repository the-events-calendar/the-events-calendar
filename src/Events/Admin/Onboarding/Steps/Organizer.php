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
class Organizer extends Abstract_Step {
	/**
	 * The tab number for this step.
	 *
	 * @since 6.8.2
	 *
	 * @var int
	 */
	public static $step_number = 3;

	/**
	 * Process the organizer data.
	 *
	 * @since 6.8.1
	 *
	 * @param bool $organizer The organizer request data.
	 */
	public function process( $organizer ): bool {
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
