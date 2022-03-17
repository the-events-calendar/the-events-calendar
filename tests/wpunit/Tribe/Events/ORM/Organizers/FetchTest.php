<?php

namespace Tribe\Events\ORM\Organizers;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;

class FetchTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event     = new Event();
		$this->factory()->organizer = new Organizer();
	}

	/**
	 * It should allow getting organizers by name
	 *
	 * @test
	 */
	public function should_allow_getting_organizers_by_name() {
		$matching = $this->factory()->organizer->create( [ 'post_title' => 'Organ Izer Example' ] );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( [ $matching ], tribe_organizers()->where( 'name', 'organ-izer-example' )->get_ids() );
		$this->assertCount( 4, tribe_organizers()->get_ids() );
	}

	/**
	 * It should allow getting organizers by email
	 *
	 * @test
	 */
	public function should_allow_getting_organizers_by_email() {
		$matching = $this->factory()->organizer->create_many( 2, [ 'meta_input' => [ '_OrganizerEmail' => 'rob@organizingbyemail.com' ] ] );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_organizers()->where( 'email', 'rob' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_organizers()->where( 'email', '/rob.*\.com/' )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
	}

	/**
	 * It should allow getting organizers by phone
	 *
	 * @test
	 */
	public function should_allow_getting_organizers_by_phone() {
		$matching = $this->factory()->organizer->create_many( 2, [ 'meta_input' => [ '_OrganizerPhone' => '123-555-9999' ] ] );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_organizers()->where( 'phone', '123' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_organizers()->where( 'phone', '/[[:digit:]]+\-555\-[[:digit:]]+/' )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
	}

	/**
	 * It should allow getting organizers by website
	 *
	 * @test
	 */
	public function should_allow_getting_organizers_by_website() {
		$matching = $this->factory()->organizer->create_many( 2, [ 'meta_input' => [ '_OrganizerWebsite' => 'https://twitter.com/roblovestwitter' ] ] );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_organizers()->where( 'website', '://twitter.com/' )->get_ids() );
		$this->assertEqualSets( $matching, tribe_organizers()->where( 'website', '/.*:\/\/twitter.com\/.*/' )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
	}

	/**
	 * It should allow getting organizers by events
	 *
	 * @test
	 */
	public function should_allow_getting_organizers_by_events() {
		$matching = $this->factory()->organizer->create_many( 2 );

		$events = array_merge(
			$this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventOrganizerID' => $matching[0] ] ] ),
			$this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventOrganizerID' => $matching[1] ] ] )
		);

		$no_organizer_events = $this->factory()->event->create_many( 2 );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( [ $matching[0] ], tribe_organizers()->where( 'event', $events[0] )->get_ids() );
		$this->assertEqualSets( [ $matching[0] ], tribe_organizers()->where( 'event', get_post( $events[0] ) )->get_ids() );
		$this->assertEqualSets( $matching, tribe_organizers()->where( 'event', $events )->get_ids() );
		$this->assertEqualSets( $matching, tribe_organizers()->where( 'event', array_map( 'get_post', $events ) )->get_ids() );
		$this->assertEqualSets( [], tribe_organizers()->where( 'event', $no_organizer_events[0] )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
	}

	/**
	 * @test
	 */
	public function should_allow_fetching_organizers_by_has_events() {
		$organizers = $this->create_organizers_and_events( 4, 3, 0 );
		$this->assertEqualSets( $organizers['with'] , tribe_organizers()->where( 'has_events', true )->get_ids() );
	}

	/**
	 * @test
	 */
	public function should_allow_fetching_organizers_by_has_no_events() {
		$organizers = $this->create_organizers_and_events( 4, 3, 0 );
		$this->assertEqualSets( $organizers['without'] , tribe_organizers()->where( 'has_no_events', true )->get_ids() );
	}

	/**
	 * Creates a set of organizers and a set of events for half the organizers.
	 * Also creates a set of "extra" events for "noise"
	 *
	 * @since 5.5.0
	 *
	 * @param int $organizers   The number of organizers to create.
	 * @param int $events       The number of events to create per organizer.
	 * @param int $extra_events The number of extra events to create.
	 *
	 * @return array<string,array<int,int>> List of IDs that were created, sorted by with/without events.
	 */
	public function create_organizers_and_events( int $organizers = 1, int $events = 1, int $extra_events = 0 ) {
		$with_events         = ceil( $organizers / 2 );
		$without_events      = floor( $organizers / 2 );
		$returned_organizers = [];

		while ( $without_events > 0 ) {
			$id                               = $this->factory()->organizer->create();
			$returned_organizers['without'][] = $id;
			$without_events--;
		}

		while ( $with_events > 0 ) {
			$id                            = $this->factory()->organizer->create();
			$returned_organizers['with'][] = $id;

			$this->factory()->event->create_many( $events, [ 'organizers' => [ $id ] ] );

			$with_events--;
		}

		while ( $extra_events > 0 ) {
			$this->factory()->event->create();
			$extra_events--;
		}

		return $returned_organizers;
	}

}
