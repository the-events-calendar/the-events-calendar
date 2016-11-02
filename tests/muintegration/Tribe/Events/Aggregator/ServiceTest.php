<?php
namespace Tribe\Events\Aggregator;

use Tribe__Events__Aggregator__Service as Service;

class ServiceTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Service::class, $sut );
	}

	private function make_instance() {
		return new Service();
	}

}