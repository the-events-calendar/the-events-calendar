<?php

namespace Tribe\Events\Tests\Factories;

use Tribe__Events__Main as Main;

class Venue extends \WP_UnitTest_Factory_For_Post {

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
				'_VenueURL'           => $lc_title . ' url',
				'_VenueStateProvince' => $lc_title . ' state_province',
			],
		];

		return parent::create_object( array_merge( $defaults, $args ) );
	}
}