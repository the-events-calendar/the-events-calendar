<?php
/**
 * Test the Category Color Priority Provider functionality.
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;
use TEC\Events\Category_Colors\CSS\Generator;
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
			[
				'name'     => 'Visible Category',
				'priority' => 20,
				'hidden'   => false,
			],
			[
				'name'     => 'Hidden Category',
				'priority' => 30,
				'hidden'   => true,
			],
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

	/**
	 * @test
	 * @since TBD
	 */
	public function should_prevent_category_data_bleed_between_events() {
		$controller = tribe( Controller::class );
		$generator  = tribe( Generator::class );
		$opt_key    = $generator->get_option_key();

		// Step 1: Enable frontend UI and mark feature as in use.
		update_option( 'category-color-enable-frontend', true );
		update_option( $opt_key, '/* css to mark as active */' );
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_true' );

		// Assert step 1: Verify options are set correctly.
		$this->assertTrue(
			(bool) get_option( 'category-color-enable-frontend' ),
			'Frontend UI should be enabled.'
		);
		$this->assertNotEmpty(
			get_option( $opt_key ),
			'CSS option should be set to mark feature as in use.'
		);

		// Step 2: Create a category with color meta.
		$category_with_color = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Colored Category',
			]
		);

		update_term_meta( $category_with_color, $this->get_key( 'primary' ), '#ff0000' );
		update_term_meta( $category_with_color, $this->get_key( 'text' ), '#000000' );
		update_term_meta( $category_with_color, $this->get_key( 'priority' ), 10 );

		// Assert step 2: Verify category was created with correct meta.
		$this->assertNotEmpty( $category_with_color, 'Category with color should be created.' );
		$this->assertEquals(
			'#ff0000',
			get_term_meta( $category_with_color, $this->get_key( 'primary' ), true ),
			'Primary color meta should be set.'
		);
		$this->assertEquals(
			'#000000',
			get_term_meta( $category_with_color, $this->get_key( 'text' ), true ),
			'Text color meta should be set.'
		);
		$this->assertEquals(
			10,
			get_term_meta( $category_with_color, $this->get_key( 'priority' ), true ),
			'Priority meta should be set.'
		);

		// Step 3: Create event WITH a colored category.
		$event_with_category = $this->factory()->post->create(
			[
				'post_type' => Tribe__Events__Main::POSTTYPE,
			]
		);
		wp_set_object_terms( $event_with_category, [ $category_with_color ], Tribe__Events__Main::TAXONOMY );

		// Assert step 3: Verify event was created and assigned to category.
		$this->assertNotEmpty( $event_with_category, 'Event with category should be created.' );
		$event_terms = wp_get_object_terms( $event_with_category, Tribe__Events__Main::TAXONOMY );
		$this->assertCount( 1, $event_terms, 'Event should have exactly one category.' );
		$this->assertEquals( $category_with_color, $event_terms[0]->term_id, 'Event should be assigned to colored category.' );

		// Step 4: Simulate rendering the first event and add category data.
		global $post;
		$post = get_post( $event_with_category );
		setup_postdata( $post );

		$context = [ 'example' => 'value' ];
		$result  = $controller->add_category_data( $context );

		// Assert step 4: Verify category data is present for the first event.
		$this->assertArrayHasKey(
			'category_colors_priority_category',
			$result,
			'Expected priority category for event with colored category.'
		);
		$this->assertArrayHasKey(
			'category_colors_meta',
			$result,
			'Expected category meta for event with colored category.'
		);
		$this->assertNotEmpty(
			$result['category_colors_priority_category'],
			'Priority category should not be empty.'
		);
		$this->assertNotEmpty(
			$result['category_colors_meta'],
			'Category meta should not be empty.'
		);

		// Store the context with category data for next test.
		$context_with_category_data = $result;

		// Step 5: Create event WITHOUT any category.
		$event_without_category = $this->factory()->post->create(
			[
				'post_type' => Tribe__Events__Main::POSTTYPE,
			]
		);

		// Assert step 5: Verify event has no categories.
		$event_terms_no_cat = wp_get_object_terms( $event_without_category, Tribe__Events__Main::TAXONOMY );
		$this->assertEmpty( $event_terms_no_cat, 'Event should have no categories.' );

		// Step 6: Simulate rendering the second event with polluted context from first event.
		$post = get_post( $event_without_category );
		setup_postdata( $post );

		// Pass the same context that had category data from the first event (simulating the bleed).
		$result_without_category = $controller->add_category_data( $context_with_category_data );

		// Assert step 6: Verify category data is NOT present for the second event.
		$this->assertArrayNotHasKey(
			'category_colors_priority_category',
			$result_without_category,
			'Expected no priority category for event without category. Category data should not bleed from previous event.'
		);
		$this->assertArrayNotHasKey(
			'category_colors_meta',
			$result_without_category,
			'Expected no category meta for event without category. Category data should not bleed from previous event.'
		);

		// Step 7: Create event with category but NO color meta.
		$category_without_color = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Uncolored Category',
			]
		);

		$event_with_uncolored_category = $this->factory()->post->create(
			[
				'post_type' => Tribe__Events__Main::POSTTYPE,
			]
		);
		wp_set_object_terms( $event_with_uncolored_category, [ $category_without_color ], Tribe__Events__Main::TAXONOMY );

		// Assert step 7: Verify event has category but category has no color meta.
		$event_terms_uncolored = wp_get_object_terms( $event_with_uncolored_category, Tribe__Events__Main::TAXONOMY );
		$this->assertCount( 1, $event_terms_uncolored, 'Event should have exactly one category.' );
		$this->assertEmpty(
			get_term_meta( $category_without_color, $this->get_key( 'primary' ), true ),
			'Category should have no primary color meta.'
		);

		// Step 8: Simulate rendering event with uncolored category, using polluted context from first event.
		$post = get_post( $event_with_uncolored_category );
		setup_postdata( $post );

		$result_uncolored = $controller->add_category_data( $context_with_category_data );

		// Assert step 8: Verify category data is NOT present for event with uncolored category.
		$this->assertArrayNotHasKey(
			'category_colors_priority_category',
			$result_uncolored,
			'Expected no priority category for event with uncolored category. Category data should not bleed from previous event.'
		);
		$this->assertArrayNotHasKey(
			'category_colors_meta',
			$result_uncolored,
			'Expected no category meta for event with uncolored category. Category data should not bleed from previous event.'
		);

		wp_reset_postdata();
		remove_filter( 'tec_events_category_colors_show_frontend_ui', '__return_true' );
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function should_return_context_unchanged_when_frontend_ui_disabled() {
		$controller = tribe( Controller::class );

		// Disable frontend UI.
		update_option( 'category-color-enable-frontend', false );

		$context = [ 'example' => 'value' ];
		$result  = $controller->add_category_data( $context );

		$this->assertSame(
			$context,
			$result,
			'Expected same context when frontend UI is disabled.'
		);
	}

	/**
	 * @test
	 * @since TBD
	 */
	public function should_return_context_unchanged_when_event_is_invalid() {
		$controller = tribe( Controller::class );
		$generator  = tribe( Generator::class );
		$opt_key    = $generator->get_option_key();

		// Enable frontend UI and mark feature as in use.
		update_option( 'category-color-enable-frontend', true );
		update_option( $opt_key, '/* css to mark as active */' );
		add_filter( 'tec_events_category_colors_show_frontend_ui', '__return_true' );

		// Clear global post.
		unset( $GLOBALS['post'] );

		$context = [ 'example' => 'value' ];
		$result  = $controller->add_category_data( $context );

		$this->assertSame(
			$context,
			$result,
			'Expected same context when event is invalid or missing.'
		);

		remove_filter( 'tec_events_category_colors_show_frontend_ui', '__return_true' );
	}




}
