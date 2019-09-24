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
use Tribe\Utils\Post_Thumbnail;

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
			$address               = tribe_get_address( $this->post->ID );
			$country               = tribe_get_country( $this->post->ID );
			$city                  = tribe_get_city( $this->post->ID );
			$state_province        = tribe_get_stateprovince( $this->post->ID );
			$state                 = tribe_get_state( $this->post->ID );
			$province              = tribe_get_province( $this->post->ID );
			$zip                   = tribe_get_zip( $this->post->ID );

			$properties = [
				'address'               => $address,
				'country'               => $country,
				'city'                  => $city,
				'state_province'        => $state_province,
				'state'                 => $state,
				'province'              => $province,
				'zip'                   => $zip,
				'permalink'             => get_permalink( $this->post->ID ),
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
