<?php
/**
 * Models an Venue.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Models\Post_Types
 */
namespace Tribe\Events\Models\Post_Types;

use Tribe\Models\Post_Types\Base;
use Tribe\Utils\Post_Thumbnail;
use Tribe__Events__Pro__Geo_Loc as Geolocalization;

/**
 * Class Venue.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Post_Types
 */
class Venue extends Base {

	/**
	 * {@inheritDoc}
	 */
	protected function build_properties( $filter ) {
		try {
			$cache_this = $this->get_caching_callback( $filter );

			$address               = tribe_get_address( $this->post->ID );
			$country               = tribe_get_country( $this->post->ID );
			$city                  = tribe_get_city( $this->post->ID );
			$state_province        = tribe_get_stateprovince( $this->post->ID );
			$state                 = tribe_get_state( $this->post->ID );
			$province              = tribe_get_province( $this->post->ID );
			$zip                   = tribe_get_country( $this->post->ID );
			$overwrite_coordinates = tribe_is_truthy( get_post_meta( $this->post->ID, Geolocalization::OVERWRITE, true ) );
			$latitude              = get_post_meta( $this->post->ID, Geolocalization::LAT, true );
			$longitude             = get_post_meta( $this->post->ID, Geolocalization::LNG, true );
			$geolocation_string    = get_post_meta( $this->post->ID, Geolocalization::ADDRESS, true );

			$properties = [
				'address'               => $address,
				'country'               => $country,
				'city'                  => $city,
				'state_province'        => $state_province,
				'state'                 => $state,
				'province'              => $province,
				'zip'                   => $zip,
				'overwrite_coordinates' => $overwrite_coordinates,
				'latitude'              => $latitude,
				'longitude'             => $longitude,
				'geolocation_string'    => $geolocation_string,
				'thumbnail'             => ( new Post_Thumbnail( $this->post->ID ) )->on_resolve( $cache_this ),
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