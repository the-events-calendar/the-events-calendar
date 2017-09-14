<?php

namespace Tribe\Events;

use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Venue as Venue;

/**
 * Class VenueTest
 *
 * Really a proxy to test the base class.
 */
class VenueTest extends Events_TestCase {
	/**
	 * It should allow searching like in title
	 * @test
	 */
	public function it_should_allow_searching_like_in_title() {
		$venue_1 = $this->factory()->venue->create( [ 'post_title' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_title' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_title' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in content
	 *
	 * @test
	 */
	public function should_allow_searching_like_in_content() {
		$venue_1 = $this->factory()->venue->create( [ 'post_content' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_content' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_content' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in excerpt
	 *
	 * @test
	 */
	public function should_allow_searching_like_in_excerpt() {
		$venue_1 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Foo' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Bar' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_excerpt' => 'Venue Baz' ] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 1, $sut->find_like( 'venue foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Venue Foo' ) );
		$this->assertCount( 1, $sut->find_like( 'Foo Venue' ) );
		$this->assertCount( 1, $sut->find_like( 'foo venue' ) );
	}

	/**
	 * It should allow searching like in title, content and excerpt
	 *
	 * @test
	 */
	public function should_allow_searching_like_in_title_content_and_excerpt() {
		$venue_1 = $this->factory()->venue->create( [
			'post_excerpt' => 'Venue Foo',
			'post_content' => 'lorem dolor',
			'post_excerpt' => 'sed nunqua',
		] );
		$venue_2 = $this->factory()->venue->create( [
			'post_excerpt' => 'Venue Bar',
			'post_content' => 'dolor sit',
			'post_excerpt' => 'altera via',
		] );
		$venue_3 = $this->factory()->venue->create( [
			'post_excerpt' => 'Venue Baz',
			'post_content' => 'sit nunqua',
			'post_excerpt' => 'Caesar docet',
		] );

		$sut = $this->make_instance();

		$this->assertCount( 3, $sut->find_like( 'Venue' ) );
		$this->assertCount( 3, $sut->find_like( 'venue' ) );
		$this->assertCount( 2, $sut->find_like( 'dolor' ) );
		$this->assertCount( 2, $sut->find_like( 'sit' ) );
		$this->assertCount( 1, $sut->find_like( 'caesar' ) );
		$this->assertCount( 2, $sut->find_like( 'nunqua' ) );
	}

	/**
	 * It should allow searching like in custom fields
	 *
	 * @test
	 */
	public function should_allow_searching_like_in_custom_fields() {
		$venue_1 = $this->factory()->venue->create( [
			'post_title'   => 'Venue Foo',
			'post_content' => 'lorem dolor',
			'post_excerpt' => 'sed nunqua',
			'meta_input'   => [
				'_VenueAddress'       => '221b Baker Street',
				'_VenueCity'          => 'London',
				'_VenueProvince'      => 'Greater London',
				'_VenueState'         => 'England',
				'_VenueStateProvince' => 'England, Greater London',
				'_VenueZip'           => '223345',
				'_VenuePhone'         => '111111',
			],
		] );
		$venue_2 = $this->factory()->venue->create( [
			'post_title'   => 'Venue Bar',
			'post_content' => 'dolor sit',
			'post_excerpt' => 'altera via',
			'meta_input'   => [
				'_VenueAddress'       => '10, Piccadilly Circus',
				'_VenueCity'          => 'London',
				'_VenueProvince'      => 'Greater London',
				'_VenueState'         => 'England',
				'_VenueStateProvince' => 'England, Greater London',
				'_VenueZip'           => '223345',
				'_VenuePhone'         => '22222222',
			],
		] );
		$venue_3 = $this->factory()->venue->create( [
			'post_title'   => 'Venue Baz',
			'post_content' => 'sit nunqua',
			'post_excerpt' => 'Caesar docet',
			'meta_input'   => [
				'_VenueAddress'       => '100, Avenue du Temple',
				'_VenueCity'          => 'Paris',
				'_VenueProvince'      => 'Ile de France',
				'_VenueState'         => 'France',
				'_VenueStateProvince' => 'France, Ile de France',
				'_VenueZip'           => '23443',
				'_VenuePhone'         => '3333333',
			],
		] );

		$sut = $this->make_instance();

		$this->assertCount( 2, $sut->find_like( 'london' ) );
		$this->assertCount( 1, $sut->find_like( '3333333' ) );
		$this->assertCount( 1, $sut->find_like( 'temple avenue' ) );
		$this->assertCount( 2, $sut->find_like( 'greater london' ) );
		$this->assertCount( 1, $sut->find_like( 'france' ) );
		$this->assertCount( 1, $sut->find_like( 'france, ile de' ) );
		$this->assertCount( 2, $sut->find_like( '223345' ) );
	}

	/**
	 * @return Venue
	 */
	protected function make_instance() {
		return tribe( 'tec.linked-posts.venue' );
	}

	/**
	 * It should allow getting all venues related to an event
	 * @test
	 */
	public function it_should_allow_getting_all_venues_related_to_an_event() {
		$venue_1 = $this->factory()->venue->create();
		$venue_2 = $this->factory()->venue->create();
		$event_1 = $this->factory()->event->create( [ 'venue' => $venue_1 ] );
		$event_2 = $this->factory()->event->create( [ 'venue' => $venue_2 ] );
		$event_3 = $this->factory()->event->create();

		$sut = $this->make_instance();
		$this->assertEquals( [ $venue_1 ], $sut->find_for_event( $event_1 ) );
		$this->assertEquals( [ $venue_2 ], $sut->find_for_event( $event_2 ) );
		$this->assertEmpty( $sut->find_for_event( $event_3 ) );
	}

	/**
	 * It should return empty array if passing invalid event id
	 * @test
	 */
	public function it_should_return_empty_array_if_passing_invalid_event_id() {
		$sut = $this->make_instance();
		$this->assertEmpty( $sut->find_for_event( 23 ) );
	}

	/**
	 * It should allow getting venues with linked events
	 * @test
	 */
	public function it_should_allow_getting_venues_with_linked_events() {
		$venue_1 = $this->factory()->venue->create();
		$venue_2 = $this->factory()->venue->create();
		$venue_3 = $this->factory()->venue->create();
		$venue_4 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_1 ] );
		$this->factory()->event->create( [ 'venue' => $venue_2 ] );

		$sut = $this->make_instance();
		$this->assertEquals( [ $venue_1, $venue_2 ], $sut->find_with_events( true ) );
		$this->assertEquals( [ $venue_3, $venue_4 ], $sut->find_with_events( false ) );
	}

	/**
	 * It should return empty array if there are no venues and getting with events
	 * @test
	 */
	public function it_should_return_empty_array_if_there_are_no_venues_and_getting_with_events() {
		$this->factory()->event->create_many( 3 );

		$sut = $this->make_instance();
		$this->assertEmpty( $sut->find_with_events( true ) );
	}


	/**
	 * It should return empty array if there are no events and getting with events
	 * @test
	 */
	public function it_should_return_empty_array_if_there_are_no_events_and_getting_with_events() {
		$this->factory()->venue->create_many( 3 );

		$sut = $this->make_instance();
		$this->assertEmpty( $sut->find_with_events( true ) );
	}

	/**
	 * It should not consider draft and pending events when getting venues with events
	 * @test
	 */
	public function it_should_not_consider_draft_and_pending_events_when_getting_venues_with_events() {
		$venue_1 = $this->factory()->venue->create();
		$venue_2 = $this->factory()->venue->create();
		$venue_3 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_1 ] );
		$this->factory()->event->create( [ 'venue' => $venue_2, 'post_status' => 'draft' ] );
		$this->factory()->event->create( [ 'venue' => $venue_3, 'post_status' => 'pending' ] );

		$sut = $this->make_instance();
		$this->assertEquals( [ $venue_1 ], $sut->find_with_events( true ) );
		$this->assertEquals( [ $venue_1, $venue_2, $venue_3 ], $sut->find_with_events( true, [] ) );
	}

	/**
	 * It should allow getting venues with upcoming events
	 * @test
	 */
	public function it_should_allow_getting_venues_with_upcoming_events() {
		$venue_1 = $this->factory()->venue->create();
		$venue_2 = $this->factory()->venue->create();
		$venue_3 = $this->factory()->venue->create();
		$venue_4 = $this->factory()->venue->create();
		$venue_5 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_1, 'when' => '+1 month' ] );
		$this->factory()->event->create( [ 'venue' => $venue_2, 'when' => '+1 month' ] );
		$this->factory()->event->create( [ 'venue' => $venue_3, 'when' => '-1 month' ] );
		$this->factory()->event->create( [ 'venue' => $venue_4, 'when' => '-1 month' ] );

		$sut = $this->make_instance();
		$this->assertEquals( [ $venue_1, $venue_2 ], $sut->find_with_upcoming_events( true ) );
		$this->assertEquals( [ $venue_3, $venue_4, $venue_5 ], $sut->find_with_upcoming_events( false ) );
	}
}