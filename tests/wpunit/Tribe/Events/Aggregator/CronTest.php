<?php

namespace Tribe\Events\Aggregator;

use Tribe\Events\Test\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe\Events\Test\Traits\Aggregator\AggregatorMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Prophecy\Argument;
use Tribe\Events\Test\Factories\Aggregator\V1\Service;
use Tribe\Events\Virtual\Tests\Traits\With_Uopz;
use Tribe__Events__Aggregator__Cron as Cron;
use Tribe__Events__Aggregator__Records as Records;

class CronTest extends Aggregator_TestCase {
	use RecordMaker;
	use AggregatorMaker;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Cron::class, $sut );
	}

	public function _before() {
		// By pass the is_active() EA call.
		add_filter( 'tribe_aggregator_api', function ( $api ) {
			$api->key = 'foo-bar';

			return $api;
		} );
	}

	/**
	 * @return Cron
	 */
	private function make_instance() {
		return new class extends Cron {
			public function __construct() {
				// no side-effects constructor
			}
		};
	}

	/**
	 * It should trash record posts not belonging to a supported origin
	 *
	 * @test
	 */
	public function should_trash_record_posts_not_belonging_to_a_supported_origin() {
		$record_post = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->schedule,
			'ping_status'    => 'schedule',
			'post_mime_type' => 'ea/foo-bar',
		] );

		$this->assertEquals( Records::$status->schedule, get_post_status( $record_post ) );

		$cron = $this->make_instance();
		$cron->verify_child_record_creation();

		$this->assertEmpty( get_post( $record_post ) );
	}

	/**
	 * should prevent to modify a template record if it fails to process
	 *
	 * @test
	 */
	public function should_prevent_to_modify_a_template_record_if_it_fails_to_process() {
		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		tribe_register( 'events-aggregator.main', $this->make_aggregator_instance() );

		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );
		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service
			->post_import(
				[
					'type'                      => 'schedule',
					'origin'                    => 'gcal',
					'source'                    => 'http://source-one.cal',
					'callback'                  => home_url(
						'/event-aggregator/insert/?key=' . urlencode( $import_id )
					),
					'resolve_geolocation'       => 1,
					'frequency'                 => 'every30mins',
					'allow_multiple_organizers' => 1,
				]
			)
			->willReturn(
				(object) [
					'message_code' => 'success:create-import',
					'data'         => (object) [
						'import_id' => 'import-created-123',
					],
					'status'       => 'created',
					'message'      => 'Created',
				]
			);
		// Import returns an error.
		$service->get_import( 'import-created-123', [] )->willReturn( new \WP_Error() );
		tribe_register( 'events-aggregator.service', $service->reveal() );

		/** @var Record $scheduled */
		$scheduled = $this->make_schedule_record(
			'birthday-cal',
			[
				'source'          => 'http://source-one.cal',
				'hash'            => $import_id,
				'next_batch_hash' => $next_import_id,
			]
		);

		$records = Records::instance();
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->schedule ] )->found_posts );
		$this->assertSame( 0, $records->query( [ 'post_status' => Records::$status->failed ] )->found_posts );

		$value = getenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE' );
		putenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE=true' );

		$cron = $this->make_instance();
		$cron->verify_child_record_creation();

		$this->assertinstanceOf( \WP_Post::class, $scheduled->post );
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$this->assertSame( 2, $records->query( [ 'post_status' => 'any' ] )->found_posts );
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->schedule ] )->found_posts );
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->failed ] )->found_posts );

		putenv( "TRIBE_DEBUG_OVERRIDE_SCHEDULE={$value}" );
		$this->restore_aggregator();
	}

	/**
	 * should process pending records with empty events
	 *
	 * @test
	 */
	 public function should_process_pending_records_with_empty_events() {
		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		tribe_register( 'events-aggregator.main', $this->make_aggregator_instance() );

		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );
		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service
			->post_import(
				[
					'type'                      => 'schedule',
					'origin'                    => 'gcal',
					'source'                    => 'http://source-one.cal',
					'callback'                  => home_url(
						'/event-aggregator/insert/?key=' . urlencode( $import_id )
					),
					'resolve_geolocation'       => 1,
					'frequency'                 => 'every30mins',
					'allow_multiple_organizers' => 1,
				]
			)
			->willReturn(
				(object) [
					'message_code' => 'success:create-import',
					'data'         => (object) [
						'import_id' => 'import-created-123',
					],
					'status'       => 'created',
					'message'      => 'Created',
				]
			);
		$service->get_import( 'import-created-123', [] )->willReturn(
			(object) [
				'status'       => 'success',
				'message_code' => 'success:create-import',
				'message'      => 'Import created',
				'data'         => [
					'import_id' => 'import-created-123',
					'events'    => [],
				],
			]
		);
		$service->get_service_message(
			'success:create-import',
			[
				'import_id' => 'import-created-123',
				'events'    => [],
			],
			'Import created'
		)->willReturn( 'Import created' );
		tribe_register( 'events-aggregator.service', $service->reveal() );

		/** @var Record $scheduled */
		$scheduled = $this->make_schedule_record(
			'birthday-cal',
			[
				'source'          => 'http://source-one.cal',
				'hash'            => $import_id,
				'next_batch_hash' => $next_import_id,
			]
		);

		$records = Records::instance();
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->schedule ] )->found_posts );
		$this->assertSame( 0, $records->query( [ 'post_status' => Records::$status->failed ] )->found_posts );

		$value = getenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE' );
		putenv( 'TRIBE_DEBUG_OVERRIDE_SCHEDULE=true' );

		$cron = $this->make_instance();
		$cron->verify_child_record_creation();

		$this->assertinstanceOf( \WP_Post::class, $scheduled->post );
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$this->assertSame( 2, $records->query( [ 'post_status' => 'any' ] )->found_posts );
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->schedule ] )->found_posts );
		$this->assertSame( 1, $records->query( [ 'post_status' => Records::$status->pending ] )->found_posts );

		putenv( "TRIBE_DEBUG_OVERRIDE_SCHEDULE={$value}" );
		$this->restore_aggregator();
	}

	/**
	 * It should process all pending records
	 *
	 * @test
	 */
	public function should_process_all_pending_records() {
		$first = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $first, '_tribe_aggregator_origin', 'eventbrite' );

		$second = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $second, '_tribe_aggregator_origin', 'meetup' );
		add_post_meta( $second, '_tribe_aggregator_allow_batch_push', '0' );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'meetup' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', '1' );

		$cron = $this->make_instance();
		$cron->verify_fetching_from_service();

		$this->assertEquals( Records::$status->failed, get_post_status( $first ) );
		$this->assertEquals( Records::$status->failed, get_post_status( $second ) );
		$this->assertEquals( Records::$status->pending, get_post_status( $batch ) );
	}

	/**
	 * Test batch records are not processed by cron task.
	 *
	 * @test
	 */
	public function it_should_bypass_batch_push_records() {
		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'meetup' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', '1' );

		$cron = $this->make_instance();
		$cron->verify_fetching_from_service();

		$this->assertEquals( Records::$status->pending, get_post_status( $batch ) );
	}

	/**
	 * should select batch pushing records to be processed
	 *
	 * @test
	 */
	public function should_select_batch_pushing_records_to_be_processed() {
		$backup  = tribe( 'events-aggregator.service' );
		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );

		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service
			->post(
				"import/{$import_id}/deliver/",
				[
					'body' => [
						'batch_size'       => 10,
						'batch_interval'   => 10,
						'tec_version'      => \Tribe__Events__Main::VERSION,
						'next_import_hash' => $next_import_id,
						'api'              => get_rest_url( get_current_blog_id(), 'tribe/event-aggregator/v1' ),
					],
				]
			)
			->willReturn( json_encode( [ 'success' => true ] ) );

		tribe_register( 'events-aggregator.service', $service->reveal() );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'ical' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', true );
		add_post_meta( $batch, '_tribe_aggregator_import_id', $import_id );
		add_post_meta( $batch, '_tribe_aggregator_next_batch_hash', $next_import_id );

		$cron = $this->make_instance();
		$cron->start_batch_pushing_records();

		$this->assertEquals( Records::$status->pending, get_post_status( $batch ) );

		// your tear down methods here
		tribe_register( 'events-aggregator.service', $backup );
	}


	/**
	 * should select  only a portion of events
	 *
	 * @test
	 */
	public function should_select_only_a_portion_of_events() {
		$backup  = tribe( 'events-aggregator.service' );
		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );

		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service
			->post(
				"import/{$import_id}/deliver/",
				[
					'body' => [
						'batch_size'       => 10,
						'batch_interval'   => 10,
						'tec_version'      => \Tribe__Events__Main::VERSION,
						'next_import_hash' => $next_import_id,
						'api'              => get_rest_url( get_current_blog_id(), 'tribe/event-aggregator/v1' ),
						'selected_events'  => [ 1, 2, 3 ],
					],
				]
			)
			->willReturn( json_encode( [ 'success' => true ] ) );

		tribe_register( 'events-aggregator.service', $service->reveal() );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'ical' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', true );
		add_post_meta( $batch, '_tribe_aggregator_import_id', $import_id );
		add_post_meta( $batch, '_tribe_aggregator_next_batch_hash', $next_import_id );
		add_post_meta( $batch, 'ids_to_import', [ 1, 2, 3 ] );

		$cron = $this->make_instance();
		$cron->start_batch_pushing_records();

		$this->assertEquals( Records::$status->pending, get_post_status( $batch ) );

		// your tear down methods here
		tribe_register( 'events-aggregator.service', $backup );
	}

	/**
	 * should mark batch pushing record if is over limit
	 *
	 * @test
	 */
	public function should_mark_batch_pushing_record_if_is_over_limit() {
		$backup  = tribe( 'events-aggregator.service' );
		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );

		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( true );

		tribe_register( 'events-aggregator.service', $service->reveal() );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'ical' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', true );
		add_post_meta( $batch, '_tribe_aggregator_import_id', $import_id );
		add_post_meta( $batch, '_tribe_aggregator_next_batch_hash', $next_import_id );

		$cron = $this->make_instance();
		$cron->start_batch_pushing_records();

		$this->assertEquals( Records::$status->failed, get_post_status( $batch ) );
		$this->assertEquals( 'error:usage-limit-exceeded', get_post_meta( $batch, '_tribe_aggregator_last_import_status', true ) );

		// your tear down methods here
		tribe_register( 'events-aggregator.service', $backup );
	}

	/**
	 * should mark a record as failure if the ea service returns an error
	 *
	 * @test
	 */
	public function should_mark_a_record_as_failure_if_the_ea_service_returns_an_error() {
		$backup  = tribe( 'events-aggregator.service' );
		$service = $this->prophesize( \Tribe__Events__Aggregator__Service::class );

		$import_id      = uniqid( 'import_id', true );
		$next_import_id = uniqid( 'next_import_id', true );

		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service
			->post(
				"import/{$import_id}/deliver/",
				[
					'body' => [
						'batch_size'       => 10,
						'batch_interval'   => 10,
						'tec_version'      => \Tribe__Events__Main::VERSION,
						'next_import_hash' => $next_import_id,
						'api'              => get_rest_url( get_current_blog_id(), 'tribe/event-aggregator/v1' ),
					],
				]
			)
			->willReturn( new \WP_Error());

		tribe_register( 'events-aggregator.service', $service->reveal() );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar',
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'ical' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', true );
		add_post_meta( $batch, '_tribe_aggregator_import_id', $import_id );
		add_post_meta( $batch, '_tribe_aggregator_next_batch_hash', $next_import_id );

		$cron = $this->make_instance();
		$cron->start_batch_pushing_records();

		$this->assertEquals( Records::$status->failed, get_post_status( $batch ) );

		// your tear down methods here
		tribe_register( 'events-aggregator.service', $backup );
	}

	public function test_purging_with_direct_queries_if_off_by_default():void{
		// Create an expired record.
		$record = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->success,
			'post_mime_type' => 'ea/url',
			'post_date'      => '2018-01-01 00:00:00',
		] );
		// Listen in on the pre_delete_post filter to check if the post is deleted.
		$wp_post_delete_calls = 0;
		add_action( 'deleted_post', function ( $post_id ) use ( &$wp_post_delete_calls, $record ) {
			$wp_post_delete_calls++;
			$this->assertEquals( $record, $post_id );
		} );

		$cron = $this->make_instance();
		$cron->purge_expired_records();

		$this->assertEquals( 1, $wp_post_delete_calls );
	}

	public function test_purging_with_direct_queries_will_delete_the_correct_records(): void {
		// Create 3 expired records.
		$expired_records = static::factory()->post->create_many( 3, [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->success,
			'post_mime_type' => 'ea/url',
			'post_date'      => '2018-01-01 00:00:00',
		] );
		// For each record add some meta and some comments.
		// Comments are used by EA to track errors, here we add them just to make sure they are deleted with the post.
		foreach ( $expired_records as $expired_record ) {
			add_post_meta( $expired_record, 'foo', 'bar' );
			static::factory()->comment->create_many( 3, [
				'comment_post_ID' => $expired_record,
			] );
		}
		// Create 3 non expired records.
		$non_expired_records = static::factory()->post->create_many( 3, [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->success,
			'post_mime_type' => 'ea/url',
			'post_date'      => (new \DateTime('now'))->format('Y-m-d H:i:s'),
		] );
		// For each record add some meta and some comments.
		// Comments are used by EA to track errors, here we add them just to make sure they are not deleted.
		foreach ( $non_expired_records as $non_expired_record ) {
			add_post_meta( $non_expired_record, 'foo', 'bar' );
			static::factory()->comment->create_many( 3, [
				'comment_post_ID' => $non_expired_record,
			] );
		}
		// Filter the `tec_event_aggregator_direct_record_deletion` filter to make sure the direct deletion is used.
		add_filter( 'tec_event_aggregator_direct_record_deletion', '__return_true' );
		// Listen in on the `post_deleted` filter to make sure WordPress core functions are not used.
		$wp_post_delete_calls = 0;
		add_action( 'deleted_post', function ( $post_id ) use ( &$wp_post_delete_calls, $expired_records ) {
			$wp_post_delete_calls ++;
			$this->assertContains( $post_id, $expired_records );
		} );

		$cron = $this->make_instance();
		$cron->purge_expired_records();

		$this->assertEmpty( $wp_post_delete_calls );
		// The expired posts should be gone, with them the meta and comments, caches included.
		foreach ( $expired_records as $expired_record ) {
			$this->assertEmpty( get_post_meta( $expired_record ) );
			$this->assertEmpty( get_comments( [ 'post_id' => $expired_record ] ) );
			$this->assertEmpty( get_post( $expired_record ) );
		}
		// The non expired posts should be still there, with them the meta and comments, caches included.
		foreach ( $non_expired_records as $non_expired_record ) {
			$this->assertNotEmpty( get_post_meta( $non_expired_record ) );
			$this->assertNotEmpty( get_comments( [ 'post_id' => $non_expired_record ] ) );
			$this->assertInstanceOf( \WP_Post::class, get_post( $non_expired_record ) );
		}
	}

	public function purge_expired_records_max_allowed_packet_provider(): array {
		return [
			// @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
			'minimum'     => [ 1024, 24, ],
			'maximum'     => [ 1073741824, 50000 ],
			'default'     => [ 67108864, 50000 ],
			'51k'         => [ 51000, 50000 ],
			'23k'         => [ 23000, 22000 ],
			'empty value' => [ '', 100 ],
		];
	}

	/**
	 * @dataProvider purge_expired_records_max_allowed_packet_provider
	 */
	public function test_batch_size_used_for_direct_delete_is_related_to_mysql_max_allowed_packet( $max_allowed_packet, int $expected_batch_size ): void {
		// Filter `query` to return a fixed value for the `max_allowed_packet` variable.
		add_filter( 'query', static function ( $query ) use ( $max_allowed_packet ) {
			if ( false !== strpos( $query, 'max_allowed_packet' ) ) {
				return 'SELECT ' . ( $max_allowed_packet ?: 'NULL' );
			}

			return $query;
		} );
		// Listen in on the `tec_event_aggregator_direct_record_deletion_batch_size` filter to catch the batch size.
		$batch_size = 0;
		add_filter( 'tec_event_aggregator_direct_record_deletion_batch_size', function ( $size ) use ( &$batch_size ) {
			$batch_size = $size;

			return $size;
		} );
		// Filter the `tec_event_aggregator_direct_record_deletion` filter to make sure the direct deletion is used.
		add_filter( 'tec_event_aggregator_direct_record_deletion', '__return_true' );

		$cron = $this->make_instance();
		$cron->purge_expired_records();

		$this->assertEquals( $expected_batch_size, $batch_size );
	}
}
