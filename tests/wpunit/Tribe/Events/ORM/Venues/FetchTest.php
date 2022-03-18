<?php

namespace Tribe\Events\ORM\Venues;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Venue;

class FetchTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
	}

	/**
	 * It should allow getting venues by address
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_address() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueAddress' => '123 Common Main St' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'address', 'Common' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'address', '/[[:digit:]]+ Common [[:alnum:]]+ St/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by city
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_city() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueCity' => 'Bright Rainbow Village' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'city', 'Rainbow' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'city', '/[[:alnum:]]+ Rainbow [[:alnum:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by country
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_country() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueCountry' => 'United States of America' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'country', 'States' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'country', '/[[:alnum:]]+ States [[:alnum:]]+ America/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by phone
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_phone() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenuePhone' => '123-555-9999' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'phone', '123' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'phone', '/[[:digit:]]+\-555\-[[:digit:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by postal_code
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_postal_code() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueZip' => '10025' ] ] );
		$this->factory()->venue->create_many( 3, [ 'meta_input' => [ '_VenueZip' => '2389' ] ] );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'postal_code', '25' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'postal_code', '/1[[:digit:]]+25/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by province
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_province() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueStateProvince' => 'New Brunswick' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'province', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'province', '/New [[:alnum:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by state
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_state() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueStateProvince' => 'New York' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state', '/New [[:alnum:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by state_province
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_state_province() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueStateProvince' => 'New York' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'state_province', 'New' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'state_province', '/New [[:alnum:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by website
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_website() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueURL' => 'https://twitter.com/roblovestwitter' ] ] );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_venues()->where( 'website', '://twitter.com/' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'website', '/.*:\/\/twitter.com\/.*/' )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * It should allow getting venues by events
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_events() {
		$matching = $this->factory()->venue->create_many( 2 );

		$events = array_merge(
			$this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventVenueID' => $matching[0] ] ] ),
			$this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventVenueID' => $matching[1] ] ] )
		);

		$no_venue_events = $this->factory()->event->create_many( 2 );

		$this->factory()->venue->create_many( 3 );

		$this->assertEqualSets( [ $matching[0] ], tribe_venues()->where( 'event', $events[0] )->get_ids() );
		$this->assertEqualSets( [ $matching[0] ], tribe_venues()->where( 'event', get_post( $events[0] ) )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'event', $events )->get_ids() );
		$this->assertEqualSets( $matching, tribe_venues()->where( 'event', array_map( 'get_post', $events ) )->get_ids() );
		$this->assertEqualSets( [], tribe_venues()->where( 'event', $no_venue_events[0] )->get_ids() );
		$this->assertCount( 5, tribe_venues()->get_ids() );
	}

	/**
	 * @test
	 */
	public function should_allow_fetching_venues_by_has_events() {
		$venues = $this->create_venues_and_events( 4, 3, 3 );

		$this->assertEqualSets( $venues['with'] , tribe_venues()->where( 'has_events', true )->get_ids() );
	}

	/**
	 * @test
	 */
	public function should_allow_fetching_venues_by_has_no_events() {
		$venues = $this->create_venues_and_events( 4, 3, 3 );

		$this->assertEqualSets( $venues['without'] , tribe_venues()->where( 'has_no_events', true )->get_ids() );
	}

	/**
	 * Creates a set of venues and a set of events for half the venues.
	 * Also creates a set of "extra" events for "noise"
	 *
	 * @since 5.5.0
	 *
	 * @param int $venues       The number of venues to create.
	 * @param int $events       The number of events to create per venue.
	 * @param int $extra_events The number of extra events to create.
	 *
	 * @return array<string,array<int,int>> List of IDs that were created, sorted by with/without events.
	 */
	public function create_venues_and_events( int $venues = 1, int $events = 1, int $extra_events = 0 ) {
		$with_events    = ceil( $venues / 2 );
		$without_events = floor( $venues / 2 );
		$returned_venues = [];

		while ($without_events > 0 ) {
			$id = $this->factory()->venue->create();
			$returned_venues['without'][] = $id;
			$without_events--;
		}

		while ($with_events > 0 ) {
			$id                = $this->factory()->venue->create();
			$returned_venues['with'][] = $id;

			$this->factory()->event->create_many( $events, [ 'venue' => $id ] );

			$with_events--;
		}

		while ($extra_events > 0 ) {
			$this->factory()->event->create();
			$extra_events--;
		}

		return $returned_venues;
	}
}
