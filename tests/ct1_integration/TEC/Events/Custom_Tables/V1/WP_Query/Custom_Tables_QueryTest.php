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

			// Drop a query for terms.
			if ( strpos( $trimmed_query, 'SELECT DISTINCT t.term_id' ) !== false ) {
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
		$this->assertEqualsCanonicalizing( $events, $found );
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
		yield 'by invalid order type' => [
			[
				'orderby' => 'ID',
				'order' => 'bork bork bork',
			],
		];
		yield 'by ASC' => [
			[
				'orderby' => 'ID',
				'order' => 'ASC',
			],
		];
		yield 'by DESC' => [
			[
				'orderby' => 'ID',
				'order' => 'DESC',
			],
		];
		yield 'by nested invalid order type' => [
			[
				'orderby' => [
					'ID' => 'bork bork bork',
				],
			],
		];
		yield 'by nested ASC' => [
			[
				'orderby' => [
					'ID' => 'ASC',
				],
			],
		];
		yield 'by nested DESC' => [
			[
				'orderby' => [
					'ID' => 'DESC',
				],
			],
		];
		yield 'SQL injection on order and order by' => [
			[
				'orderby' => 'ID; (SELECT ID FROM wp_posts)',
				'order' => 'ASC (SELECT ID FROM wp_posts)',
			],
		];
		yield 'SQL injection on order by' => [
			[
				'orderby' => 'ID (SELECT ID FROM wp_posts)',
				'order' => 'DESC',
			],
		];
		yield 'SQL injection on order' => [
			[
				'orderby' => 'ID',
				'order' => 'ASC; SELECT ID FROM wp_posts',
			],
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

		$args = wp_parse_args(
			$args,
			[
				'post_type' => TEC::POSTTYPE,
				'order'     => 'DESC',
			]
		);

		$query = new \WP_Query( $args );

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

	/**
	 * Data provider for SQL injection via search parameter tests.
	 *
	 * @return \Generator
	 */
	public function sql_injection_via_search_provider(): \Generator {
		// CVE-2025-12197: Sleep PoC
		yield 'SLEEP injection via rand()' => [
			's' => 'a,rand()*(SELECT(0)FROM(SELECT(SLEEP(5)))a)#,a',
			'malicious_pattern' => 'SLEEP',
		];

		// Additional SQL injection attempts via rand()
		yield 'SELECT injection via rand()' => [
			's' => 'a,rand()*(SELECT user_pass FROM wp_users)#,a',
			'malicious_pattern' => 'user_pass',
		];

		yield 'UNION injection via rand()' => [
			's' => 'a,rand() UNION SELECT NULL,NULL,NULL#,a',
			'malicious_pattern' => 'UNION',
		];

		yield 'Complex payload with rand prefix' => [
			's' => 'test,rand()*IF(1=1,SLEEP(5),0)#,test',
			'malicious_pattern' => 'IF(1=1',
		];

		yield 'rand with nested SELECT' => [
			's' => 'x,rand()+(SELECT 1 FROM(SELECT COUNT(*),CONCAT(0x3a,(SELECT version()),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)#,x',
			'malicious_pattern' => 'information_schema',
		];
	}

	/**
	 * Test that SQL injection via search parameter with rand() is prevented.
	 *
	 * @test
	 * @dataProvider sql_injection_via_search_provider
	 */
	public function should_prevent_sql_injection_via_search_rand_parameter( string $search, string $malicious_pattern ) {
		// Create some test events.
		tribe_events()->set_args(
			[
				'post_title'  => 'Test Event 1',
				'post_status' => 'publish',
				'start_date'  => 'tomorrow 10 am',
				'duration'    => 2 * HOUR_IN_SECONDS,
			]
		)->create();

		tribe_events()->set_args(
			[
				'post_title'  => 'Test Event 2',
				'post_status' => 'publish',
				'start_date'  => 'tomorrow 2 pm',
				'duration'    => 2 * HOUR_IN_SECONDS,
			]
		)->create();

		global $wpdb;
		$wpdb->last_error = '';

		// Perform the query with the malicious search parameter.
		$query   = new \WP_Query(
			[
				'post_type'      => TEC::POSTTYPE,
				'post_status'    => 'publish',
				's'              => $search,
				'posts_per_page' => 10,
			]
		);
		$request = $query->request;

		// Ensure no DB errors (safe query execution).
		$this->assertEmpty( $wpdb->last_error, 'SQL injection attempt should not cause database errors.' );

		// --- Backward-compatible regex assertion helper.
		$assertNotRegex = function ( $pattern, $subject, $message = '' ) {
			if ( method_exists( $this, 'assertDoesNotMatchRegularExpression' ) ) {
				$this->assertDoesNotMatchRegularExpression( $pattern, $subject, $message );
			} elseif ( method_exists( $this, 'assertNotRegExp' ) ) {
				$this->assertNotRegExp( $pattern, $subject, $message );
			} else {
				// Fallback: invert a positive match.
				$this->assertSame( 0, preg_match( $pattern, $subject ), $message );
			}
		};

		// Allow SQL keywords if they appear inside a quoted LIKE clause.
		// We're only concerned if they appear *unquoted* in the executable SQL.
		$unsafe_pattern = '/\b(SLEEP|UNION|UPDATE|DELETE|INSERT|DROP)\b/i';

		// Extract everything outside of quoted strings.
		$unquoted_sql = preg_replace( "/'[^']*'/", '', $request );

		// Now scan only that stripped version.
		$this->assertSame(
			0,
			preg_match( $unsafe_pattern, $unquoted_sql ),
			'Unsafe SQL keywords should not appear in unquoted SQL context.'
		);

		// If rand() is present, ensure it's only used safely.
		if ( stripos( $request, 'rand' ) !== false ) {
			// Remove all quoted segments so we only check executable SQL.
			$unquoted_sql = preg_replace( "/'[^']*'/", '', $request );

			// Fail only if rand() has parameters *inside* or is followed by SQL operators.
			// NOTE: we must escape `/` inside the character class.
			$bad_rand_pattern = '/rand\s*\([^)]*\)\s*[\+\-\*\/]/i';

			$this->assertSame(
				0,
				preg_match( $bad_rand_pattern, $unquoted_sql ),
				'RAND() should not include parameters or be followed by SQL expressions.'
			);
		}
	}

	/**
	 * Test that legitimate RAND() orderby still works after the fix.
	 *
	 * @test
	 */
	public function should_allow_legitimate_rand_orderby() {
		// Create test events
		tribe_events()->set_args( [
			'post_title'  => 'Random Event 1',
			'post_status' => 'publish',
			'start_date'  => 'tomorrow 10 am',
			'duration'    => 2 * HOUR_IN_SECONDS,
		] )->create();

		tribe_events()->set_args( [
			'post_title'  => 'Random Event 2',
			'post_status' => 'publish',
			'start_date'  => 'tomorrow 2 pm',
			'duration'    => 2 * HOUR_IN_SECONDS,
		] )->create();

		global $wpdb;
		$wpdb->last_error = '';

		// Test with legitimate rand orderby
		$query = new \WP_Query( [
			'post_type'      => TEC::POSTTYPE,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
			'posts_per_page' => 10,
		] );

		$request = $query->request;

		// Assert no errors
		$this->assertEmpty( $wpdb->last_error, 'Legitimate RAND() orderby should not cause errors' );

		// Assert RAND() is present in the query
		$this->assertStringContainsStringIgnoringCase(
			'RAND()',
			$request,
			'Legitimate RAND() should be present in ORDER BY'
		);

		// Assert we got results
		$this->assertGreaterThan( 0, $query->found_posts, 'Query should return results' );
	}
}
