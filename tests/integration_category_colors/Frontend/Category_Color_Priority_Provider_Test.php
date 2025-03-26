<?php
/**
 * Test the Category Color Priority Provider functionality.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Meta_Keys_Trait;

class Category_Color_Priority_Provider_Test extends WPTestCase {
	use Meta_Keys_Trait;

	/**
	 * @var Category_Color_Priority_Category_Provider
	 */
	protected $priority_provider;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->priority_provider = tribe( Category_Color_Priority_Category_Provider::class );
		$this->category_meta     = tribe( Event_Category_Meta::class );
	}

	/**
	 * @test
	 * @dataProvider priority_provider_data_provider
	 */
	public function should_sort_categories_correctly( $categories, $expected_order ) {
		// Create categories with priorities
		$term_ids = [];
		foreach ( $categories as $category ) {
			$term_id = $this->factory()->term->create(
				[
					'taxonomy' => Tribe__Events__Main::TAXONOMY,
					'name'     => $category['name'],
				]
			);

			$this->category_meta
				->set_term( $term_id )
				->set( $this->get_key( 'priority' ), $category['priority'] )
				->save();

			$term_ids[] = $term_id;
		}

		// Create a test event and assign it to all categories
		$event_id = $this->factory()->post->create(
			[
				'post_type'  => 'tribe_events',
				'post_title' => 'Test Event',
			]
		);
		$event = get_post( $event_id );
		wp_set_object_terms( $event_id, $term_ids, Tribe__Events__Main::TAXONOMY );

		// Get highest priority category
		$highest_priority_category = $this->priority_provider->get_highest_priority_category( $event );

		// Assert we got the highest priority category
		$this->assertNotNull( $highest_priority_category );
		$this->assertEquals( $expected_order[0], $highest_priority_category->name );
	}

	/**
	 * Data provider for priority provider tests
	 */
	public function priority_provider_data_provider() {
		yield 'categories with different priorities' => [
			'categories'     => [
				[ 'name' => 'High Priority', 'priority' => 2 ],
				[ 'name' => 'Low Priority', 'priority' => 1 ],
			],
			'expected_order' => [ 'High Priority', 'Low Priority' ], // Higher number = higher priority
		];

		yield 'categories with equal priorities' => [
			'categories'     => [
				[ 'name' => 'Category A', 'priority' => 1 ],
				[ 'name' => 'Category B', 'priority' => 1 ],
			],
			'expected_order' => [ 'Category A', 'Category B' ], // Should return first one in case of tie
		];

		yield 'categories with no priority set' => [
			'categories'     => [
				[ 'name' => 'Category A', 'priority' => null ],
				[ 'name' => 'Category B', 'priority' => null ],
			],
			'expected_order' => [ 'Category A', 'Category B' ], // Should handle null priorities gracefully
		];

		yield 'categories with negative priorities' => [
			'categories'     => [
				[ 'name' => 'Negative Priority', 'priority' => -1 ],
				[ 'name' => 'Zero Priority', 'priority' => 0 ],
			],
			'expected_order' => [ 'Zero Priority', 'Negative Priority' ], // Higher number = higher priority
		];

		yield 'categories with very high priorities' => [
			'categories'     => [
				[ 'name' => 'Very High Priority', 'priority' => 999999 ],
				[ 'name' => 'High Priority', 'priority' => 1000 ],
			],
			'expected_order' => [ 'Very High Priority', 'High Priority' ], // Higher number = higher priority
		];
	}

	/**
	 * @test
	 */
	public function should_handle_hidden_categories() {
		// Create categories with different priorities
		$categories = [
			[ 'name' => 'Visible Category', 'priority' => 2, 'hidden' => false ],
			[ 'name' => 'Hidden Category', 'priority' => 3, 'hidden' => true ],
		];

		$term_ids = [];
		foreach ( $categories as $category ) {
			$term_id = $this->factory()->term->create(
				[
					'taxonomy' => Tribe__Events__Main::TAXONOMY,
					'name'     => $category['name'],
				]
			);

			$this->category_meta
				->set_term( $term_id )
				->set( $this->get_key( 'priority' ), $category['priority'] )
				->set( $this->get_key( 'hide_from_legend' ), $category['hidden'] )
				->save();

			$term_ids[] = $term_id;
		}

		// Create a test event and assign it to all categories
		$event_id = $this->factory()->post->create(
			[
				'post_type'  => 'tribe_events',
				'post_title' => 'Test Event',
			]
		);
		$event    = get_post( $event_id );
		wp_set_object_terms( $event_id, $term_ids, Tribe__Events__Main::TAXONOMY );

		// Get highest priority category
		$highest_priority_category = $this->priority_provider->get_highest_priority_category( $event );

		// Hidden categories do not function for the Priority Provider.
		$this->assertNotNull( $highest_priority_category );
		$this->assertEquals( 'Hidden Category', $highest_priority_category->name );
	}

	/**
	 * @test
	 */
	public function should_handle_events_with_no_categories() {
		// Create an event with no categories
		$event_id = $this->factory()->post->create(
			[
				'post_type'  => 'tribe_events',
				'post_title' => 'Test Event',
			]
		);
		$event    = get_post( $event_id );

		// Get highest priority category
		$highest_priority_category = $this->priority_provider->get_highest_priority_category( $event );

		// Assert we got null since there are no categories
		$this->assertNull( $highest_priority_category );
	}

	/**
	 * @test
	 */
	public function should_handle_invalid_priorities() {
		// Create categories with invalid priorities
		$categories = [
			[ 'name' => 'Invalid Priority 1', 'priority' => 'invalid' ],
			[ 'name' => 'Invalid Priority 2', 'priority' => 'not-a-number' ],
		];

		$term_ids = [];
		foreach ( $categories as $category ) {
			$term_id = $this->factory()->term->create(
				[
					'taxonomy' => Tribe__Events__Main::TAXONOMY,
					'name'     => $category['name'],
				]
			);

			$this->category_meta
				->set_term( $term_id )
				->set( $this->get_key( 'priority' ), $category['priority'] )
				->save();

			$term_ids[] = $term_id;
		}

		// Create a test event and assign it to all categories
		$event_id = $this->factory()->post->create(
			[
				'post_type'  => 'tribe_events',
				'post_title' => 'Test Event',
			]
		);
		$event    = get_post( $event_id );
		wp_set_object_terms( $event_id, $term_ids, Tribe__Events__Main::TAXONOMY );

		// Get highest priority category
		$highest_priority_category = $this->priority_provider->get_highest_priority_category( $event );

		// Assert we got the first category since invalid priorities are treated as equal
		$this->assertNotNull( $highest_priority_category );
		$this->assertEquals( $term_ids[0], $highest_priority_category->term_id );
	}

	/**
	 * @test
	 */
	public function should_handle_mixed_valid_and_invalid_priorities() {
		// Create categories with mixed valid and invalid priorities
		$categories = [
			[ 'name' => 'Valid Priority', 'priority' => 2 ],
			[ 'name' => 'Invalid Priority', 'priority' => 'invalid' ],
		];

		$term_ids = [];
		foreach ( $categories as $category ) {
			$term_id = $this->factory()->term->create(
				[
					'taxonomy' => Tribe__Events__Main::TAXONOMY,
					'name'     => $category['name'],
				]
			);

			$this->category_meta
				->set_term( $term_id )
				->set( $this->get_key( 'priority' ), $category['priority'] )
				->save();

			$term_ids[] = $term_id;
		}

		// Create a test event and assign it to all categories
		$event_id = $this->factory()->post->create(
			[
				'post_type'  => 'tribe_events',
				'post_title' => 'Test Event',
			]
		);
		$event    = get_post( $event_id );

		wp_set_object_terms( $event_id, $term_ids, Tribe__Events__Main::TAXONOMY );

		// Get highest priority category
		$highest_priority_category = $this->priority_provider->get_highest_priority_category( $event );

		// Assert we got the category with valid priority
		$this->assertNotNull( $highest_priority_category );
		$this->assertEquals( $term_ids[0], $highest_priority_category->term_id );
	}
}
