<?php

namespace Tribe\Events\Test\Factories;

use Tribe__Events__Main as Main;

class Venue extends \WP_UnitTest_Factory_For_Post {

	/**
	 * @var array An array containing some pre-compiled locations data.
	 */
	protected $locations
		= [
			'new_york' => [
				'_VenueAddress	'   => '939 Lexington Ave',
				'_VenueCity	'      => 'New York',
				'_VenueCountry	'   => 'United States',
				'_VenueProvince'      => '',
				'_VenueState'         => 'NY',
				'_VenueZip'           => '10065',
				'_VenuePhone'         => '',
				'_VenueURL'           => '',
				'_VenueShowMap'       => 'true',
				'_VenueShowMapLink'   => 'true',
				'_VenueStateProvince' => 'NY',
			],
			'paris'    => [
				'_VenueAddress'       => '37 Rue de la Bûcherie',
				'_VenueCity'          => 'Paris',
				'_VenueCountry'       => 'France',
				'_VenueProvince'      => 'Paris',
				'_VenueState'         => '',
				'_VenueZip'           => '75005',
				'_VenuePhone'         => '',
				'_VenueURL'           => '',
				'_VenueShowMap'       => 'true',
				'_VenueShowMapLink'   => 'true',
				'_VenueStateProvince' => 'Paris',
			],
		];

	function create_object( $args ) {
		$args['post_type'] = Main::VENUE_POST_TYPE;

		$title = 'Venue' . uniqid();
		$lc_title = strtolower( $title );

		$defaults = [
			'meta_input' => [
				'_EventShowMap'       => '1',
				'_EventShowMapLink'   => '1',
				'_VenueAddress'       => $lc_title . ' address',
				'_VenueCity'          => $lc_title . 'city',
				'_VenueCountry'       => $lc_title . ' country',
				'_VenueProvince'      => $lc_title . ' province',
				'_VenueState'         => $lc_title . ' state',
				'_VenueZip'           => $lc_title . ' zip',
				'_VenuePhone'         => $lc_title . ' phone',
				'_VenueURL'           => 'http://' . str_slug( $lc_title ) . '.com',
				'_VenueStateProvince' => $lc_title . ' state_province',
			],
		];

		if ( isset( $args['location'] ) && isset( $this->locations[ $args['location'] ] ) ) {
			$defaults['meta_input'] = array_merge( $defaults['meta_input'], $this->locations[ $args['location'] ] );
			unset( $args['location'] );
		}

		if ( isset( $args['meta_input'] ) ) {
			$defaults['meta_input'] = array_merge( $defaults['meta_input'], $args['meta_input'] );
			unset( $args['meta_input'] );
		}

		return parent::create_object( array_merge( $defaults, $args ) );
	}
}