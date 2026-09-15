<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

/**
 * Ported from the Events Calendar Pro suite together with the class.
 */
class Provisional_Post_CacheTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * Should fetch a valid base.
	 *
	 * @test
	 */
	public function should_get_base() {
		$base = tribe( Provisional_Post_Cache::class )->get_base();
		$this->assertIsNumeric( $base );
		$this->assertGreaterThan( 0, $base );
	}

	/**
	 * Should generate cache.
	 *
	 * @test
	 */
	public function should_hydrate() {
		$event = tribe_events()->set_args(
			[
				'title'      => 'Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-01 08:00:00',
				'end_date'   => '2050-01-01 17:00:00',
				'timezone'   => 'America/New_York',
			]
		)->create();

		$occurrence = Occurrence::find_by_post_id( $event->ID );
		$this->assertInstanceOf( Occurrence::class, $occurrence );
		$cache_key = "event_occurrence_{$occurrence->occurrence_id}";

		$provisional_cache = tribe( Provisional_Post_Cache::class );
		$provisional_cache->flush_all();
		$provisional_cache->flush_occurrences( $occurrence->post_id );
		$this->assertFalse( $provisional_cache->already_cached( $occurrence->post_id ), 'Before cache hydration occurrence should NOT be cached yet.' );

		$this->assertArrayNotHasKey( $cache_key, tribe_cache(), 'Ensures that occurrence does not exist in the Common cache.' );

		$provisional_cache->hydrate_caches( [ $occurrence->provisional_id ] );
		$this->assertTrue( $provisional_cache->already_cached( $occurrence->post_id ), 'After cache hydration occurrence should be cached already.' );

		$this->assertArrayHasKey( $cache_key, tribe_cache(), 'Ensures that occurrence exists in the Common cache.' );
	}
}
