<?php
namespace Tribe\Events\Linked_Posts;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__Main;

class Publish_AssociatedTest extends WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		$this->factory()->event = new Event();
		$this->factory()->organizer = new Organizer();
		$this->factory()->venue = new Venue();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_should_publish_non_private_linked_posts() {
		$venue_1_id = $this->factory()->venue->create( [
			'post_status' => 'draft',
		] );
		$venue_2_id = $this->factory()->venue->create( [
			'post_status' => 'private',
		] );
		$venue_3_id = $this->factory()->venue->create( [
			'post_status' => 'pending',
		] );
		$organizer_1_id = $this->factory()->organizer->create( [
			'post_status' => 'draft',
		] );
		$organizer_2_id = $this->factory()->organizer->create( [
			'post_status' => 'future',
			'post_date'   => date( 'Y-m-d 00:00:00', strtotime( '+1 month' ) ),
		] );
		$organizer_3_id = $this->factory()->organizer->create( [
			'post_status' => 'trash',
		] );

		$event_args = [
			'post_status' => 'publish',
		];

		$event_id = $this->factory()->event->create( $event_args );

		$meta_ids = [
			'venue'     => [
				$venue_1_id,
				$venue_2_id,
				$venue_3_id,
			],
			'organizer' => [
				$organizer_1_id,
				$organizer_2_id,
				$organizer_3_id,
			],
		];

		foreach ( $meta_ids['venue'] as $id ) {
			add_post_meta( $event_id, '_EventVenueID', $id );
		}

		foreach ( $meta_ids['organizer'] as $id ) {
			add_post_meta( $event_id, '_EventOrganizerID', $id );
		}

		/** @var Tribe__Events__Main $main */
		$main = tribe( 'tec.main' );

		$event       = tribe_events()->where( 'ID', $event_id )->first();
		$venue_1     = get_post( $venue_1_id );
		$venue_2     = get_post( $venue_2_id );
		$venue_3     = get_post( $venue_3_id );
		$organizer_1 = get_post( $organizer_1_id );
		$organizer_2 = get_post( $organizer_2_id );
		$organizer_3 = get_post( $organizer_3_id );

		$this->assertEquals( 'draft', $venue_1->post_status );
		$this->assertEquals( 'private', $venue_2->post_status );
		$this->assertEquals( 'pending', $venue_3->post_status );
		$this->assertEquals( 'draft', $organizer_1->post_status );
		$this->assertEquals( 'future', $organizer_2->post_status );
		$this->assertEquals( 'trash', $organizer_3->post_status );

		$main->publishAssociatedTypes( $event_id, $event );

		$venue_1     = get_post( $venue_1_id );
		$venue_2     = get_post( $venue_2_id );
		$venue_3     = get_post( $venue_3_id );
		$organizer_1 = get_post( $organizer_1_id );
		$organizer_2 = get_post( $organizer_2_id );
		$organizer_3 = get_post( $organizer_3_id );

		$this->assertEquals( 'publish', $venue_1->post_status );
		$this->assertEquals( 'private', $venue_2->post_status );
		$this->assertEquals( 'publish', $venue_3->post_status );
		$this->assertEquals( 'publish', $organizer_1->post_status );
		$this->assertEquals( 'publish', $organizer_2->post_status );
		$this->assertEquals( 'publish', $organizer_3->post_status );
	}
}
