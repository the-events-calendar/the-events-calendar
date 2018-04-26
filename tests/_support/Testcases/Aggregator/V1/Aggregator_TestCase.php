<?php

namespace Tribe\Events\Test\Testcases\Aggregator\V1;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Aggregator\V1\Service;

class Aggregator_TestCase extends WPTestCase {

	function setUp() {
		parent::setUp();
		$this->factory()->ea_service = new Service();
	}

}