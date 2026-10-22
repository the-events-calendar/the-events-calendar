<?php

namespace TEC\Events;

use DateTime;
use DateTimeZone;
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

	/**
	 * It should store a valid timezone identifier as submitted.
	 *
	 * @test
	 */
	public function should_store_a_valid_submitted_timezone() {
		$id = static::factory()->post->create();
		global $wpdb;
		$event_type = TEC::POSTTYPE;
		$wpdb->query( "update $wpdb->posts set post_type='{$event_type}' where ID = $id" );

		API::saveEventMeta( $id, [
			'EventStartDate' => '2022-03-27',
			'EventStartTime' => '01:30:00',
			'EventEndDate'   => '2022-03-27',
			'EventEndTime'   => '04:30:00',
			'EventTimezone'  => 'Europe/Paris',
		] );

		$this->assertEquals( 'Europe/Paris', get_post_meta( $id, '_EventTimezone', true ) );
	}

	public function invalid_timezone_provider(): array {
		return [
			'markup'                => [ '<img src="x" onerror="alert(1)">' ],
			'utc prefixed attribute' => [ 'UTC+5" onerror="alert(1)' ],
			'not a timezone'        => [ 'Not/A_Timezone' ],
		];
	}

	/**
	 * It should fall back to the site timezone instead of storing a value that is not a real
	 * timezone identifier or UTC offset, and derive the UTC dates and abbreviation from it.
	 *
	 * @test
	 * @dataProvider invalid_timezone_provider
	 */
	public function should_not_store_an_invalid_submitted_timezone( string $timezone ) {
		$site_timezone = 'America/New_York';
		update_option( 'timezone_string', $site_timezone );

		$id = static::factory()->post->create();
		global $wpdb;
		$event_type = TEC::POSTTYPE;
		$wpdb->query( "update $wpdb->posts set post_type='{$event_type}' where ID = $id" );

		$start_date = '2022-03-27';
		$start_time = '01:30:00';

		API::saveEventMeta( $id, [
			'EventStartDate' => $start_date,
			'EventStartTime' => $start_time,
			'EventEndDate'   => $start_date,
			'EventEndTime'   => '04:30:00',
			'EventTimezone'  => $timezone,
		] );

		$local_start = new DateTime( "$start_date $start_time", new DateTimeZone( $site_timezone ) );

		$this->assertEquals( $site_timezone, get_post_meta( $id, '_EventTimezone', true ) );
		$this->assertEquals( $local_start->format( 'T' ), get_post_meta( $id, '_EventTimezoneAbbr', true ) );
		$this->assertEquals(
			$local_start->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' ),
			get_post_meta( $id, '_EventStartDateUTC', true )
		);
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
