<?php

namespace Tribe\Events\Aggregator\API;

use Prophecy\Argument;
use Tribe\Events\Tests\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe__Events__Aggregator__API__Origins as Origins;
use Tribe__Events__Aggregator__Service as Service;

class OriginsTest extends Aggregator_TestCase {

	/**
	 * @var \Tribe__Events__Aggregator__Service
	 */
	protected static $service_backup;

	/**
	 * @var Service
	 */
	protected $service;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		if ( null === self::$service_backup ) {
			self::$service_backup = tribe( 'events-aggregator.service' );
		}

		tribe_set_var( 'events-aggregator.origins-data', null );
		$this->service = $this->prophesize( Service::class );
	}

	public function tearDown() {
		// your tear down methods here
		tribe_register( 'events-aggregator.service', self::$service_backup );

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
		$mock_origins = $this->factory()->ea_service->create_origins();
		$this->service->get_origins( true )->willReturn( [ $mock_origins, null ] );
		$this->service->api()->willReturn( true );
		$this->service->get_origins()->willReturn( $this->factory()->ea_service->create_origins() );

		$sut = $this->make_instance();

		$origins = $sut->get();

		$this->assertArrayHasKey( 'url', $origins );
		$this->assertFalse( $origins['url']->disabled );
	}

	/**
	 * It should store the origins data in transients if successfully fetched
	 *
	 * @test
	 */
	public function should_store_the_origins_data_in_transients_if_successfully_fetched() {
		$mock_origins = $this->factory()->ea_service->create_origins();
		$this->service->get_origins( true )->willReturn( [ $mock_origins, null ] );
		$this->service->api()->willReturn( true );
		$sut = $this->make_instance();
		delete_transient( $sut->cache_group . '_origin_limit' );
		delete_transient( $sut->cache_group . '_origin_oauth' );
		delete_transient( $sut->cache_group . '_origins' );
		delete_transient( $sut->cache_group . '_fetch_lock' );
		tribe_set_var( 'events-aggregator.origins-data', [] );

		$sut->get();

		$this->assertEquals( $mock_origins->limit, get_transient( $sut->cache_group . '_origin_limit' ) );
		$this->assertEquals( $mock_origins->oauth, get_transient( $sut->cache_group . '_origin_oauth' ) );
		$this->assertEmpty( get_transient( $sut->cache_group . '_fetch_lock' ) );
	}

	/**
	 * It should not store the origins data in transients if not successfully fetched
	 *
	 * @test
	 */
	public function should_not_store_the_origins_data_in_transients_if_not_successfully_fetched() {
		$mock_origins = $this->factory()->ea_service->create_origins();
		$this->service->get_origins( true )->willReturn( [
			$mock_origins,
			new \WP_Error( 'something-happened' ),
		] );
		$this->service->api()->willReturn( true );
		$sut = $this->make_instance();
		delete_transient( $sut->cache_group . '_origin_limit' );
		delete_transient( $sut->cache_group . '_origin_oauth' );
		delete_transient( $sut->cache_group . '_origins' );
		delete_transient( $sut->cache_group . '_fetch_lock' );

		$sut->get();

		$this->assertEmpty( get_transient( $sut->cache_group . '_origin_limit' ) );
		$this->assertEmpty( get_transient( $sut->cache_group . '_origin_oauth' ) );
		$this->assertEmpty( get_transient( $sut->cache_group . '_fetch_lock' ) );
	}

	/**
	 * It should store the origins data in transients for 5' if response is a 403
	 *
	 * @test
	 */
	public function should_store_the_origins_data_in_transients_for_5_if_response_is_a_403() {
		$mock_origins = $this->factory()->ea_service->create_origins();
		$this->service->get_origins( true )->willReturn( [
			$mock_origins,
			[ 'response' => [ 'code' => 403 ] ],
		] );
		$this->service->get_origins( Argument::any() )->willReturn( $mock_origins );
		$this->service->api()->willReturn( true );
		$sut = $this->make_instance();
		delete_transient( $sut->cache_group . '_origin_limit' );
		delete_transient( $sut->cache_group . '_origin_oauth' );
		delete_transient( $sut->cache_group . '_origins' );
		delete_transient( $sut->cache_group . '_fetch_lock' );

		$sut->get();

		$this->assertEmpty( get_transient( $sut->cache_group . '_origin_limit' ) );
		$this->assertEmpty( get_transient( $sut->cache_group . '_origin_oauth' ) );
		$this->assertEquals( $mock_origins, get_transient( $sut->cache_group . '_fetch_lock' ) );
	}

	/**
	 * @return Origins
	 */
	private function make_instance() {
		tribe_register( 'events-aggregator.service', $this->service->reveal() );

		return new Origins();
	}
}