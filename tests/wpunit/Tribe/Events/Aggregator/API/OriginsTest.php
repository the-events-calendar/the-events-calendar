<?php

namespace Tribe\Events\Aggregator\API;

use Tribe\Events\Tests\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe__Events__Aggregator__API__Origins as Origins;
use Tribe__Events__Aggregator__Service as Service;

class OriginsTest extends Aggregator_TestCase {
	/**
	 * @var Service
	 */
	protected $service;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		tribe_set_var( 'events-aggregator.origins-data', null );
		$this->service = $this->prophesize( Service::class );
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

		$this->assertInstanceOf( Origins::class, $sut );
	}

	/**
	 * @test
	 * it should enable the Other URL origin if allowed from the server
	 */
	public function it_should_enable_the_other_url_origin_if_allowed_from_the_server() {
		$this->service->api()->willReturn( true );
		$this->service->get_origins()->willReturn( $this->factory()->ea_service->create_origins() );

		$sut = $this->make_instance();

		$origins = $sut->get();

		$this->assertArrayHasKey( 'url', $origins );
		$this->assertFalse( $origins['url']->disabled );
	}

	/**
	 * @return Origins
	 */
	private function make_instance() {
		tribe_register( 'events-aggregator.service', $this->service->reveal() );

		return new Origins();
	}

}