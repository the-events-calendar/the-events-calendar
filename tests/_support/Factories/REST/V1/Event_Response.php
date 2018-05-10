<?php

namespace Tribe\Events\Test\Factories\REST\V1;

use Tribe\Events\Test\Factories\Event;

class Event_Response extends Event {

	function create( $args = array(), $generation_definitions = null ) {
		return $this->create_and_get( $args, $generation_definitions );
	}

	function create_and_get( $args = array(), $generation_definitions = null ) {
		$repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );

		$data = $repository->get_event_data( parent::create( $args, $generation_definitions ) );

		return $data;
	}
}
