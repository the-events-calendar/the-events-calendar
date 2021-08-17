<?php
/**
 * Models an Organizer.
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Models\Post_Types
 */

namespace Tribe\Events\Models\Post_Types;

use Tribe\Models\Post_Types\Base;

/**
 * Class Organizer
 *
 * @since   5.3.0
 *
 * @package Tribe\Events\Models\Post_Types
 */
class Organizer extends Base {

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.3.0
	 */
	protected function get_cache_slug() {
		return 'organizers';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.3.0
	 */
	protected function build_properties( $filter ) {
		try {
			$phone   = tribe_get_organizer_phone( $this->post->ID );
			$website = tribe_get_organizer_website_url( $this->post->ID );
			// Do not mangle the email now, it should fall on the client code to apply antispambot filters to it.
			$email     = tribe_get_organizer_email( $this->post->ID, false );
			$permalink = get_permalink( $this->post->ID );

			$properties = [
				'phone'     => $phone,
				'website'   => $website,
				'email'     => $email,
				'permalink' => $permalink,
			];
		} catch ( \Exception $e ) {
			return [];
		}

		return $properties;
	}
}
