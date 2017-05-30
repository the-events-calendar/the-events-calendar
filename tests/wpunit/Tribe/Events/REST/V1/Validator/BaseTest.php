<?php

namespace Tribe\Events\REST\V1\Validator;

use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\REST\V1\Venue_Response;
use Tribe\Events\Tests\Factories\Venue;
use Tribe__Events__REST__V1__Validator__Base as Validator;

class BaseTest extends \Codeception\TestCase\WPTestCase {

	function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->factory()->venue_response = new Venue_Response();
	}

	public function is_venue_id_or_entry_bad_inputs() {
		return [
			[ 23 ],
			[ 'foo' ],
			[ '23' ],
			[ [ 'address' => '221b Baker Street' ] ],
		];
}
	/**
	 * Test is_venue_id_or_entry with bad inputs
	 *
	 * @test
	 * @dataProvider is_venue_id_or_entry_bad_inputs
	 */
	public function test_is_venue_id_or_entry($input) {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id_or_entry( $input ) );
	}

	/**
	 * Test is_venue_id_or_entry with good inputs
	 *
	 * @test
	 */
	public function test_is_venue_id_or_entry_with_good_inputs() {
		/** @var \WP_Post $venue */
		$venue = $this->factory()->venue->create_and_get();
		$venue_response = $this->factory()->venue_response->create();

		$sut = $this->make_instance();
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue->ID ) );
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_response['id'] ) );
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_response ) );
		$venue_data = [
			'venue' => 'Some venue', // the only required param
		];
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_data ) );
	}

	/**
	 * @return Validator
	 */
	protected function make_instance() {
		return new Validator();
	}
}
