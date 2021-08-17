<?php
/**
 * Models a Venue.
 *
 * @since   4.9.9
 *
 * @package Tribe\Events\Models\Post_Types
 */
namespace Tribe\Events\Models\Post_Types;

use Tribe\Models\Post_Types\Base;

/**
 * Class Venue.
 *
 * @since   4.9.9
 *
 * @package Tribe\Events\Post_Types
 */
class Venue extends Base {

	/**
	 * {@inheritDoc}
	 */
	protected function build_properties( $filter ) {
		try {
			$address         = tribe_get_address( $this->post->ID );
			$country         = tribe_get_country( $this->post->ID );
			$city            = tribe_get_city( $this->post->ID );
			$state_province  = tribe_get_stateprovince( $this->post->ID );
			$state           = tribe_get_state( $this->post->ID );
			$province        = tribe_get_province( $this->post->ID );
			$zip             = tribe_get_zip( $this->post->ID );
			$phone           = tribe_get_phone( $this->post->ID );
			$permalink       = get_permalink( $this->post->ID );
			$directions_link = tribe_get_map_link( $this->post->ID );
			$website_url     = tribe_get_venue_website_url( $this->post->ID );

			$properties = [
				'address'         => $address,
				'country'         => $country,
				'city'            => $city,
				'state_province'  => $state_province,
				'state'           => $state,
				'province'        => $province,
				'zip'             => $zip,
				'phone'           => $phone,
				'permalink'       => $permalink,
				'directions_link' => $directions_link,
				'website'         => $website_url,
			];
		} catch ( \Exception $e ) {
			return [];
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_cache_slug() {
		return 'venues';
	}
}
