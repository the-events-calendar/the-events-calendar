<?php

namespace Tribe\Events\Tests\Testcases;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Organizer;
use Tribe\Events\Tests\Factories\Venue;

class Events_TestCase extends WPTestCase {

	function setUp() {
		parent::setUp();

		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->factory()->organizer = new Organizer();
	}

}