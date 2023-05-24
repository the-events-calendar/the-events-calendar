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

	public function orderby_data_provider(): \Generator {
		yield 'no orderby specified' => [ [] ];
		yield 'event_date' => [ [ 'orderby' => 'event_date' ] ];
		yield 'event_date_utc' => [ [ 'orderby' => 'event_date_utc' ] ];
		yield 'event_duration' => [ [ 'orderby' => 'event_duration' ] ];
		yield 'ID' => [ [ 'orderby' => 'ID' ] ];

		global $wpdb;
		yield 'wp_posts.ID' => [ [ 'orderby' => $wpdb->posts . '.ID' ] ];

		yield 'REST API like query' => [
			'orderby'             =>
				[
					'date'                 => 'desc',
					'tec_event_start_date' => 'ASC',
					'post_date'            => 'ASC',
				],
			'paged'               => 1,
			'post_status'         => [ 0 => 'publish', ],
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'meta_query'          =>
				[
					'tec_event_start_date' =>
						[
							'key'     => '_EventStartDate',
							'compare' => 'EXISTS',
						],
					'tec_event_end_date'   =>
						[
							'key'     => '_EventEndDate',
							'value'   => '2022-11-07 18:15:34',
							'compare' => '>=',
							'type'    => 'DATETIME',
						],
				],
		];

		yield 'none' => [ [ 'orderby' => 'none' ] ];
		yield 'rand' => [ [ 'orderby' => 'rand' ] ];
		yield 'relevance' => [ [ 'orderby' => 'relevance' ] ];
		yield 'by _EventStartDate meta_value' => [
			[
				'meta_key' => '_EventStartDate',
				'orderby'  => 'meta_value',
			]
		];
		yield 'by _EventEndDate meta_value' => [
			[
				'meta_key' => '_EventEndDate',
				'orderby'  => 'meta_value',
			]
		];
		yield 'by _EventStartDateUTC meta_value' => [
			[
				'meta_key' => '_EventStartDateUTC',
				'orderby'  => 'meta_value',
			]
		];
		yield 'by _EventEndDateUTC meta_value' => [
			[
				'meta_key' => '_EventEndDateUTC',
				'orderby'  => 'meta_value',
			]
		];
	}

	/**
	 * It should correctly order events by different order_by criteria
	 *
	 * @test
	 * @dataProvider orderby_data_provider
	 */
	public function should_correctly_order_events_by_different_order_by_criteria( array $args ) {
		// Fix the `now` moment to avoid snapshot invalidation.
		add_filter( 'tec_events_query_current_moment', static function () {
			return '2022-10-01 08:00:00';
		} );
		$query = new \WP_Query( wp_parse_args( [
			'post_type' => TEC::POSTTYPE,
			'order'     => 'DESC',
		], $args ) );

		$request = $query->request;

		global $wpdb;
		$this->assertEmpty( $wpdb->last_error );
		$this->assertMatchesSnapshot( $request );
	}

	/**
	 * Test that we can convert a meta_value order by, into the CT1 equivalent and retrieve expected result.
	 *
	 * @test
	 */
	public function should_orderby_get_posts_with_meta_query() {
		$post_id = tribe_events()->set_args( [
			'post_title'  => 'Event Faux ',
			'post_status' => 'publish',
			'start_date'  => "2023-03-23 00:00:00",
			'duration'    => 2 * HOUR_IN_SECONDS,
		] )->create()->ID;
		tribe_events()->set_args( [
			'post_title'  => 'Event Faux ',
			'post_status' => 'publish',
			'start_date'  => "2023-03-20 00:00:00",
			'duration'    => 2 * HOUR_IN_SECONDS,
		] )->create()->ID;

		$args = array(
			'post_type'        => array( 'tribe_events' ),
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'meta_query'       => array(
				'relation'     => 'AND',
				'starts_after' => array(
					'key'     => '_EventEndDate',
					'compare' => '>=',
					'value'   => '2023-03-01 00:00:00'
				)
			),
			'fields'           => 'ids',
			'suppress_filters' => false,
			'orderby'          => 'meta_value',
			'order'            => 'DESC'
		);

		// Order by should be parsed correctly.
		$posts = get_posts( $args );
		$this->assertCount( 1, $posts, "Should find our post with meta order by query." );
		$this->assertContains( $post_id, $posts, "The first event created should be found due to order by query." );
	}
}
