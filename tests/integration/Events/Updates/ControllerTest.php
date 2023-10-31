<?php
namespace TEC\Events\Updates;

use DateInterval;
use DateTime;
use DateTimeZone;
use TEC\Common\Tests\Provider\Controller_Test_Case;

/**
 * Class ControllerTest
 *
 * @since   TBD
 *
 * @package TEC\Events\Updates
 */
class ControllerTest extends Controller_Test_Case {
	/**
	 * @var string Our class name.
	 */
	protected $controller_class = Controller::class;

	/**
	 * @test
	 */
	public function should_fix_all_day_events() {
		// Given a start of midnight, normal cut off times.
		tribe_update_option( 'multiDayCutoff', '00:00' );
		$tz     = new DateTimeZone( 'America/Los_Angeles' );
		$utc_tz = new DateTimeZone( 'UTC' );

		// Setup our dates.
		$start     = new DateTime( '2019-01-02 00:00:00', $tz );
		$end       = new DateTime( '2019-01-03 23:59:59', $tz );
		$start_utc = ( clone $start )->setTimezone( $utc_tz );
		$end_utc   = ( clone $end )->setTimezone( $utc_tz );

		$format = 'Y-m-d H:i:s';
		$post   = tribe_events()->set_args( [
			'start_date'   => $start->format( $format ),
			'timezone'     => $tz->getName(),
			'duration'     => ( 48 * HOUR_IN_SECONDS ) - 1,
			'status'       => 'publish',
			'title'        => 'Faux Event',
			'_EventAllDay' => 'yes'
		] )
		                        ->create();
		// Sanity check.
		$this->assertEquals( $start->format( $format ), get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( $end->format( $format ), get_post_meta( $post->ID, '_EventEndDate', true ) );
		$this->assertEquals( $start_utc->format( $format ), get_post_meta( $post->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( $end_utc->format( $format ), get_post_meta( $post->ID, '_EventEndDateUTC', true ) );

		// Trigger our mass updates to go forward 8 hours.
		$controller = tribe( Controller::class );
		$controller->fix_all_day_events( '08:00:00' );

		// Setup 8 hour incremented vars.
		$period            = new DateInterval( "PT8H" );
		$shifted_start     = ( clone $start )->add( $period );
		$shifted_end       = ( clone $end )->add( $period );
		$shifted_start_utc = ( clone $shifted_start )->setTimezone( $utc_tz );
		$shifted_end_utc   = ( clone $shifted_end )->setTimezone( $utc_tz );

		// Now test it shifted 8 hours forward.
		wp_cache_flush(); // Must clear to bypass the post meta cache.
		$this->assertEquals( $shifted_start->format( $format ), get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( $shifted_end->format( $format ), get_post_meta( $post->ID, '_EventEndDate', true ) );
		$this->assertEquals( $shifted_start_utc->format( $format ), get_post_meta( $post->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( $shifted_end_utc->format( $format ), get_post_meta( $post->ID, '_EventEndDateUTC', true ) );

		// Now go backwards with an extra hop to validate concurrent filters work.
		tribe_update_option( 'multiDayCutoff', '01:00' );
		tribe_update_option( 'multiDayCutoff', '00:00' );
		wp_cache_flush(); // Must clear to bypass the post meta cache.
		$this->assertEquals( $start->format( $format ), get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( $end->format( $format ), get_post_meta( $post->ID, '_EventEndDate', true ) );
		$this->assertEquals( $start_utc->format( $format ), get_post_meta( $post->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( $end_utc->format( $format ), get_post_meta( $post->ID, '_EventEndDateUTC', true ) );
	}
}
