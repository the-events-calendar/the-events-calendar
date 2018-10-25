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
		$matching = $this->factory()->organizer->create_many( 2, [ 'post_title' => 'Organ Izer Example' ] );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( $matching, tribe_organizers()->where( 'name', 'Izer' )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
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

		$events  = $this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventOrganizerID' => $matching[0] ] ] );
		$events += $this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventOrganizerID' => $matching[1] ] ] );

		$no_organizer_events = $this->factory()->event->create_many( 2 );

		$this->factory()->organizer->create_many( 3 );

		$this->assertEqualSets( [ $matching[0] ], tribe_organizers()->where( 'event', $events[0] )->get_ids() );
		$this->assertEqualSets( [ $matching[0] ], tribe_organizers()->where( 'event', get_post( $events[0] ) )->get_ids() );
		$this->assertEqualSets( [ $matching ], tribe_organizers()->where( 'event', $events )->get_ids() );
		$this->assertEqualSets( [ $matching ], tribe_organizers()->where( 'event', array_map( 'get_post', $events ) )->get_ids() );
		$this->assertEqualSets( [], tribe_organizers()->where( 'event', $no_organizer_events[0] )->get_ids() );
		$this->assertCount( 5, tribe_organizers()->get_ids() );
	}

}
