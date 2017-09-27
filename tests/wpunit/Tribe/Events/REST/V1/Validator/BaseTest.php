<?php

namespace Tribe\Events\REST\V1\Validator;

use Tribe\Events\Tests\Testcases\REST\V1\Events_Testcase;
use Tribe__Events__REST__V1__Validator__Base as Validator;

class BaseTest extends Events_Testcase {

	public function linked_post_bad_inputs() {
		return [
			[ 23 ],
			[ 'foo' ],
			[ '23' ],
			[ [ 'website' => 'http://example.com' ] ],
		];
	}

	/**
	 * Test is_venue_id_or_entry with bad inputs
	 *
	 * @test
	 * @dataProvider linked_post_bad_inputs
	 */
	public function test_is_venue_id_or_entry( $input ) {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_venue_id_or_entry( $input ) );
	}

	/**
	 * @return Validator
	 */
	protected function make_instance() {
		return new Validator();
	}

	/**
	 * Test is_venue_id_or_entry with good inputs
	 *
	 * @test
	 */
	public function test_is_venue_id_or_entry_with_good_inputs() {
		/** @var \WP_Post $venue */
		$venue = $this->factory()->venue->create_and_get();
		$venue_response = $this->factory()->rest_venue_response->create();

		$sut = $this->make_instance();
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue->ID ) );
		$this->assertTrue( $sut->is_venue_id_or_entry( [ 'id' => $venue->ID ] ) );
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_response['id'] ) );
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_response ) );
		$venue_data = [
			'venue' => 'Some venue', // the only required param
		];
		$this->assertTrue( $sut->is_venue_id_or_entry( $venue_data ) );
	}

	/**
	 * Test is_organizer_id_or_entry with bad inputs
	 *
	 * @test
	 * @dataProvider linked_post_bad_inputs
	 */
	public function test_is_organizer_id_or_entry_with_bad_inputs( $input ) {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->is_organizer_id_or_entry( $input ) );
	}

	/**
	 * Test is_orgnanizer_id_or_entry with list of valid IDs
	 *
	 * @test
	 */
	public function test_is_orgnanizer_id_or_entry_with_list_of_valid_i_ds() {
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();
		$organizer_3 = $this->factory()->organizer->create();
		$ids_array = [ $organizer_1, $organizer_2, $organizer_3 ];
		$ids_comma_separated_list = implode( ',', $ids_array );

		$sut = $this->make_instance();

		$this->assertTrue( $sut->is_organizer_id_or_entry( $ids_array ) );
		$this->assertTrue( $sut->is_organizer_id_or_entry( $ids_comma_separated_list ) );
	}

	/**
	 * Test is_organizer_id_or_entry with one entry
	 *
	 * @test
	 */
	public function test_is_organizer_id_or_entry_with_one_entry() {
		$sut = $this->make_instance();

		$organizers = [
			[ 'organizer' => 'foo' ], // minimum required parameter
		];

		$this->assertTrue( $sut->is_organizer_id_or_entry( $organizers ) );
	}

	/**
	 * Test is_organizer_id_or_entry with many entries
	 *
	 * @test
	 */
	public function test_is_organizer_id_or_entry_with_many_entries() {
		$sut = $this->make_instance();

		$organizers = [
			[ 'organizer' => 'foo' ], // minimum required parameter
			[ 'organizer' => 'baz' ], // minimum required parameter
			[ 'organizer' => 'bar' ], // minimum required parameter
		];

		$this->assertTrue( $sut->is_organizer_id_or_entry( $organizers ) );

		$bad_organizers = [
			[ 'organizer' => 'foo' ], // minimum required parameter
			[ 'organizer' => 'baz' ], // minimum required parameter
			[ 'website' => 'http://example.com' ], // missing required parameter
		];

		$this->assertFalse( $sut->is_organizer_id_or_entry( $bad_organizers ) );
	}

	/**
	 * Test is_organizer_is_or_entry with mixed existing IDs and entries
	 *
	 * @test
	 */
	public function test_is_organizer_is_or_entry_with_mixed_existing_i_ds_and_entries() {
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();

		$sut = $this->make_instance();

		$organizers = [
			[ 'id' => $organizer_1 ],
			[ 'organizer' => 'baz' ], // minimum required parameter
			[ 'id' => $organizer_2 ],
			[ 'organizer' => 'bar' ], // minimum required parameter
		];

		$this->assertTrue( $sut->is_organizer_id_or_entry( $organizers ) );
	}
}
