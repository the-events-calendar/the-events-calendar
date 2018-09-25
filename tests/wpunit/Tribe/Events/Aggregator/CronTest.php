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
			'post_mime_type' => 'ea/foo-bar'
		] );

		$this->assertEquals( Records::$status->schedule, get_post_status( $record_post ) );

		$cron = $this->make_instance();
		add_filter( 'tribe_aggregator_api', function ( $api ) {
			$api->key = 'foo-bar';

			return $api;
		} );
		$cron->verify_child_record_creation();

		$this->assertEmpty( get_post( $record_post ) );
	}
}