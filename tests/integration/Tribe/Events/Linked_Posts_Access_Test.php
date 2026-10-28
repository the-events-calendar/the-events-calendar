<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__API;
use Tribe__Events__Ignored_Events as Ignored_Events;

class Linked_Posts_Access_Test extends WPTestCase {
	/**
	 * @test
	 */
	public function it_should_not_link_a_post_of_another_type_as_organizer() {
		$page_id  = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Carrier',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
			]
		)->create()->ID;

		$organizer_id = tribe_organizers()->set_args( [ 'title' => 'Organizer', 'status' => 'publish' ] )->create()->ID;

		Tribe__Events__API::saveEventMeta(
			$event_id,
			[
				'EventStartDate'   => '2030-01-01',
				'EventStartHour'   => '10',
				'EventStartMinute' => '00',
				'EventEndDate'     => '2030-01-01',
				'EventEndHour'     => '12',
				'EventEndMinute'   => '00',
				'EventOrganizerID' => [ $organizer_id, $page_id ],
			]
		);

		$linked = array_map( 'absint', get_post_meta( $event_id, '_EventOrganizerID', false ) );

		$this->assertContains( $organizer_id, $linked );
		$this->assertNotContains( $page_id, $linked );
	}

	/**
	 * @test
	 */
	public function it_should_not_restore_an_event_that_is_not_ignored() {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Draft',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
			]
		)->create()->ID;

		Ignored_Events::instance()->restore_event( $event_id );

		$this->assertSame( 'draft', get_post_status( $event_id ) );
	}
}
