<?php

namespace Tribe\Events\Importer;
require_once 'File_Importer_VenuesTest.php';


class File_Importer_Venues_AddressTest extends File_Importer_VenuesTest {

	/**
	 * @test
	 * it should insert venue address if provided
	 */
	public function it_should_insert_venue_post_content_if_address_is_provided() {
		$this->data = [
			'address_1'   => '1626 Second Avenue',
			'address_2_1' => '',
		];
		$sut        = $this->make_instance( 'venues-address' );
		$post_id    = $sut->import_next_row();

		$this->assertEquals( '1626 Second Avenue', get_post_meta( $post_id, '_VenueAddress', true ) );
	}

	/**
	 * @test
	 * it should overwrite address when reimporting
	 */
	public function it_should_overwrite_post_content_when_reimporting() {
		$this->data = [
			'address_1'   => 'West 55th Street',
			'address_2_1' => '259-A',
		];
		$sut        = $this->make_instance( 'venues-address' );
		$post_id    = $sut->import_next_row();

		$this->assertEquals( 'West 55th Street 259-A', get_post_meta( $post_id, '_VenueAddress', true ) );

		$this->data       = [
			'address_1'   => 'West 55th Street',
			'address_2_1' => '',
		];
		$sut              = $this->make_instance( 'venues-address' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( 'West 55th Street', get_post_meta( $post_id, '_VenueAddress', true ) );
	}

	/**
	 * @test
	 * it should restore a venue address that has been emptied
	 */
	public function it_should_restore_a_venue_address_that_has_been_emptied() {
		$this->data = [
			'address_1'   => '',
			'address_2_1' => '',
		];
		$sut        = $this->make_instance( 'venues-address' );
		$post_id    = $sut->import_next_row();

		$this->assertEquals( '', get_post_meta( $post_id, '_VenueAddress', true ) );

		$this->data       = [
			'address_1'   => '10 Downing St',
			'address_2_1' => '',
		];
		$sut              = $this->make_instance( 'venues-address' );
		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '10 Downing St', get_post_meta( $post_id, '_VenueAddress', true ) );
	}
}
