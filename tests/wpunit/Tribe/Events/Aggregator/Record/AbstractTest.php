<?php

namespace Tribe\Events\Aggregator\Record;

include_once( codecept_data_dir( 'classes/Tribe__Events__Aggregator__Record__Scheduled_Test.php' ) );

use Prophecy\Argument;
use Tribe\Events\Tests\Factories\Aggregator\V1\Import_Record;
use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Aggregator__Record__Abstract as Base;
use Tribe__Events__Aggregator__Record__Activity as Activity;
use Tribe__Events__Aggregator__Record__Facebook as FB_Record;
use Tribe__Events__Aggregator__Record__Scheduled_Test as Record;
use Tribe__Events__Aggregator__Record__Url as Url_Import_Record;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Main as Main;

class AbstractTest extends Events_TestCase {

	function setUp() {
		parent::setUp();
		$this->factory()->import_record = new Import_Record();
	}

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
			'on_demand'   => 0,
			'every30mins' => .5 * HOUR_IN_SECONDS,
			'hourly'      => HOUR_IN_SECONDS,
			'daily'       => 1 * DAY_IN_SECONDS,
			'weekly'      => 7 * DAY_IN_SECONDS,
			'monthly'     => 30 * DAY_IN_SECONDS,
		];

		if ( ! array_key_exists( $frequency_id, $supported_frequencies ) ) {
			$frequencies = implode( ', ', $supported_frequencies );
			throw new \InvalidArgumentException( "Frequency id should be one among [{$frequencies}]" );
		}

		$modified = is_numeric($modified) ? $modified : strtotime( $modified );

		if ( 0 >= $modified ) {
			throw new \InvalidArgumentException( 'Modified should be a string parseable by the strtotime function' );
		}

		$post = $this->factory()->post->create_and_get( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->schedule,
			'post_date'      => date( 'Y-m-d H:i:s', $modified ),
			'post_mime_type' => 'ea/ical',
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
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-4 days' );
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

		$this->add_failed_children_to( $scheduled_record, '-4 days' );

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
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-4 days' );

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
		$scheduled_record = $this->make_scheduled_record_instance( 'weekly', '-1 day' );

		$this->add_successful_children_to( $scheduled_record, '-2 weeks' );
		$this->add_successful_children_to( $scheduled_record, '-9 days' );
		$last = $this->add_failed_children_to( $scheduled_record, '-4 days' );

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
			'post_type'      => Records::$post_type,
			'post_parent'    => $scheduled_record->post->ID,
			'post_status'    => Records::$status->failed,
			'post_date'      => date( 'Y-m-d H:i:s', $modified ),
			'post_mime_type' => $scheduled_record->post->post_mime_type,
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
			'post_type'      => Records::$post_type,
			'post_parent'    => $scheduled_record->post->ID,
			'post_status'    => Records::$status->success,
			'post_date'      => date( 'Y-m-d H:i:s', $modified ),
			'post_mime_type' => $scheduled_record->post->post_mime_type,
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

	/**
	 * Test import_organizer_image
	 */
	public function test_import_organizer_image() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$organizer_id = $this->factory()->organizer->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldBeCalled();

		$this->assertTrue( $sut->import_organizer_image( $organizer_id, $service_image->ID, $activity->reveal() ) );
	}

	/**
	 * Test import_organizer_image with non URL
	 */
	public function test_import_organizer_image_with_non_url() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$organizer_id = $this->factory()->organizer->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_organizer_image( $organizer_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test import_organizer_image with empty organizer
	 */
	public function test_import_organizer_image_with_empty_organizer() {
		$sut = new FB_Record();
		$organizer_id = '';
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_organizer_image( $organizer_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test import_organizer_image with not an organizer
	 */
	public function test_import_organizer_image_with_not_an_organizer() {
		$sut = new FB_Record();
		$organizer_id = $this->factory()->post->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_organizer_image( $organizer_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test import_venue_image
	 */
	public function test_import_venue_image() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$venue_id = $this->factory()->venue->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldBeCalled();

		$this->assertTrue( $sut->import_venue_image( $venue_id, $service_image->ID, $activity->reveal() ) );
	}

	/**
	 * Test import_venue_image with non URL
	 */
	public function test_import_venue_image_with_non_url() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$venue_id = $this->factory()->venue->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_venue_image( $venue_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test import_venue_image with empty venue
	 */
	public function test_import_venue_image_with_empty_venue() {
		$sut = new FB_Record();
		$venue_id = '';
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_venue_image( $venue_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test import_venue_image with not an venue
	 */
	public function test_import_venue_image_with_not_an_venue() {
		$sut = new FB_Record();
		$venue_id = $this->factory()->post->create();
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_venue_image( $venue_id, 'foo', $activity->reveal() ) );
	}

	/**
	 * Test venue image import can be blocked with filter
	 */
	public function test_venue_image_import_can_be_blocked_with_filter() {
		add_filter( 'tribe_aggregator_import_venue_image', '__return_false' );
		$sut           = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$venue_id      = $this->factory()->venue->create();
		$activity      = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_venue_image( $venue_id, $service_image->guid, $activity->reveal() ) );
	}

	/**
	 * Test organizer image import can be blocked with filter
	 */
	public function test_organizer_image_import_can_be_blocked_with_filter() {
		add_filter( 'tribe_aggregator_import_organizer_image', '__return_false' );
		$sut           = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$organizer_id  = $this->factory()->venue->create();
		$activity      = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_organizer_image( $organizer_id, $service_image->guid, $activity->reveal() ) );
	}
	/**
	 * Test import_event_image
	 */
	public function test_import_event_image() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$event = (array)$this->factory()->event->create_and_get();
		$event['image'] = $service_image->ID;
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldBeCalled();

		$this->assertTrue( $sut->import_event_image( $event, $activity->reveal() ) );
	}

	/**
	 * Test import_event_image with non URL
	 */
	public function test_import_event_image_with_non_url() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$event = (array)$this->factory()->event->create_and_get();
		$event['image'] = 'foo';
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_event_image( $event, $activity->reveal() ) );
	}

	/**
	 * Test import_event_image with empty event
	 */
	public function test_import_event_image_with_empty_event() {
		$sut = new FB_Record();
		$event = [];
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_event_image( $event,  $activity->reveal() ) );
	}

	/**
	 * Test import_event_image with not an event
	 */
	public function test_import_event_image_with_not_an_event() {
		$sut = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$event = (array)$this->factory()->post->create_and_get();
		$event['image'] = $service_image->guid;
		$activity = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', Argument::any() )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_event_image( $event, $activity->reveal() ) );
	}

	/**
	 * Test event image import can be blocked with filter
	 */
	public function test_event_image_import_can_be_blocked_with_filter() {
		add_filter( 'tribe_aggregator_import_event_image', '__return_false' );
		$sut           = new FB_Record();
		$service_image = $this->factory()->attachment->create_and_get( [ 'file' => codecept_data_dir( 'images/featured-image.jpg' ) ] );
		$event      = (array)$this->factory()->event->create_and_get();
		$event['image'] = $service_image->guid;
		$activity      = $this->prophesize( Activity::class );
		$activity->add( 'attachment', 'created', $service_image->ID )->shouldNotBeCalled();

		$this->assertFalse( $sut->import_event_image( $event, $activity->reveal() ) );
	}

	/**
	 * It should allow filtering the venue id when global ID does not provide a match
	 *
	 * @test
	 */
	public function should_allow_filtering_the_venue_id_when_global_id_does_not_provide_a_match() {
		$item = $this->factory()->import_record->create_and_get_event_record(
			'url',
			[
				'venue' =>
					[ 'venue' => 'Venue title from record' ]
			]
		);
		$venue_id = $this->factory()->venue->create();
		add_filter( 'tribe_aggregator_find_matching_venue', function ( $_ = null, $venue_data ) use ( $venue_id ) {
			$this->assertEquals( 'Venue title from record', $venue_data['Venue'] );

			return $venue_id;
		}, 10, 2 );
		$sut = new Url_Import_Record();

		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity       = $sut->insert_posts( [ $item ] );
		$created_events = $activity->get( \Tribe__Events__Main::POSTTYPE, 'created' );
		$updated_venues = $activity->get( \Tribe__Events__Venue::POSTTYPE, 'updated' );
		$this->assertCount( 1, $created_events );
		$this->assertCount( 1, $updated_venues );
		$this->assertEquals( $venue_id, get_post_meta( $created_events[0], '_EventVenueID', true ) );
	}

	/**
	 * It should allow filtering the organizer id when global ID does not provide a match
	 *
	 * @test
	 */
	public function should_allow_filtering_the_organizer_id_when_global_id_does_not_provide_a_match() {
		$item = $this->factory()->import_record->create_and_get_event_record( 'url', [ 'organizer_count' => 3, ] );
		$organizer_ids = $this->factory()->organizer->create_many(3);
		$i =0;
		add_filter( 'tribe_aggregator_find_matching_organizer', function ( $_ = null, $organizer_data ) use ( $organizer_ids, &$i ) {
			return $organizer_ids[ $i ++ ];
		}, 10, 2 );
		$sut = new Url_Import_Record();

		/** @var \Tribe__Events__Aggregator__Record__Activity $activity */
		$activity           = $sut->insert_posts( [ $item ] );
		$created_events     = $activity->get( \Tribe__Events__Main::POSTTYPE, 'created' );
		$updated_organizers = $activity->get( \Tribe__Events__Organizer::POSTTYPE, 'updated' );
		$this->assertCount( 1, $created_events );
		$this->assertCount( 3, $updated_organizers );
		$this->assertEquals( $organizer_ids, get_post_meta( $created_events[0], '_EventOrganizerID' ) );
	}
	
	/**
	 * It should reschedule a failed import again at half its frequency
	 *
	 * @test
	 */
	public function should_reschedule_a_failed_import_again_at_half_its_frequency() {
		$frequency        = 'weekly';
		$modified         = '-1 day';
		$child_failed_at = '-20 minutes';
		$second_child_failed_at = '-5 days';

		$scheduled_record = $this->make_scheduled_record_instance( $frequency, $modified );

		$failed = $this->add_failed_children_to( $scheduled_record, $child_failed_at  );

		$this->assertFalse( $scheduled_record->is_schedule_time() );

		wp_delete_post( $failed, true );

		$failed = $this->add_failed_children_to( $scheduled_record, $second_child_failed_at );

		$this->assertTrue( $scheduled_record->is_schedule_time() );
	}

	public function frequencies_and_expected_retry_times() {
		return [
			[ 'on_demand', false ],
			[ 'every30mins', false ],
			[ 'hourly', false ],
			[ 'daily', 6 * 3600 ],
			[ 'weekly', 24 * 3600 ],
			[ 'monthly', 24 * 3600 ],
		];
	}

	/**
	 * It should correctly return a record retry timestamp
	 *
	 * @test
	 *
	 * @dataProvider frequencies_and_expected_retry_times
	 */
	public function should_correctly_return_a_record_retry_timestamp( $frequency_id, $expected_interval ) {
		$modified_time = strtotime( '-1 hour' );
		$record        = $this->make_scheduled_record_instance( $frequency_id, $modified_time );

		if ( false !== $expected_interval ) {
			$this->assertEquals( $modified_time + $expected_interval, $record->get_retry_time() );
		} else {
			$this->assertFalse( $record->get_retry_time() );
		}
	}
}
