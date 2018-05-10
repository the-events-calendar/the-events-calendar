<?php

namespace Tribe\Events\Test\Factories\REST\V1;

use Tribe\Events\Test\Factories\Organizer;

class Organizer_Response extends Organizer {


	function create( $args = array(), $generation_definitions = null ) {
		return $this->create_and_get( $args, $generation_definitions );
	}

	function create_and_get( $args = array(), $generation_definitions = null ) {
		$repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );

		return $repository->get_organizer_data( parent::create( $args, $generation_definitions ) );
	}
}