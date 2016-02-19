<?php
namespace Tribe\Events\Dates;

use Tribe__Date_Utils as Date_Utils;
use Tribe__Events__Dates__Known_Range as Known_Range;
use Tribe__Events__Main as Main;

class Known_RangeTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;

	protected $format = '';

	protected $two_hrs = 7200;

	protected $seven_days = 604800;

	protected $start;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->start = time();
		$this->format =  Date_Utils::DBDATETIMEFORMAT;
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should set the earliest date to the start of the earliest event
	 */
	public function it_should_set_the_earliest_date_to_the_start_of_the_earliest_event() {
		$earliest = date( $this->format, $this->start );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => $earliest,
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->seven_days + $this->two_hrs ),
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $earliest, tribe_get_option( 'earliest_date' ) );
	}

	/**
	 * @test
	 * it should set latest date to end of latest event
	 */
	public function it_should_set_latest_date_to_end_of_latest_event() {
		$latest = date( $this->format, $this->start + $this->seven_days + $this->two_hrs );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => $latest,
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $latest, tribe_get_option( 'latest_date' ) );
	}

	/**
	 * @test
	 * it should reset earliest date to updated start of earliest event
	 */
	public function it_should_reset_earliest_date_to_updated_start_of_earliest_event() {
		$earliest    = date( $this->format, $this->start );
		$earliest_id = $this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => $earliest,
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->seven_days + $this->two_hrs ),
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $earliest, tribe_get_option( 'earliest_date' ) );

		$new_earliest = date( $this->format, $this->start - $this->two_hrs );
		update_post_meta( $earliest_id, '_EventStartDate', $new_earliest );

		$sut->rebuild_known_range();

		$this->assertEquals( $new_earliest, tribe_get_option( 'earliest_date' ) );
	}

	/**
	 * @test
	 * it should reset latest date to updated end of latest event
	 */
	public function it_should_reset_latest_date_to_updated_end_of_latest_event() {
		$latest_time = $this->start + $this->seven_days + $this->two_hrs;
		$latest      = date( $this->format, $latest_time );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$latest_id = $this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => $latest,
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $latest, tribe_get_option( 'latest_date' ) );

		$new_latest = date( $this->format, $latest_time + $this->two_hrs );
		update_post_meta( $latest_id, '_EventEndDate', $new_latest );

		$sut->rebuild_known_range();

		$this->assertEquals( $new_latest, tribe_get_option( 'latest_date' ) );
	}

	/**
	 * @test
	 * it should set earliest date to start of new earliest event
	 */
	public function it_should_set_earliest_date_to_start_of_new_earliest_event() {
		$earliest = date( $this->format, $this->start );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => $earliest,
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->seven_days + $this->two_hrs ),
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $earliest, tribe_get_option( 'earliest_date' ) );

		$new_earliest_time = $this->start - $this->seven_days;
		$new_earliest      = date( $this->format, $new_earliest_time );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => $new_earliest,
				'_EventEndDate'   => date( $this->format, $new_earliest_time + $this->two_hrs ),
			]
		] );

		$sut->rebuild_known_range();

		$this->assertEquals( $new_earliest, tribe_get_option( 'earliest_date' ) );
	}

	/**
	 * @test
	 * it should set latest date to end of new latest event
	 */
	public function it_should_set_latest_date_to_end_of_new_latest_event() {
		$latest_time = $this->start + $this->seven_days + $this->two_hrs;
		$latest      = date( $this->format, $latest_time );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start ),
				'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
			]
		] );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
				'_EventEndDate'   => $latest,
			]
		] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $latest, tribe_get_option( 'latest_date' ) );

		$new_latest_time_start = $latest_time + $this->seven_days;
		$new_latest            = date( $this->format, $new_latest_time_start + $this->two_hrs );
		$this->factory()->post->create( [
			'post_type'  => Main::POSTTYPE,
			'meta_input' => [
				'_EventStartDate' => date( $this->format, $new_latest_time_start ),
				'_EventEndDate'   => $new_latest,
			]
		] );

		$sut->rebuild_known_range();

		$this->assertEquals( $new_latest, tribe_get_option( 'latest_date' ) );
	}

	/**
	 * @test
	 * it should set earliest date markers
	 */
	public function it_should_set_earliest_date_markers() {
		$earliest_ids = $this->factory()->post->create_many( 3,
			[
				'post_type'  => Main::POSTTYPE,
				'meta_input' => [
					'_EventStartDate' => date( $this->format, $this->start ),
					'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
				]
			] );

		$this->factory()->post->create_many( 3,
			[
				'post_type'  => Main::POSTTYPE,
				'meta_input' => [
					'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
					'_EventEndDate'   => date( $this->format, $this->start + $this->seven_days + $this->two_hrs ),
				]
			] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $earliest_ids, tribe_get_option( 'earliest_date_markers' ) );
	}

	/**
	 * @test
	 * it should set latest date markers
	 */
	public function it_should_set_latest_date_markers() {
		$this->factory()->post->create_many( 3,
			[
				'post_type'  => Main::POSTTYPE,
				'meta_input' => [
					'_EventStartDate' => date( $this->format, $this->start ),
					'_EventEndDate'   => date( $this->format, $this->start + $this->two_hrs ),
				]
			] );

		$latest_ids = $this->factory()->post->create_many( 3,
			[
				'post_type'  => Main::POSTTYPE,
				'meta_input' => [
					'_EventStartDate' => date( $this->format, $this->start + $this->seven_days ),
					'_EventEndDate'   => date( $this->format, $this->start + $this->seven_days + $this->two_hrs ),
				]
			] );

		$sut = new Known_Range();
		$sut->rebuild_known_range();

		$this->assertEquals( $latest_ids, tribe_get_option( 'latest_date_markers' ) );
	}
}