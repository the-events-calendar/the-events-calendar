<?php

namespace Tribe\Events\Tests\Testcases\REST\V1;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Tests\Factories\Event as Event_Factory;
use Tribe\Events\Tests\Factories\Organizer as Organizer_Factory;
use Tribe\Events\Tests\Factories\REST\V1\Event_Response;
use Tribe\Events\Tests\Factories\REST\V1\Organizer_Response;
use Tribe\Events\Tests\Factories\REST\V1\Venue_Response;
use Tribe\Events\Tests\Factories\Venue as Venue_Factory;

class Events_Testcase extends WPTestCase {

	function setUp() {
		parent::setUp();

		$this->factory()->event = new Event_Factory();
		$this->factory()->venue = new Venue_Factory();
		$this->factory()->organizer = new Organizer_Factory();
		$this->factory()->rest_event_response = new Event_Response();
		$this->factory()->rest_venue_response = new Venue_Response();
		$this->factory()->rest_organizer_response = new Organizer_Response();
	}
}
