<?php

namespace TEC\Events\Updates;

use DateTimeZone;
use Tribe__Date_Utils;
use Tribe__Events__Timezones;

class Sync_UTCTest extends \Codeception\TestCase\WPTestCase {

	/**
	 *
	 * @test
	 */
	public function should_sync_when_utc_is_off() {
		global $wpdb;
		// Create a post first.
		$args  = [
			'start_date'   => '2018-01-01 09:00:00',
			'end_date'     => '2018-01-01 11:00:00',
			'timezone'     => 'Europe/Paris',
			'title'        => 'A test event',
			'_EventAllDay' => 'yes',
			'post_status'  => 'publish'
		];
		$event = tribe_events()->set_args( $args )->create();

		// No UTC dates out of sync yet.
		$this->assertEquals( 0, tribe_events()->by( 'ID', $event->ID )->sync_utc_dates() );

		// Push the UTC out of sync.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value='2013-01-01 11:00:00' WHERE meta_key='_EventEndDateUTC' AND post_id=%s", $event->ID ) );
		$this->assertEquals( 1, tribe_events()->by( 'ID', $event->ID )->sync_utc_dates() );
	}

	/**
	 * @test
	 */
	public function should_sync_utc_dates_correctly() {
		global $wpdb;
		// Create a post first.
		$args  = [
			'start_date'   => '2018-01-01 09:00:00',
			'end_date'     => '2018-01-01 11:00:00',
			'timezone'     => 'Europe/Paris',
			'title'        => 'A test event',
			'_EventAllDay' => 'yes',
			'post_status'  => 'publish'
		];
		$event = tribe_events()->set_args( $args )->create();

		// Push the UTC out of sync.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value='2013-01-11 11:00:00' WHERE meta_key='_EventEndDateUTC' AND post_id=%s", $event->ID ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value='2013-01-11 10:00:00' WHERE meta_key='_EventStartDateUTC' AND post_id=%s", $event->ID ) );
		// Sanity check the sync operation.
		$this->assertEquals( 1, tribe_events()->by( 'ID', $event->ID )->sync_utc_dates() );
		// Now fetch new data and see what we have.
		$start_utc = get_post_meta( $event->ID, '_EventStartDateUTC', true );
		$end_utc   = get_post_meta( $event->ID, '_EventEndDateUTC', true );
		$start     = get_post_meta( $event->ID, '_EventStartDate', true );
		$end       = get_post_meta( $event->ID, '_EventEndDate', true );
		$timezone  = get_post_meta( $event->ID, '_EventTimezone', true );
		$utc_tz    = new DateTimeZone( 'UTC' );

		$is_utc_offset = Tribe__Events__Timezones::is_utc_offset( $timezone );
		// Get the UTC date from our events localized (timezone relative) date.
		if ( $is_utc_offset ) {
			// Convert a UTC offset timezone to a localized timezone, e.g. UTC-5 to America/New_York.
			$new_start_utc_date = Tribe__Date_Utils::immutable( $start, $timezone )
			                                       ->setTimezone( $utc_tz )
			                                       ->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
			$new_end_utc_date   = Tribe__Date_Utils::immutable( $end, $timezone )
			                                       ->setTimezone( $utc_tz )
			                                       ->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		} else {
			$new_start_utc_date = Tribe__Events__Timezones::to_utc( $start, $timezone );
			$new_end_utc_date   = Tribe__Events__Timezones::to_utc( $end, $timezone );
		}
		$this->assertEquals( $new_start_utc_date, $start_utc );
		$this->assertEquals( $new_end_utc_date, $end_utc );
	}

}
