<?php
namespace Tribe\Events;

use Tribe__Events__JSON_LD__Venue as JSON_LD__Venue;

class JSON_LD__VenueTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		JSON_LD__Venue::unregister_all();

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	*/
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__JSON_LD__Venue', $sut );
	}

	/**
	 * @test
	 * It should return an empty array if the input data is empty
	 *
	 * @since TBD
	 */
	public function it_should_return_empty_array_when_passed_empty_values() {
		$this->assertEquals( [], $this->make_instance()->get_data( [], [] ) );
	}

	/**
	 * @return Tribe__Events__JSON_LD__Venue
	 *
	 * @since TBD
	 */
	private function make_instance() {
		return new JSON_LD__Venue();
	}
}