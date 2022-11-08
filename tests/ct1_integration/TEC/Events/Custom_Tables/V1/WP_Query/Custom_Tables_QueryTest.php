<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe__Events__Main as TEC;

class Custom_Tables_QueryTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * @before
	 */
	public function set_user_to_admin(): void {
		// Set the user to admin to be able to add tax terms.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	private function given_events_and_categories(): array {
		$term_name_to_id_map = [
			'cat1' => static::factory()->term->create( [
					'taxonomy' => TEC::TAXONOMY,
					'name'     => 'cat1',
					'slug'     => 'cat1',
				]
			),
			'cat2' => static::factory()->term->create( [
					'taxonomy' => TEC::TAXONOMY,
					'name'     => 'cat2',
					'slug'     => 'cat2',
				]
			),
		];

		$create_cat_event = static function ( array $term_ids, string $title ): int {
			return tribe_events()->set_args( [
				'post_title'  => $title,
				'post_status' => 'publish',
				'start_date'  => 'tomorrow 10 am',
				'duration'    => 2 * HOUR_IN_SECONDS,
				'category'    => $term_ids,
			] )->create()->ID;
		};
		$ids_by_category = [
			'cat1'      => [
				$create_cat_event( [ $term_name_to_id_map['cat1'] ], 'cat1 event 1' ),
				$create_cat_event( [ $term_name_to_id_map['cat1'] ], 'cat1 event 2' ),
				$create_cat_event( [ $term_name_to_id_map['cat1'] ], 'cat1 event 3' ),
			],
			'cat2'      => [
				$create_cat_event( [ $term_name_to_id_map['cat2'] ], 'cat2 event 1' ),
				$create_cat_event( [ $term_name_to_id_map['cat2'] ], 'cat2 event 2' ),
				$create_cat_event( [ $term_name_to_id_map['cat2'] ], 'cat2 event 3' ),
			],
			'cat1_cat2' => [
				$create_cat_event( [
					$term_name_to_id_map['cat1'],
					$term_name_to_id_map['cat2']
				], 'cat1_cat2 event 1' ),
				$create_cat_event( [
					$term_name_to_id_map['cat1'],
					$term_name_to_id_map['cat2']
				], 'cat1_cat2 event 2' ),
				$create_cat_event( [
					$term_name_to_id_map['cat1'],
					$term_name_to_id_map['cat2']
				], 'cat1_cat2 event 3' ),
			],
		];

		return $ids_by_category;
	}

	private function given_posts_and_categories(): array {
		$terms = static::factory()->term->create_many( 2, [ 'taxonomy' => 'category' ] );
		static::factory()->post->create_many( 3, [ 'tax_input' => [ 'category' => [ $terms[0] ] ] ] );
		static::factory()->post->create_many( 3, [ 'tax_input' => [ 'category' => [ $terms[1] ] ] ] );
		static::factory()->post->create_many( 3, [ 'tax_input' => [ 'category' => $terms ] ] );

		return $terms;
	}

	/**
	 * This test is just a control test, to make sure the expectations set in the Event query will match
	 * the correct, expected one.
	 */
	public function test_post_query_found_posts_and_max_num_pages(): void {
		$terms = $this->given_posts_and_categories();

		$query = new \WP_Query( [
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 7,
			'tax_query'      => [
				'relation' => 'OR',
				[
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $terms,
					'operator' => 'IN',
				]
			]
		] );
		$matches = $query->get_posts();

		$this->assertCount( 7, $matches );
		$this->assertEquals( 9, $query->found_posts );
		$this->assertEquals( 2, $query->max_num_pages );
	}

	public function test_original_query_found_posts_and_max_num_pages(): void {
		$this->given_events_and_categories();

		$query = new \WP_Query( [
			'post_type'      => TEC::POSTTYPE,
			'posts_per_page' => 7,
			'tax_query'      => [
				'relation' => 'OR',
				[
					'taxonomy' => TEC::TAXONOMY,
					'field'    => 'slug',
					'terms'    => [ 'cat1', 'cat2' ],
					'operator' => 'IN',
				]
			]
		] );
		$matches = $query->get_posts();

		$this->assertCount( 7, $matches );
		$this->assertEquals( 9, $query->found_posts );
		$this->assertEquals( 2, $query->max_num_pages );
	}

	/**
	 * It should not run found_rows query twice per single query
	 *
	 * @test
	 */
	public function should_not_run_found_rows_query_twice_per_single_query(): void {
		$events = [];
		foreach ( range( 1, 3 ) as $k ) {
			$events[] = tribe_events()->set_args( [
				'post_title'  => 'Event ' . $k,
				'post_status' => 'publish',
				'start_date'  => "+$k days 10 am",
				'duration'    => 2 * HOUR_IN_SECONDS,
			] )->create()->ID;
		}

		// Start logging the queries now.
		$logged_queries = [];
		add_filter( 'query', static function ( string $query ) use ( &$logged_queries ): string {
			$trimmed_query = trim( $query );

			// Only log SELECT queries.
			if ( strpos( $trimmed_query, 'SELECT' ) !== 0 ) {
				return $query;
			}

			$logged_queries[] = $trimmed_query;

			return $query;
		} );

		// Run a query that will be handled and pre-filled by the Custom Tables Query.
		$wp_query = new \WP_Query();
		$found = $wp_query->query( [
			'fields'     => 'ids',
			'post_type'  => TEC::POSTTYPE,
			// Fix the date comparison value to avoid the snapshots being invalidated by time.
			'meta_query' => [
				[
					'key'     => '_EventStartDate',
					'compare' => '>',
					'value'   => '2022-10-01 08:00:00',
				],
			],
		] );

		// We do not really care about the ORDER here, just the set nature.
		$this->assertEqualSets( $events, $found );
		$this->assertMatchesSnapshot( $logged_queries );
	}
}
