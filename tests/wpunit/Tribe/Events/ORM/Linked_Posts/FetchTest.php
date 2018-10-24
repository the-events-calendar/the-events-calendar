<?php

namespace Tribe\Events\ORM\Linked_Posts;

use Tribe\Events\Test\Factories\Venue;

class FetchTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->venue = new Venue();
	}

	/**
	 * It should allow getting linked posts by event IDs
	 *
	 * @test
	 */
	public function should_allow_getting_linked_posts_by_event_ids() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueAddress' => '123 Common Main St' ] ] );

		$event_id = $matching[0];

		$this->assertEqualSets( [ $event_id ], tribe_linked_posts()->where( 'event', $event_id )->get_ids() );
		$this->assertEqualSets( [ $event_id ], tribe_linked_posts()->where( 'event', [ $event_id ] )->get_ids() );
		$this->assertCount( 2, tribe_linked_posts()->get_ids() );
	}

	/**
	 * It should allow getting linked posts by event objects
	 *
	 * @test
	 */
	public function should_allow_getting_linked_posts_by_event_objects() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueAddress' => '123 Common Main St' ] ] );

		$event_id = $matching[0];

		$event = get_post( $event_id );

		$this->assertEqualSets( [ $event_id ], tribe_linked_posts()->where( 'event', $event )->get_ids() );
		$this->assertEqualSets( [ $event_id ], tribe_linked_posts()->where( 'event', [ $event ] )->get_ids() );
		$this->assertCount( 2, tribe_linked_posts()->get_ids() );
	}

	/**
	 * It should allow getting linked posts by event objects and IDs
	 *
	 * @test
	 */
	public function should_allow_getting_linked_posts_by_event_objects_and_ids() {
		$matching = $this->factory()->venue->create_many( 2, [ 'meta_input' => [ '_VenueAddress' => '123 Common Main St' ] ] );

		$event_id = $matching[0];

		$event = get_post( $event_id );

		$other_event_id = $matching[1];

		$this->assertEqualSets( $matching, tribe_linked_posts()->where( 'event', [ $event, $other_event_id ] )->get_ids() );
		$this->assertCount( 2, tribe_linked_posts()->get_ids() );
	}

}
