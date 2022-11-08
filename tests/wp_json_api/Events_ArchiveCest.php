<?php

use Tribe\Events\Test\Traits\WP_JSON_API_Information;
use Wp_json_apiTester as Tester;

class Events_ArchiveCest {
	use WP_JSON_API_Information;

	public function _before( Wp_json_apiTester $I ) {
	}

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
		// Sanity checks.
		$I->assertEquals( 3, tribe_events()->where( 'starts_after', '2018-01-01 00:00:00' )->count() );

		$I->sendGET( $this->wp_json_api_events_base, [
			'_fields' => 'id',
			'orderby' => 'id',
			'order'   => 'asc',
		] );

		$ids = wp_list_pluck( $events, 'ID' );
		sort( $ids, SORT_NUMERIC );
		$expected = array_map( static function ( int $id ) {
			return [ 'id' => $id ];
		}, $ids );
		$I->seeResponseEquals( json_encode( $expected ) );
	}
}
