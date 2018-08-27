<?php

namespace Tribe\Events\Test\Testcases\Aggregator\V1;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Aggregator\V1\Import_Record;
use Tribe\Events\Test\Factories\Aggregator\V1\Service;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;

class Aggregator_TestCase extends WPTestCase {

	function setUp() {
		parent::setUp();
		$this->factory()->ea_service    = new Service();
		$this->factory()->import_record = new Import_Record();
		$this->factory()->event         = new Event();
		$this->factory()->venue         = new Venue();
		$this->factory()->organizer     = new Organizer();
	}

}