<?php
namespace Tribe\Events;

use Tribe__Events__Main as Main;
use Tribe__Events__JSON_LD__Venue as JSON_LD__Venue;

class JSON_LD__VenueTest extends \Codeception\TestCase\WPTestCase {

	protected $venue;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->create_test_data();
	}

	public function tearDown() {
		// your tear down methods here

		JSON_LD__Venue::unregister_all();

		// then
		parent::tearDown();
	}

	/**
	 * Create test data
	 *
	 * @since 4.9.2
	 * @return void
	*/
	public function create_test_data() {

		$this->venue = $this->factory()->post->create_and_get( [
				'post_type' => Main::VENUE_POST_TYPE,
				'post_title' => 'Camp Nou',
				'meta_input' => [
					'_VenueAddress'       => "C. d'Aristides Maillol, 12",
					'_VenueCity'          => 'Barcelona',
					'_VenueCountry'       => 'Spain',
					'_VenueProvince'      => 'Barcelona',
					'_VenueURL'           => 'http://fcbarcelona.com',
					'_VenueStateProvince' => 'Barcelona',
					'_VenueZip'           => '08028',
				],
			] );
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
	 * @since 4.9.2
	 */
	public function it_should_return_empty_array_when_passed_empty_values() {
		$this->assertEquals( [], $this->make_instance()->get_data( [], [] ) );
	}

	/**
	 * @test
	 * it should return array with one post in it if trying to get data for one venues
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_array_with_one_post_in_it_if_trying_to_get_data_for_one_post() {
		$post = $this->factory()->post->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $post );

		$this->assertInternalType( 'array', $data );
		$this->assertCount( 1, $data );
		$this->assertContainsOnly( 'stdClass', $data );
	}

	/**
	 * @test
	 * Check that the data for the JSON_LD is populated correctly
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_correct_data() {

		$sut          = $this->make_instance();
		$venue_id     = $this->venue->ID;

		$data    = $sut->get_data( $venue_id );
		$json_ld = $data[ $venue_id ];

		// Venue assertions
		$this->assertEquals( $json_ld->{ '@type' }, 'Place' );
		$this->assertEquals( $json_ld->name, get_the_title( $venue_id ) );
		$this->assertEquals( $json_ld->telephone, tribe_get_phone( $venue_id ) );
		$this->assertEquals( $json_ld->sameAs, tribe_get_venue_website_url( $venue_id ) );

		$this->assertEquals( $json_ld->address->{ '@type' }, 'PostalAddress' );
		$this->assertEquals( $json_ld->address->streetAddress, tribe_get_address( $venue_id ) );
		$this->assertEquals( $json_ld->address->addressLocality, tribe_get_city( $venue_id ) );
		$this->assertEquals( $json_ld->address->addressRegion, tribe_get_region( $venue_id ) );
		$this->assertEquals( $json_ld->address->postalCode, tribe_get_zip( $venue_id ) );
		$this->assertEquals( $json_ld->address->addressCountry, tribe_get_country( $venue_id ) );

	}

	/**
	 * @return Tribe__Events__JSON_LD__Venue
	 *
	 * @since 4.9.2
	 */
	private function make_instance() {
		return new JSON_LD__Venue();
	}
}