<?php

namespace Tribe\Events\Aggregator\Record;

include_once( codecept_data_dir( 'classes/Tribe__Events__Aggregator__Record__Scheduled_Test.php' ) );

use Tribe__Events__Aggregator__Record__Abstract as Base;
use Tribe__Events__Aggregator__Record__Scheduled_Test as Record;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Main as Main;

class AbstractTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Builds a simulation of a scheduled record.
	 *
	 * @param string $frequency_id  A supported scheduled frequency string among ("on_demand", "daily", "weekly", "monthly")
	 * @param string $modified      A `strtotime` compatible string to indicate when the scheduled record was last modified
	 * @param int    $schedule_day  The day of the week the import should happen at; defaults to 1 (Monday)
	 * @param string $schedule_time The time of the day the import should happen at in 'H:i:s' format; defaults to 9am
	 *
	 * @return \Tribe__Events__Aggregator__Record__Scheduled_Test
	 */
	private function make_scheduled_record_instance( $frequency_id = 'weekly', $modified = 'now', $schedule_day = 1, $schedule_time = '09:00:00' ) {
		$supported_frequencies = [
			'on_demand' => 0,
			'daily'     => 1 * DAY_IN_SECONDS,
			'weekly'    => 7 * DAY_IN_SECONDS,
			'monthly'   => 30 * DAY_IN_SECONDS,
		];

		if ( ! array_key_exists( $frequency_id, $supported_frequencies ) ) {
			$frequencies = implode( ', ', $supported_frequencies );
			throw new \InvalidArgumentException( "Frequency id should be one among [{$frequencies}]" );
		}

		$modified = strtotime( $modified );

		if ( 0 >= $modified ) {
			throw new \InvalidArgumentException( 'Modified should be a string parseable by the strtotime function' );
		}

		$post = $this->factory()->post->create_and_get( [
			'post_type'   => Records::$post_type,
			'post_status' => Records::$status->schedule,
			'post_date'   => date( 'Y-m-d H:i:s', $modified ),
		] );

		$record = new Record();
		$record->set_post( $post );
		$record->frequency             = (object) [
			'id'       => $frequency_id,
			'interval' => $supported_frequencies[ $frequency_id ],
		];
		$record->meta['schedule_day']  = $schedule_day;
		$record->meta['schedule_time'] = $schedule_time;
		$record->meta['post_status']   = 'publish';
		$record->meta['origin']        = 'ical';

		return $record;
	}

	/**
	 * It should mark a scheduled record that failed on last run as in schedule time
	 *
	 * @test
	 */
	public function should_mark_a_scheduled_record_that_failed_on_last_run_as_in_schedule_time() {
		$scheduled_record                             = $this->make_scheduled_record_instance( 'weekly', '-1 hour' );
		$scheduled_record->meta['last_import_status'] = 'success:hurray';

		$this->assertFalse( $scheduled_record->is_schedule_time() );

		$scheduled_record->meta['last_import_status'] = 'error:something-happened';

		$this->assertTrue( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should not mark a failed record on demand as in schedule time
	 *
	 * @test
	 */
	public function should_not_mark_a_failed_record_on_demand_as_in_schedule_time() {
		$scheduled_record                             = $this->make_scheduled_record_instance( 'on_demand', '-1 hour' );
		$scheduled_record->meta['last_import_status'] = 'success:hurray';

		$this->assertFalse( $scheduled_record->is_schedule_time() );

		$scheduled_record->meta['last_import_status'] = 'error:something-happened';

		$this->assertFalse( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should look up children records if last_import_status meta is not set to find last imoprt status
	 *
	 * @test
	 */
	public function should_look_up_children_records_if_last_import_status_meta_is_not_set_to_find_last_imoprt_status() {
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-1 hour' );

		$this->assertFalse( $scheduled_record->is_schedule_time() );

		$this->add_failed_children_to( $scheduled_record, '-1 hour' );

		$this->assertTrue( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should not mark scheduled record with successful last children and no last_import_status as in schedule
	 *
	 * @test
	 */
	public function should_not_mark_scheduled_record_with_successful_last_children_and_no_last_import_status_as_in_schedule() {
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-1 hour' );

		$this->assertFalse( $scheduled_record->is_schedule_time() );

		$this->add_successful_children_to( $scheduled_record, '-1 hour' );

		$this->assertFalse( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should mark a scheduled import that has no children and no last import status as in schedule if in schedule
	 *
	 * @test
	 */
	public function should_mark_a_scheduled_import_that_has_no_children_and_no_last_import_status_as_in_schedule_if_in_schedule() {
		$schedule_day     = date( 'N', strtotime( 'today' ) );
		$schedule_time    = date( 'H:i:s', time() - 10 );
		$scheduled_record = $this->make_scheduled_record_instance( 'daily', '-1 week', $schedule_day, $schedule_time );

		$this->assertTrue( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should use last_import_status over last children post status to determine status
	 *
	 * @test
	 */
	public function should_use_last_import_status_over_last_children_post_status_to_determine_status() {
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-1 hour' );

		$scheduled_record->meta['last_import_status'] = 'error::something-happened';

		$this->add_successful_children_to( $scheduled_record );

		$this->assertTrue( $scheduled_record->is_schedule_time() );

		$scheduled_record->meta['last_import_status'] = 'success::hurray';

		$this->add_failed_children_to( $scheduled_record );

		$this->assertFalse( $scheduled_record->is_schedule_time() );
	}

	/**
	 * It should use the most recent children import to determine the status if last_import_status is not set
	 *
	 * @test
	 */
	public function should_use_the_most_recent_children_import_to_determine_the_status_if_last_import_status_is_not_set() {
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-1 day hour' );

		$this->add_successful_children_to( $scheduled_record );
		$this->add_successful_children_to( $scheduled_record );
		$last = $this->add_failed_children_to( $scheduled_record );

		$this->assertTrue( $scheduled_record->is_schedule_time() );

		wp_delete_post( $last, true );

		$this->assertFalse( $scheduled_record->is_schedule_time() );
	}

	/**
	 * Attaches a failed children import record to the specified scheduled record.
	 *
	 * @param Base   $scheduled_record
	 * @param string $modified
	 *
	 * @return int The children record post ID.
	 */
	protected function add_failed_children_to( $scheduled_record, $modified = 'now' ) {
		$modified = strtotime( $modified );

		if ( 0 >= $modified ) {
			throw new \InvalidArgumentException( 'Modified should be a string parseable by the strtotime function' );
		}

		return $this->factory()->post->create( [
			'post_type'   => Records::$post_type,
			'post_parent' => $scheduled_record->post->id,
			'post_status' => Records::$status->failed,
			'post_date'   => date( 'Y-m-d H:i:s', $modified ),
		] );
	}

	/**
	 * Attaches a successful children import record to the specified scheduled record.
	 *
	 * @param Base   $scheduled_record
	 * @param string $modified
	 *
	 * @return int The children record post ID.
	 */
	protected function add_successful_children_to( Base $scheduled_record, $modified = 'now' ) {
		$modified = strtotime( $modified );

		if ( 0 >= $modified ) {
			throw new \InvalidArgumentException( 'Modified should be a string parseable by the strtotime function' );
		}

		return $this->factory()->post->create( [
			'post_type'   => Records::$post_type,
			'post_parent' => $scheduled_record->post->id,
			'post_status' => Records::$status->success,
			'post_date'   => date( 'Y-m-d H:i:s', $modified ),
		] );
	}

	/**
	 * It should correctly insert posts
	 *
	 * @test
	 */
	public function should_correctly_insert_posts() {
		$this->markTestSkipped( 'This test runs for a long time and should be run alone and on purpose' );

		$file                  = codecept_data_dir( 'ea-responses/ea-huge-feed-response-01.json' );
		$response              = json_decode( file_get_contents( $file ) );
		$response_events_count = count( $response->data->events );

		if ( $response_events_count < 1000 ) {
			throw new \RuntimeException( 'The number of events in the file should be 1000 or more' );
		}

		$to_insert = $response->data->events;

		$this->assertEmpty( 0, get_posts( [ 'post_type' => Main::POSTTYPE ] ) );

		$sut = $this->make_scheduled_record_instance();

		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $sut->insert_posts( $to_insert );

		$this->assertCount( count( $to_insert ),
			get_posts( [ 'post_type' => Main::POSTTYPE, 'post_status' => $sut->meta['post_status'], 'posts_per_page' => - 1 ] ) );
		$this->assertEquals( count( $to_insert ), $activity->count( Main::POSTTYPE,'created' ) );
		$this->assertEquals( 0, $activity->count( Main::POSTTYPE,'updated' ) );
		$this->assertEquals( 0, $activity->count( Main::POSTTYPE,'skipped' ) );
	}
}