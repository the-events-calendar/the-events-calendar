<?php

namespace TEC\Events;

use Tribe__Events__API as API;
use Tribe__Events__Main as TEC;

class APITest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @after
	 */
	public function reregister_taxonomies(): void {
		TEC::instance()->register_taxonomy();
	}

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

	public function get_event_terms_will_work_when_cat_tax_unregistered(): void {
		unregister_taxonomy( TEC::TAXONOMY );
		$post_id = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
			'tax_input'  => [
				'post_tag' => [ 'tag1', 'tag2' ],
			]
		] )->create()->ID;

		$event_terms = API::get_event_terms( $post_id );

		$this->assertEquals( [
			'post_tag' => [ 'tag-1', 'tag-2' ]
		], $event_terms );
	}
}
