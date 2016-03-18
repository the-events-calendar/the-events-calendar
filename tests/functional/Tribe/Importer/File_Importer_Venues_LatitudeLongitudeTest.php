<?php

namespace Tribe\Events\Importer;
require_once 'File_Importer_VenuesTest.php';


class File_Importer_Venues_LatitudeLongitudeTest extends File_Importer_VenuesTest {

	public function setUp() {
		parent::setUp();
		remove_filter( 'tribe_events_venue_created', array( \Tribe__Events__Pro__Geo_Loc::instance(), 'save_venue_geodata' ), 10 );
	}


	/**
	 * @test
	 * it should not mark record as invalid if missing latitude and longitude information
	 */
	public function it_should_not_mark_record_as_invalid_if_missing_latitude_and_longitude_information() {
		$this->data        = [
			'lat_1'  => '',
			'long_1' => '',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
	}

	/**
	 * @test
	 * it should import latitude and longitude if specified
	 */
	public function it_should_import_latitude_and_longitude_if_specified() {
		$this->data        = [
			'lat_1'  => '55.843727',
			'long_1' => '-4.191534',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( '1', get_post_meta( $post_id, '_VenueOverwriteCoords', true ) );
		$this->assertCount( 1, get_post_meta( $post_id, '_VenueLat', false ) );
		$this->assertCount( 1, get_post_meta( $post_id, '_VenueLng', false ) );
		$this->assertEquals( '55.843727', get_post_meta( $post_id, '_VenueLat', true ) );
		$this->assertEquals( '-4.191534', get_post_meta( $post_id, '_VenueLng', true ) );
	}

	/**
	 * @test
	 * it should not set the venue to overwrite coords is lat or long are missing
	 */
	public function it_should_not_set_the_venue_to_overwrite_coords_is_lat_or_long_are_missing() {
		$this->data        = [
			'lat_1'  => '',
			'long_1' => '-4.191534',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );
		$this->assertEquals( '0', get_post_meta( $post_id, '_VenueOverwriteCoords', true ) );
		$this->assertCount( 1, get_post_meta( $post_id, '_VenueLat', false ) );
		$this->assertCount( 1, get_post_meta( $post_id, '_VenueLng', false ) );
		$this->assertEquals( '', get_post_meta( $post_id, '_VenueLat', true ) );
		$this->assertEquals( '-4.191534', get_post_meta( $post_id, '_VenueLng', true ) );
	}

	/**
	 * @test
	 * it should not overwrite previously set latitude
	 */
	public function it_should_not_overwrite_previously_set_latitude() {
		$this->data        = [
			'lat_1'  => '55.843727',
			'long_1' => '-4.191534',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );

		$this->data = [
			'lat_1'  => '18.843727',
			'long_1' => '-4.191534',
		];

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '55.843727', get_post_meta( $reimport_post_id, '_VenueLat', true ) );
	}

	/**
	 * @test
	 * it should not overwrite previously set longitude
	 */
	public function it_should_not_overwrite_previously_set_longitude() {
		$this->data        = [
			'lat_1'  => '55.843727',
			'long_1' => '-4.191534',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );

		$this->data = [
			'lat_1'  => '55.843727',
			'long_1' => '18',
		];

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '-4.191534', get_post_meta( $reimport_post_id, '_VenueLng', true ) );
	}

	/**
	 * @test
	 * it should not overwrite previously set overwrite coords
	 */
	public function it_should_not_overwrite_previously_set_overwrite_coords() {
		$this->data        = [
			'lat_1'  => '55.843727',
			'long_1' => '-4.191534',
		];
		$this->field_map[] = 'venue_latitude';
		$this->field_map[] = 'venue_longitude';

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$post_id = $sut->import_next_row();

		$this->assertNotFalse( $post_id );

		$this->data = [
			'lat_1'  => '',
			'long_1' => '',
		];

		$sut = $this->make_instance( 'venues-latitude-longitude' );

		$reimport_post_id = $sut->import_next_row();

		$this->assertEquals( $post_id, $reimport_post_id );
		$this->assertEquals( '1', get_post_meta( $reimport_post_id, '_VenueOverwriteCoords', true ) );
	}

}