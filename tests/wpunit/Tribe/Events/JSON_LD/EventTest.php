<?php
namespace Tribe\Events;

use Tribe__Events__Main as Main;
use Tribe__Events__JSON_LD__Event as JSON_LD__Event;

class JSON_LD__EventTest extends \Codeception\TestCase\WPTestCase {

	protected $event;
	protected $free_event;
	protected $venue;
	protected $organizer;

	public function setUp() {
		// before
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// your set up methods here
		$this->create_test_data();
	}

	public function tearDown() {
		// your tear down methods here

		JSON_LD__Event::unregister_all();

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

		tribe_update_option( 'tribe_events_timezone_mode', 'event' );

		$this->organizer = $this->factory()->post->create_and_get( [
				'post_type' => Main::ORGANIZER_POST_TYPE,
				'post_title' => 'Leo Messi',
				'meta_input' => [
					'_OrganizerPhone'   => '+1 888 8888',
					'_OrganizerWebsite' => 'http://messi.com',
					'_OrganizerEmail'   => 'leo@messi.com',
				],
			] );

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

		$start_date = strtotime( "+1 weeks" );
		$end_date   = strtotime( "+1 weeks 4 hour" );

		$this->event = $this->factory()->post->create_and_get( [
				'post_type'  => Main::POSTTYPE,
				'post_title' => 'Barcelona vs. Real Madrid',
				'meta_input' => [
					'_EventStartDate'        => $start_date,
					'_EventEndDate'          => $end_date,
					'_EventCost'             => '100',
					'_EventCurrencySymbol'   => '€',
					'_EventCurrencyPosition' => 'prefix',
					'_EventVenueID'          => $this->venue->ID,
					'_EventOrganizerID'      => $this->organizer->ID,
					'_EventURL'              => 'http://elclasico.com',
				],
			] );


		$this->free_event = $this->factory()->post->create_and_get( [
			'post_type'  => Main::POSTTYPE,
			'post_title' => 'Barcelona vs. Real Madrid',
			'meta_input' => [
				'_EventStartDate'        => $start_date,
				'_EventEndDate'          => $end_date,
				'_EventCost'             => 'FREE',
				'_EventCurrencySymbol'   => '$',
				'_EventCurrencyPosition' => 'prefix',
				'_EventVenueID'          => $this->venue->ID,
				'_EventOrganizerID'      => $this->organizer->ID,
				'_EventURL'              => 'http://elclasico.com',
			],
		] );
	}

	/**
	 * @test
	 * it should be instantiatable
	*/
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__JSON_LD__Event', $sut );
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
	 * it should return array with one post in it if trying to get data for one event
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
	 * it should return array with ten posts in it if trying to get data for ten events
	 *
	 * @since 4.9.2
	 */
	public function it_should_return_array_with_ten_posts_in_it_if_trying_to_get_data_for_ten_posts() {
		$post = $this->factory()->post->create_many( 10, [ 'post_type' => Main::POSTTYPE ] );

		$sut  = $this->make_instance();
		$data = $sut->get_data( $post );

		$this->assertInternalType( 'array', $data );
		$this->assertCount( 10, $data );
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
		$event_id     = $this->event->ID;
		$organizer_id = $this->organizer->ID;
		$venue_id     = $this->venue->ID;

		$data    = $sut->get_data( $event_id );
		$json_ld = $data[ $event_id ];

		// Event assertions
		$this->assertEquals( $json_ld->name, get_the_title( $event_id ) );
		$this->assertEquals( $json_ld->{ '@type' }, 'Event' );
		$this->assertEquals( $json_ld->offers->price, '100' );

		// Venue assertions
		$this->assertEquals( $json_ld->location->{ '@type' }, 'Place' );
		$this->assertEquals( $json_ld->location->name, get_the_title( $venue_id ) );
		$this->assertEquals( $json_ld->location->telephone, tribe_get_phone( $venue_id ) );
		$this->assertEquals( $json_ld->location->sameAs, tribe_get_venue_website_url( $venue_id ) );

		$this->assertEquals( $json_ld->location->address->{ '@type' }, 'PostalAddress' );
		$this->assertEquals( $json_ld->location->address->streetAddress, tribe_get_address( $venue_id ) );
		$this->assertEquals( $json_ld->location->address->addressLocality, tribe_get_city( $venue_id ) );
		$this->assertEquals( $json_ld->location->address->addressRegion, tribe_get_region( $venue_id ) );
		$this->assertEquals( $json_ld->location->address->postalCode, tribe_get_zip( $venue_id ) );
		$this->assertEquals( $json_ld->location->address->addressCountry, tribe_get_country( $venue_id ) );

		// Organizer assertions
		$this->assertEquals( $json_ld->organizer->{ '@type' }, 'Person' );
		$this->assertEquals( $json_ld->organizer->name, get_the_title( $organizer_id ) );
		$this->assertEquals( $json_ld->organizer->telephone, tribe_get_organizer_phone( $organizer_id ) );
		$this->assertEquals( $json_ld->organizer->sameAs, tribe_get_organizer_website_url( $organizer_id ) );

	}

	/**
	 * @test
	 * Check that the data for the JSON_LD is populated correctly
	 *
	 * @since 5.14.1
	 */
	public function it_should_return_correct_data_for_free_event() {

		$sut          = $this->make_instance();
		$event_id     = $this->free_event->ID;
		// We don't need Organizer/Venue for this test.

		$data    = $sut->get_data( $event_id );
		$json_ld = $data[ $event_id ];

		// Event assertions
		$this->assertEquals( $json_ld->name, get_the_title( $event_id ) );
		$this->assertEquals( $json_ld->{ '@type' }, 'Event' );
		$this->assertEquals( $json_ld->offers->price, 0 );

	}

	/**
	 * @return \Tribe__Events__JSON_LD__Event
	 *
	 * @since 4.9.2
	 */
	private function make_instance() {
		return new JSON_LD__Event();
	}
}
