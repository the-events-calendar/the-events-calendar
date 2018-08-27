<?php
namespace Tribe\Events\REST\V1;

use Tribe__Events__REST__V1__Settings as Settings;
use Tribe__Events__REST__V1__System as System;

class SettingsTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__Events__REST__V1__System
	 */
	protected $system;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->system = $this->prophesize( System::class );
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

		$this->assertInstanceOf( Settings::class, $sut );
	}

	/**
	 * @return Settings
	 */
	protected function make_instance() {
		return new Settings( $this->system->reveal() );
	}

	/**
	 * @test
	 * it should not add any fields if the system does not support the WP REST API
	 */
	public function it_should_not_add_any_fields_if_the_system_does_not_support_the_wp_rest_api() {
		$this->system->supports_wp_rest_api()->willReturn( false );

		$sut = $this->make_instance();

		$fields = array( 'foo' => 'bar' );
		$this->assertEquals( $fields, $sut->filter_tribe_addons_tab_fields( $fields ) );
	}

	/**
	 * @test
	 * it should not add any field if the system does not support the TEC REST API
	 */
	public function it_should_not_add_any_field_if_the_system_does_not_support_the_tec_rest_api() {
		$this->system->supports_wp_rest_api()->willReturn( true );
		$this->system->supports_tec_rest_api()->willReturn( false );

		$sut = $this->make_instance();

		$fields = array( 'foo' => 'bar' );
		$this->assertEquals( $fields, $sut->filter_tribe_addons_tab_fields( $fields ) );
	}
}
