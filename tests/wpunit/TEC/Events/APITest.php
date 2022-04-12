<?php

namespace TEC\Events;

use Tribe__Events__API as API;
use Tribe__Events__Main as TEC;

class APITest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should correctly handle daylight saving hours
	 *
	 * @test
	 */
	public function should_correctly_handle_daylight_saving_hours() {
		$timezone_string = 'Europe/Paris';
		// Create a post first.
		$id = static::factory()->post->create();
		// Update the post type with a direct query to skip filters.
		global $wpdb;
		$event_type = TEC::POSTTYPE;
		$wpdb->query( "update $wpdb->posts set post_type='{$event_type}' where ID = $id" );

		// Sunday, March 27, at 2AM France daylight saving time starts.
		API::saveEventMeta( $id, [
			'EventStartDate' => '2022-03-27',
			'EventStartTime' => '01:30:00',
			'EventEndDate'   => '2022-03-27',
			'EventEndTime'   => '04:30:00',
			'EventTimezone'  => $timezone_string,
		] );

		$this->assertEquals( 2 * HOUR_IN_SECONDS, (int) get_post_meta( $id, '_EventDuration', true ) );
	}

	/**
	 * It should correctly handle non-daylight saving hours
	 *
	 * @test
	 */
	public function should_correctly_handle_non_daylight_saving_hours() {
		$timezone_string = 'Europe/Paris';
		// Create a post first.
		$id = static::factory()->post->create();
		// Update the post type with a direct query to skip filters.
		global $wpdb;
		$event_type = TEC::POSTTYPE;
		$wpdb->query( "update $wpdb->posts set post_type='{$event_type}' where ID = $id" );

		// Daylight saving time kicked in on Sunday, March 27; we're past that at this point.
		API::saveEventMeta( $id, [
			'EventStartDate' => '2022-03-30',
			'EventStartTime' => '01:30:00',
			'EventEndDate'   => '2022-03-30',
			'EventEndTime'   => '04:30:00',
			'EventTimezone'  => $timezone_string,
		] );

		$this->assertEquals( 3 * HOUR_IN_SECONDS, (int) get_post_meta( $id, '_EventDuration', true ) );
	}
}
