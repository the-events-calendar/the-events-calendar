<?php

namespace Tribe\Events\Editor;

use Codeception\TestCase\WPTestCase;
use DateTime;
use DateTimeZone;
use Tribe__Date_Utils as Dates;
use WP_REST_Request;

class Meta_Utc_Dates_Test extends WPTestCase {
	public function setUp() {
		parent::setUp();

		// The suite bootstrap registers the editor provider after `init`, so the meta registration has not run.
		$meta = tribe( 'events.editor.meta' );
		if ( ! has_filter( 'rest_after_insert_' . \Tribe__Events__Main::POSTTYPE, [ $meta, 'add_utc_dates' ] ) ) {
			$meta->register();
		}
	}

	/**
	 * @test
	 */
	public function it_should_compute_the_utc_dates_in_the_timezone_stored_for_an_invalid_one() {
		update_option( 'timezone_string', 'America/New_York' );
		$start = '2030-06-01 10:00:00';
		$end   = '2030-06-01 12:00:00';

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event_id = $this->factory()->post->create( [ 'post_type' => \Tribe__Events__Main::POSTTYPE ] );

		$request = new WP_REST_Request( 'POST', '/wp/v2/' . \Tribe__Events__Main::POSTTYPE . '/' . $event_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				[
					'meta' => [
						'_EventStartDate' => $start,
						'_EventEndDate'   => $end,
						'_EventTimezone'  => 'Not/A_Zone',
					],
				]
			)
		);
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

		$stored_zone = get_post_meta( $event_id, '_EventTimezone', true );
		$to_utc      = static fn( string $date ) => ( new DateTime( $date, new DateTimeZone( $stored_zone ) ) )
			->setTimezone( new DateTimeZone( 'UTC' ) )
			->format( Dates::DBDATETIMEFORMAT );

		$this->assertSame( 'America/New_York', $stored_zone );
		$this->assertSame( $to_utc( $start ), get_post_meta( $event_id, '_EventStartDateUTC', true ) );
		$this->assertSame( $to_utc( $end ), get_post_meta( $event_id, '_EventEndDateUTC', true ) );
	}
}
