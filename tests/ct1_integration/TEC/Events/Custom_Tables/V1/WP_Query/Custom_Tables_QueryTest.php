<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use Tribe__Events__Main as TEC;

class Custom_Tables_QueryTest extends \Codeception\TestCase\WPTestCase {

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
}
