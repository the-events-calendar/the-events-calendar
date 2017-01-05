<?php

namespace Tribe\Events\Tests\Factories\REST\V1;

use Tribe\Events\Tests\Factories\Venue;

class Venue_Response extends Venue {

	function create_and_get( $args = array(), $generation_definitions = null ) {
		$repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );

		return $repository->get_event_data( parent::create( $args, $generation_definitions ) );
	}
}