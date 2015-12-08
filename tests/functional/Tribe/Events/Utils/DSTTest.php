<?php
namespace Tribe\Events\Utils;

use Tribe__Events__Utils__DST as DST;

class DSTTest extends \Codeception\TestCase\WPTestCase {

	protected        $backupGlobals = false;
	protected static $tz_backup;

	public static function setUpBeforeClass() {
		self::$tz_backup = date_default_timezone_get();

		return parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		date_default_timezone_set( self::$tz_backup );

		return parent::tearDownAfterClass();
	}


	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		date_default_timezone_set( 'Europe/Rome' );
		// DST on march 27th
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	public function dst_times() {
		return [
			[
				strtotime( 'January 14th, 2015' ),
				false,
			],
			[
				strtotime( 'April 4th, 2015' ),
				true,
			],
			[
				strtotime( 'December 10th, 2015' ),
				false,
			],
		];
	}

	/**
	 * @test
	 * it should spot DST times
	 * @dataProvider  dst_times
	 */
	public function it_should_spot_dst_times( $time, $is_dst ) {
		$sut = new DST( $time );
		$this->assertEquals( $is_dst, $sut->is_in_dst() );
	}

	public function times_and_alignments() {
		return [
			[
				strtotime( '10am February 1st, 2015' ),
				strtotime( 'February 3rd, 2015' ),
				strtotime( '10am February 1st, 2015' ),
			],
			[
				strtotime( '11am April 4th, 2015' ),
				strtotime( 'February 3rd, 2015' ),
				strtotime( '10am April 4th, 2015' ),
			],
			[
				strtotime( '9am January 4th, 2015' ),
				strtotime( 'April 4th, 2015' ),
				strtotime( '10am January 4th, 2015' ),
			],
		];
	}

	/**
	 * @test
	 * it should allow aligning times
	 * @dataProvider times_and_alignments
	 */
	public function it_should_allow_aligning_times( $expected, $time_one, $time_two ) {
		$dst_one = new DST( $time_one );
		$dst_two = new DST( $time_two );

		$aligned_time = $dst_two->get_time_aligned_with( $dst_one );

		$this->assertEquals( $expected, $aligned_time );
	}

	public function sys_and_wp_timezone_identifiers() {
		return [
			[
				'America/New_York',
				'Europe/Rome',
				'November 1st, 2015'
			],
			[
				'Europe/Rome',
				'America/New_York',
				'November 1st, 2015'
			],
			[
				'America/New_York',
				'America/New_York',
				'November 1st, 2015'
			],
			[
				'Europe/Rome',
				'Europe/Rome',
				'November 1st, 2015'
			],
		];
	}

	/**
	 * @test
	 * it should take WP timezone settings into account if different from system timezone
	 * @dataProvider sys_and_wp_timezone_identifiers
	 */
	public function it_should_take_wp_timezone_settings_into_account_if_different_from_system_timezone( $timezone_identifier, $wp_timezone_identifier, $date ) {
		$date = strtotime( $date );
		date_default_timezone_set( $wp_timezone_identifier );
		$expected = (bool) date( 'I', $date );

		date_default_timezone_set( $timezone_identifier );
		$sys_is_in_dst = (bool) date( 'I', $date );

		update_option( 'timezone_string', $wp_timezone_identifier );

		$sut          = new DST( $date );
		$wp_is_in_dst = $sut->is_in_dst();

		$this->assertEquals( $expected, $wp_is_in_dst );
	}

	/**
	 * @test
	 * it should restore system timezone after operations
	 */
	public function it_should_restore_system_timezone_after_operations() {
		$system_timezone = 'America/New_York';
		date_default_timezone_set($system_timezone);
		$wp_timezone     = 'Europe/Rome';
		update_option( 'timezone_string', $wp_timezone );

		$sut = new DST( strtotime( 'November 1st, 2015' ) );
		$sut->is_in_dst();

		$this->assertEquals( 'America/New_York', date_default_timezone_get() );
	}

}