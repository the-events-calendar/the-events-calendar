<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Models\Post_Types\Event as Event_Post_Type;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Cache_Listener as Cache_Listener;
use WP_Post;

/**
 * Ported from the Events Calendar Pro suite together with the class; the rule-based
 * fixture is replaced by a dates-only Event.
 */
class Provisional_PostTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;

	public function hydration_states(): array {
		return [
			'active / success'   => [ false, false ],
			'deferred / success' => [ true, false ],
			'active / failure'   => [ false, true ],
			'deferred / failure' => [ true, true ],
		];
	}

	/**
	 * @test
	 * @dataProvider hydration_states
	 */
	public function should_restore_query_ownership_after_nested_hydration( bool $deferred, bool $fail ): void {
		$queries = new \TEC\Events\Custom_Tables\V1\WP_Query\Provisional\Provider( tribe() );
		$queries->noop( $deferred );
		$cache = $this->createMock( Provisional_Post_Cache::class );
		$cache->method( 'get_base' )->willReturn( 10000000 );
		$subject = new Provisional_Post( $cache, $queries, new \Tribe__Cache() );
		$depth   = 0;
		$cache->expects( $this->exactly( 2 ) )->method( 'hydrate_caches' )->willReturnCallback(
			function () use ( $subject, $queries, $fail, &$depth ): void {
				$this->assertTrue( $queries->is_noop() );
				if ( 0 === $depth++ ) {
					$subject->hydrate_caches( [ 10000002 ] );
					$this->assertTrue( $queries->is_noop(), 'Nested hydration must keep the outer call suppressed.' );
				} elseif ( $fail ) {
					throw new \RuntimeException( 'Hydration failed' );
				}
			}
		);

		try {
			$this->assertTrue( $subject->hydrate_caches( [ 10000001 ] ) );
			$this->assertFalse( $fail, 'The injected failure must propagate.' );
		} catch ( \RuntimeException $exception ) {
			$this->assertTrue( $fail );
			$this->assertSame( 'Hydration failed', $exception->getMessage() );
		}
		$this->assertSame( $deferred, $queries->is_noop() );
	}

	private function given_a_single_event(): WP_Post {
		$event = tribe_events()->set_args(
			[
				'title'      => 'Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-01 08:00:00',
				'end_date'   => '2050-01-01 17:00:00',
				'timezone'   => 'America/New_York',
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $event );

		return $event;
	}

	/**
	 * It should not cache whole Occurrence object in object caching context
	 *
	 * @test
	 */
	public function should_not_cache_whole_occurrence_object_in_object_caching_context() {
		$event      = $this->given_a_single_event();
		$occurrence = Occurrence::find_by_post_id( $event->ID );

		$occurrence_post = tribe_get_event( $occurrence->provisional_id );

		$this->assertInstanceOf(
			Occurrence::class,
			$occurrence_post->_tec_occurrence,
			'A reference to the Occurrence object should decorate the provisional post.'
		);
		$this->assertEquals(
			$occurrence->occurrence_id,
			$occurrence_post->_tec_occurrence_id,
			'The Occurrence occurrence_id should decorate the provisional post.'
		);

		// Access a property of the object that will trigger caching in the object cache.
		$event_pt = Event_Post_Type::from_post( $occurrence_post->ID );
		$event_pt->commit_to_cache( 'raw' );
		$cached = tribe_cache()->get( $event_pt->get_properties_cache_key( 'raw' ), Cache_Listener::TRIGGER_SAVE_POST );

		$this->assertNotFalse( $cached );
		$this->assertArrayNotHasKey( '_tec_occurrence_id', $cached, 'The Occurrence occurrence_id should not be cached with the decoration properties.' );
		$this->assertArrayNotHasKey( '_tec_occurrence', $cached, 'The Occurrence object should not be cached with the decoration properties.' );

		$cached_post = (array) wp_cache_get( $occurrence_post->ID, 'posts' );
		$this->assertArrayHasKey( '_tec_occurrence_id', $cached_post, 'The Occurrence occurrence_id should be cached along with the post object.' );
		$this->assertArrayNotHasKey( '_tec_occurrence', $cached_post, 'The Occurrence object should not be cached along with the post object.' );
	}

	/**
	 * @test
	 */
	public function should_correctly_filter_existing_occurrence_provisional_postmeta_query() {
		global $wpdb;
		$event            = $this->given_a_single_event();
		$first_occurrence = Occurrence::find_by_post_id( $event->ID );
		$this->assertInstanceOf( Occurrence::class, $first_occurrence, 'The Occurrence should exist in the database to begin with.' );
		$provisional_id = $first_occurrence->provisional_id;

		// A query like the one `get_metadata` would run.
		$sql = "SELECT meta_key, meta_value, meta_id, post_id
FROM {$wpdb->postmeta} WHERE post_id = $provisional_id
                                                         ORDER BY meta_key,meta_id";

		$filtered = tribe( Provisional_Post_Meta::class )->hydrate_provisional_postmeta_query( $sql );

		$expected_sql = "SELECT meta_key, meta_value, meta_id, post_id FROM {$wpdb->postmeta} WHERE post_id = {$event->ID} ORDER BY meta_key,meta_id";
		$this->assertEquals( $expected_sql, $filtered );

		// Force the Model class to return `false` on next fetch, simulating a DB error.
		$this->set_class_fn_return( Builder::class, 'find', false );
		wp_cache_flush();

		$filtered = tribe( Provisional_Post::class )->hydrate_provisional_post_query( $sql );

		$this->assertEquals( $sql, $filtered );
	}

	/**
	 * It should correctly filter existing Occurrence provisional post query
	 *
	 * @test
	 */
	public function should_correctly_filter_existing_occurrence_provisional_post_query() {
		global $wpdb;
		$event            = $this->given_a_single_event();
		$first_occurrence = Occurrence::find_by_post_id( $event->ID );
		$this->assertInstanceOf( Occurrence::class, $first_occurrence, 'The Occurrence should exist in the database to begin with.' );
		$provisional_id = $first_occurrence->provisional_id;

		// A query like the one `WP_Post::get_instance` would run.
		$sql = "SELECT * FROM $wpdb->posts WHERE ID = $provisional_id LIMIT 1";

		$filtered = tribe( Provisional_Post::class )->hydrate_provisional_post_query( $sql );

		$this->assertStringStartsWith( "SELECT $provisional_id as ID, {$wpdb->posts}.post_author, {$wpdb->posts}.post_date,", $filtered );
		$this->assertStringContainsString( "{$wpdb->posts}.post_title", $filtered );
		$this->assertStringEndsWith( "FROM {$wpdb->posts} WHERE ID = {$event->ID} LIMIT 1", $filtered );
		$this->assertStringNotContainsString( "{$wpdb->posts}.ID", $filtered, 'The real post ID column is replaced by the provisional one.' );

		// Force the Model class to return `false` on next fetch, simulating a DB error.
		$this->set_class_fn_return( Builder::class, 'find', false );
		wp_cache_flush();

		$filtered = tribe( Provisional_Post::class )->hydrate_provisional_post_query( $sql );

		$this->assertEquals( $sql, $filtered );
	}

	/**
	 * It should correctly handle compromised occurrence row cache
	 *
	 * @test
	 */
	public function should_correctly_handle_compromised_occurrence_row_cache() {
		$recurring_event = $this->given_a_multi_date_event(
			[
				[ 'start' => '2050-01-07 09:00:00', 'end' => '2050-01-07 10:00:00' ],
				[ 'start' => '2050-01-09 09:00:00', 'end' => '2050-01-09 10:00:00' ],
			]
		);
		$this->assertEquals( 3, Occurrence::where( 'post_id', $recurring_event->ID )->count() );

		foreach ( Occurrence::where( 'post_id', $recurring_event->ID )->all() as $occurrence ) {
			$this->assertEquals(
				$recurring_event->ID,
				tribe( Provisional_Post::class )->get_occurrence_post_id( $occurrence->provisional_id )
			);

			// Poison the Occurrence cache and fetch it again.
			tribe_cache()[ 'occurrence_row_' . $occurrence->occurrence_id ] = 'poisoned';
			$this->assertEquals(
				$recurring_event->ID,
				tribe( Provisional_Post::class )->get_occurrence_post_id( $occurrence->provisional_id )
			);
		}
	}

	/**
	 * It should tell provisional ids from real post ids and normalize them
	 *
	 * @test
	 */
	public function should_tell_provisional_ids_from_real_post_ids_and_normalize_them() {
		$event            = $this->given_a_single_event();
		$occurrence       = Occurrence::find_by_post_id( $event->ID );
		$provisional_post = tribe( Provisional_Post::class );

		$this->assertFalse( $provisional_post->is_provisional_post_id( $event->ID ) );
		$this->assertFalse( $provisional_post->is_provisional_post_id( 0 ) );
		$this->assertFalse( $provisional_post->is_provisional_post_id( 'nope' ) );
		$this->assertTrue( $provisional_post->is_provisional_post_id( $occurrence->provisional_id ) );

		$this->assertEquals( $occurrence->occurrence_id, $provisional_post->normalize_provisional_post_id( (int) $occurrence->provisional_id ) );
		$this->assertEquals( $event->ID, $provisional_post->normalize_provisional_post_id( $event->ID ) );
		$this->assertEquals( $event->ID, Occurrence::normalize_id( $occurrence->provisional_id ) );
	}

	/**
	 * Data provider for the SQL query parsing tests.
	 *
	 * @return array[] Test cases with query strings and expected results.
	 */
	public function query_parsing_data_provider(): array {
		global $wpdb;
		$posts_table = $wpdb->posts ?? 'test_posts';

		return [
			'basic_with_limit'              => [ "SELECT * FROM {$posts_table} WHERE ID = 123 LIMIT 1", 123 ],
			'basic_without_limit'           => [ "SELECT * FROM {$posts_table} WHERE ID = 456", 456 ],
			'with_whitespace'               => [ "SELECT * FROM {$posts_table} WHERE ID = 789 LIMIT 1   ", 789 ],
			'with_tabs_and_spaces'          => [ "SELECT * FROM {$posts_table} WHERE ID = 101112 LIMIT 1\t  \n", 101112 ],
			'with_single_line_comment'      => [ "SELECT * FROM {$posts_table} WHERE ID = 131415 LIMIT 1 -- This is a comment", false ],
			'with_multiline_comment'        => [ "SELECT * FROM {$posts_table} WHERE ID = 161718 LIMIT 1 /* Simple comment */", 161718 ],
			'with_comment_containing_limit' => [ "SELECT * FROM {$posts_table} WHERE ID = 192021 /* This comment mentions LIMIT */", 192021 ],
			'with_comment_before_limit'     => [ "SELECT * FROM {$posts_table} WHERE ID = 252627 /* comment */ LIMIT 1", false ],
			'wrong_table'                   => [ 'SELECT * FROM wp_other_table WHERE ID = 123 LIMIT 1', false ],
			'select_specific_fields'        => [ "SELECT ID, post_title FROM {$posts_table} WHERE ID = 123 LIMIT 1", false ],
			'multiple_conditions'           => [ "SELECT * FROM {$posts_table} WHERE ID = 123 AND post_status = 'publish' LIMIT 1", false ],
			'limit_greater_than_1'          => [ "SELECT * FROM {$posts_table} WHERE ID = 123 LIMIT 5", false ],
			'id_in_string'                  => [ "SELECT * FROM {$posts_table} WHERE post_title = 'ID = 123' LIMIT 1", false ],
			'malformed_query'               => [ "ELECT * FROM {$posts_table} WHERE ID = 123 LIMIT 1", false ],
			'case_sensitivity'              => [ "select * from {$posts_table} where id = 123 limit 1", false ],
			'large_id'                      => [ "SELECT * FROM {$posts_table} WHERE ID = 2147483647 LIMIT 1", 2147483647 ],
			'zero_id'                       => [ "SELECT * FROM {$posts_table} WHERE ID = 0 LIMIT 1", 0 ],
			'negative_id'                   => [ "SELECT * FROM {$posts_table} WHERE ID = -1 LIMIT 1", false ],
			'empty_query'                   => [ '', false ],
			'whitespace_only'               => [ '   ', false ],
		];
	}

	/**
	 * Test the SQL query parsing regex with comprehensive data sets.
	 *
	 * @test
	 * @dataProvider query_parsing_data_provider
	 */
	public function should_correctly_parse_sql_queries_for_post_ids( string $query, $expected_result ) {
		$provisional_post = tribe( Provisional_Post::class );
		$reflection       = new \ReflectionClass( $provisional_post );
		$method           = $reflection->getMethod( 'parse_query_post_id' );
		$method->setAccessible( true );

		$result = $method->invoke( $provisional_post, $query );

		if ( false === $expected_result ) {
			$this->assertFalse( $result );
		} else {
			$this->assertEquals( $expected_result, $result );
		}
	}

	/**
	 * Test the hydrate_provisional_post_query method with various SQL patterns.
	 *
	 * @test
	 */
	public function should_only_process_valid_provisional_post_queries() {
		global $wpdb;
		$event          = $this->given_a_single_event();
		$occurrence     = Occurrence::find_by_post_id( $event->ID );
		$provisional_id = $occurrence->provisional_id;

		$provisional_post = tribe( Provisional_Post::class );

		// A valid provisional post query is processed.
		$valid_query     = "SELECT * FROM {$wpdb->posts} WHERE ID = {$provisional_id} LIMIT 1";
		$processed_query = $provisional_post->hydrate_provisional_post_query( $valid_query );
		$this->assertNotEquals( $valid_query, $processed_query, 'Valid provisional post query should be processed' );
		$this->assertStringContainsString( (string) $provisional_id, $processed_query, 'Processed query should contain provisional ID' );

		// An unexpected query format is returned unchanged.
		$invalid_query = "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID = {$provisional_id} LIMIT 1";
		$this->assertEquals( $invalid_query, $provisional_post->hydrate_provisional_post_query( $invalid_query ) );

		// A real post ID is returned unchanged.
		$regular_post_query = "SELECT * FROM {$wpdb->posts} WHERE ID = {$event->ID} LIMIT 1";
		$this->assertEquals( $regular_post_query, $provisional_post->hydrate_provisional_post_query( $regular_post_query ) );

		// A confusing comment does not prevent the processing.
		$comment_query = "SELECT * FROM {$wpdb->posts} WHERE ID = {$provisional_id} LIMIT 1 /* Query from WP core LIMIT processing */";
		$this->assertNotEquals( $comment_query, $provisional_post->hydrate_provisional_post_query( $comment_query ) );
	}
}
