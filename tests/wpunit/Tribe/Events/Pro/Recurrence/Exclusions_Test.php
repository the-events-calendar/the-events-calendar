<?php
namespace Tribe\Events\Pro\Recurrence;

use Prophecy\Argument;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Admin_Notices as Admin_Notices;
use Tribe__Events__Pro__Recurrence__Exclusions as Exclusions;

class ExclusionsTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;
	protected $date_default_timezone;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->date_default_timezone = date_default_timezone_get();
	}

	public function tearDown() {
		// your tear down methods here
		$this->restore_system_timezone();

		// then
		parent::tearDown();
	}

	public function different_timezones() {
		return [
			[ 'America/Vancouver' ],
			[ 'Europe/Rome' ],
			[ 'Etc/GMT+9' ],
			[ 'UTC' ],
			[ 'Asia/Kathmandu' ],
		];
	}

	/**
	 * remove exclusion will not exclude date on the day before excluded one
	 *
	 * @dataProvider  different_timezones
	 */
	public function test_remove_exclusion_will_not_exclude_date_on_the_day_before_excluded_one( $timezone_string ) {
		$this->set_system_timezone( $timezone_string );

		$duration = 60 * 60 * 3;
		$sut      = new Exclusions( $timezone_string );

		$date_durations  = [
			[ 'timestamp' => strtotime( '4 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '3 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '2 days ago' ), 'duration' => $duration ],
		];
		$exclusion_dates = [
			[ 'timestamp' => strtotime( '8 am today', strtotime( 'today' ) ), 'duration' => 0 ]
		];

		$this->restore_system_timezone();


		$this->assertEquals( $date_durations, $sut->remove_exclusions( $date_durations, $exclusion_dates ) );
	}

	/**
	 * remove exclusions will not exclude date starting on day before excluded and finishing in excluded day
	 *
	 * @dataProvider  different_timezones
	 */
	public function test_remove_exclusions_will_not_exclude_date_starting_on_day_before_excluded_and_finishing_in_excluded_day( $timezone_string ) {
		$this->set_system_timezone( $timezone_string );

		$duration = 60 * 60 * 3;
		$sut      = new Exclusions( $timezone_string );

		$date_durations  = [
			[ 'timestamp' => strtotime( '4 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '3 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '2 days ago' ), 'duration' => $duration ], // 11pm + 3 hrs = 1am of excluded day
			[ 'timestamp' => strtotime( 'yesterday 11pm' ), 'duration' => $duration ],
		];
		$exclusion_dates = [
			[ 'timestamp' => strtotime( '8 am today', strtotime( 'today' ) ), 'duration' => 0 ]
		];

		$this->restore_system_timezone();

		$this->assertEquals( $date_durations, $sut->remove_exclusions( $date_durations, $exclusion_dates ) );
	}

	/**
	 * remove exclusions will exclude date starting and finishing in excluded date
	 *
	 * @dataProvider  different_timezones
	 */
	public function test_remove_exclusions_will_exclude_date_starting_and_finishing_in_excluded_date( $timezone_string ) {
		$this->set_system_timezone( $timezone_string );

		$duration = 60 * 60 * 3;
		$sut      = new Exclusions( $timezone_string );

		$date_durations  = [
			[ 'timestamp' => strtotime( '4 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '3 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '2 days ago' ), 'duration' => $duration ], // 10am + 3 hrs = 1pm of excluded day
			[ 'timestamp' => strtotime( 'today 10am' ), 'duration' => $duration ],
		];
		$exclusion_dates = [
			[ 'timestamp' => strtotime( '8 am today', strtotime( 'today' ) ), 'duration' => 0 ]
		];
		$this->restore_system_timezone();

		$in       = $date_durations;
		$expected = array_values( array_splice( $date_durations, 0, 3 ) );
		$this->assertEquals( $expected, $sut->remove_exclusions( $in, $exclusion_dates ) );
	}

	/**
	 * remove exclusions will exclude date starting on excluded date and finishing day after
	 *
	 * @dataProvider  different_timezones
	 */
	public function test_remove_exclusions_will_exclude_date_starting_on_excluded_date_and_finishing_day_after( $timezone_string ) {
		$this->set_system_timezone( $timezone_string );

		$duration = 60 * 60 * 3;
		$sut      = new Exclusions( $timezone_string );

		$date_durations  = [
			[ 'timestamp' => strtotime( '4 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '3 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '2 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( 'today 11pm' ), 'duration' => $duration ],
			// 11pm + 3 hrs = 1am of day after excluded day
		];
		$exclusion_dates = [
			[ 'timestamp' => strtotime( '8 am today', strtotime( 'today' ) ), 'duration' => 0 ]
		];
		$this->restore_system_timezone();

		$in       = $date_durations;
		$expected = array_values( array_splice( $date_durations, 0, 3 ) );
		$this->assertEquals( $expected, $sut->remove_exclusions( $in, $exclusion_dates ) );
	}

	/**
	 * remove exclusions will not exclude date starting date after excluded
	 *
	 * @dataProvider different_timezones
	 */
	public function test_remove_exclusions_will_not_exclude_date_starting_date_after_excluded( $timezone_string ) {
		$this->set_system_timezone( $timezone_string );

		$duration = 60 * 60 * 3;
		$sut      = new Exclusions( $timezone_string );

		$date_durations  = [
			[ 'timestamp' => strtotime( '4 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '3 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '2 days ago' ), 'duration' => $duration ],
			[ 'timestamp' => strtotime( '+2 days' ), 'duration' => $duration ],
		];
		$exclusion_dates = [
			[ 'timestamp' => strtotime( '8 am today', strtotime( 'today' ) ), 'duration' => 0 ]
		];
		$this->restore_system_timezone();

		$this->assertEquals( $date_durations, $sut->remove_exclusions( $date_durations, $exclusion_dates ) );
	}

	protected function restore_system_timezone() {
		date_default_timezone_set( $this->date_default_timezone );
	}

	/**
	 * @param $timezone_string
	 */
	protected function set_system_timezone( $timezone_string ) {
		date_default_timezone_set( $timezone_string );
	}
}