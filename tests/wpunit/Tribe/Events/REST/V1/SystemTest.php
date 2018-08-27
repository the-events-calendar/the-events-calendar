<?php
namespace Tribe\Events\REST\V1;

use Tribe__Events__REST__V1__System as System;

class SystemTest extends \Codeception\TestCase\WPTestCase {

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

		$this->assertInstanceOf( System::class, $sut );
	}

	/**
	 * @return System
	 */
	private function make_instance() {
		return new System();
	}

	/**
	 * @test
	 * it should mark TEC REST API as disabled if disabled option is set
	 */
	public function it_should_mark_tec_rest_api_as_disabled_if_disabled_option_is_set() {
		tribe_update_option( System::get_disable_option_name(), true );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->tec_rest_api_is_enabled() );
	}

	/**
	 * @test
	 * it should mark TEC REST API as disabled if filter is disabling it
	 */
	public function it_should_mark_tec_rest_api_as_disabled_if_filter_is_disabling_it() {
		add_filter( 'tribe_events_rest_api_enabled', '__return_false' );

		$sut = $this->make_instance();

		$this->assertFalse( $sut->tec_rest_api_is_enabled() );
	}
}