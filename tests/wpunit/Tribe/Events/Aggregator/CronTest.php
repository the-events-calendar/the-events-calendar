<?php

namespace Tribe\Events\Aggregator;

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
			'post_mime_type' => 'ea/foo-bar'
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
			'post_mime_type' => 'ea/foo-bar'
		] );

		add_post_meta( $first, '_tribe_aggregator_origin', 'eventbrite' );

		$second = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar'
		] );

		add_post_meta( $second, '_tribe_aggregator_origin', 'meetup' );
		add_post_meta( $second, '_tribe_aggregator_allow_batch_push', '0' );

		$batch = $this->factory()->post->create( [
			'post_type'      => Records::$post_type,
			'post_status'    => Records::$status->pending,
			'post_mime_type' => 'ea/foo-bar'
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
			'post_mime_type' => 'ea/foo-bar'
		] );

		add_post_meta( $batch, '_tribe_aggregator_origin', 'meetup' );
		add_post_meta( $batch, '_tribe_aggregator_allow_batch_push', '1' );

		$cron = $this->make_instance();
		$cron->verify_fetching_from_service();

		$this->assertEquals( Records::$status->pending, get_post_status( $batch ) );
	}
}