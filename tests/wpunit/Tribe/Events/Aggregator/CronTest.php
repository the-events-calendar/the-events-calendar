<?php

namespace Tribe\Events\Aggregator;

use Prophecy\Argument;
use Tribe\Events\Test\Factories\Aggregator\V1\Service;
use Tribe__Events__Aggregator__Cron as Cron;
use Tribe__Events__Aggregator__Records as Records;

class CronTest extends \Codeception\TestCase\WPTestCase {
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
}