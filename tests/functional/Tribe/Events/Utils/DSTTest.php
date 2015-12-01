<?php
namespace Tribe\Events\Utils;

use Tribe__Events__Utils__DST as DST;

class DSTTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;
	protected $tz_backup;

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
				false
			],
			[
				strtotime( 'April 4th, 2015' ),
				true
			],
			[
				strtotime( 'December 10th, 2015' ),
				false
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
				strtotime( '10am February 1st, 2015' )
			],
			[
				strtotime( '11am April 4th, 2015' ),
				strtotime( 'February 3rd, 2015' ),
				strtotime( '10am April 4th, 2015' )
			],
			[
				strtotime( '9am January 4th, 2015' ),
				strtotime( 'April 4th, 2015' ),
				strtotime( '10am January 4th, 2015' )
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
}