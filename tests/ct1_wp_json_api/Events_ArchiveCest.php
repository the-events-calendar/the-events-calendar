<?php

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\WP_JSON_API_Information;
use Wp_json_apiTester as Tester;

class Events_ArchiveCest {
	use WP_JSON_API_Information;

	/**
	 * It should return an empty array when there are no events in the site
	 *
	 * @test
	 */
	public function should_return_an_empty_array_when_there_are_no_events_in_the_site( Tester $I ) {
		// Sanity check.
		$I->assertEquals( [], tribe_events()->all() );

		$I->sendGET( $this->wp_json_api_events_base, [
			'_fields' => 'id',
			'orderby' => 'id',
			'order'   => 'asc',
		] );

		$I->seeResponseEquals( json_encode( [] ) );
	}

	/**
	 * It should return an array of events when there are events in the site
	 *
	 * @test
	 */
	public function should_return_an_array_of_events_when_there_are_events_in_the_site( Tester $I ) {
		// Create 3 events.
		$events = [];
		foreach ( range( 1, 3 ) as $k ) {
			$events[] = tribe_events()->set_args( [
				'title'      => "Event {$k}",
				'start_date' => 'tomorrow 10am',
				'end_date'   => 'tomorrow 11am',
				'timezone'   => 'America/New_York',
				'status'     => 'publish',
			] )->create();
		}
		$ids = wp_list_pluck( $events, 'ID' );
		// Sanity checks.
		$I->assertEquals( 3, tribe_events()->where( 'starts_after', '2018-01-01 00:00:00' )->count() );
		foreach ( $events as $event ) {
			$I->assertInstanceOf( Event::class, Event::find( $event->ID, 'post_id' ) );
			$I->assertInstanceOf( Occurrence::class, Occurrence::find( $event->ID, 'post_id' ) );
		}

		$I->sendGET( $this->wp_json_api_events_base, [
			'_fields' => 'id',
			'orderby' => 'id',
			'order'   => 'asc',
		] );

		sort( $ids, SORT_NUMERIC );
		$expected = array_map( static function ( int $id ) {
			return [ 'id' => $id ];
		}, $ids );
		$I->seeResponseEquals( json_encode( $expected ) );
	}
}
