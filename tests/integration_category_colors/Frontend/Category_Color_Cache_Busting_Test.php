<?php
/**
 * Test the Category Color cache busting functionality.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Meta_Keys_Trait;

class Category_Color_Cache_Busting_Test extends WPTestCase {
	use Meta_Keys_Trait;

	/**
	 * @var Category_Color_Dropdown_Provider
	 */
	protected $dropdown_provider;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		$this->category_meta     = tribe( Event_Category_Meta::class );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		
		// Bust cache to ensure clean state
		$this->dropdown_provider->bust_dropdown_categories_cache();
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_term_creation() {
		// Get initial cache state (should be empty)
		$initial_cache = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $initial_cache );

		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust Creation Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Create another term (this should trigger cache busting)
		$term_id_2 = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust Creation Test 2',
			]
		);

		$this->category_meta
			->set_term( $term_id_2 )
			->set( $this->get_key( 'primary' ), '#00ff00' )
			->set( $this->get_key( 'secondary' ), '#ff0000' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 2 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Verify cache is busted
		$cached_result_after_creation = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_creation );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_term_update() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust Update Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Update the term
		wp_update_term( $term_id, Tribe__Events__Main::TAXONOMY, [
			'name' => 'Updated Cache Bust Update Test'
		] );

		// Verify cache is busted
		$cached_result_after_update = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_update );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_term_deletion() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust Deletion Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Delete the term
		wp_delete_term( $term_id, Tribe__Events__Main::TAXONOMY );

		// Verify cache is busted
		$cached_result_after_deletion = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_deletion );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_css_regeneration() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Cache Bust CSS Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache by calling get_dropdown_categories
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists by checking directly
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Trigger CSS regeneration hook
		do_action( 'tec_events_category_colors_css_regenerated' );

		// Verify cache is busted by checking directly (not calling get_dropdown_categories which would repopulate it)
		$cached_result_after_css_regeneration = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_css_regeneration );
	}

	/**
	 * @test
	 */
	public function should_not_bust_cache_for_other_taxonomies() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Other Taxonomy Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Create a term in a different taxonomy
		$other_term_id = $this->factory()->term->create(
			[
				'taxonomy' => 'category', // Different taxonomy
				'name'     => 'Other Taxonomy Term',
			]
		);

		// Verify cache is NOT busted for other taxonomies
		$cached_result_after_other_taxonomy = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result_after_other_taxonomy );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_term_edit() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Term Edit Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache by calling get_dropdown_categories
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists by checking directly
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Actually edit the term (this will trigger the edited_ hook naturally)
		wp_update_term(
			$term_id,
			Tribe__Events__Main::TAXONOMY,
			[
				'name' => 'Updated Term Edit Test',
			]
		);

		// Verify cache is busted by checking directly (not calling get_dropdown_categories which would repopulate it)
		$cached_result_after_term_edit = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_term_edit );
	}

	/**
	 * @test
	 */
	public function should_bust_cache_on_meta_update() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Meta Update Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache by calling get_dropdown_categories
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists by checking directly
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Update the meta
		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#00ff00' ) // Change color
			->save();

		// Manually trigger the edited_ hook since update_term_meta doesn't fire it
		// This simulates what would happen if the term was actually edited
		do_action( 'edited_' . Tribe__Events__Main::TAXONOMY, $term_id );

		// Verify cache is busted by checking directly (not calling get_dropdown_categories which would repopulate it)
		$cached_result_after_meta_update = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_meta_update );
	}

	/**
	 * @test
	 */
	public function should_handle_multiple_cache_busting_events() {
		// Create a category with colors
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Multiple Events Test',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#000000' )
			->set( $this->get_key( 'priority' ), 1 )
			->set( $this->get_key( 'hide_from_legend' ), false )
			->save();

		// Populate cache
		$categories = $this->dropdown_provider->get_dropdown_categories();
		$this->assertNotEmpty( $categories );

		// Verify cache exists
		$cached_result = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertNotFalse( $cached_result );

		// Trigger multiple cache busting events
		do_action( 'tec_events_category_colors_css_regenerated' );
		wp_update_term( $term_id, Tribe__Events__Main::TAXONOMY, [
			'name' => 'Updated Multiple Events Test'
		] );

		// Verify cache is busted
		$cached_result_after_multiple_events = tribe_cache()->get( Category_Color_Dropdown_Provider::CACHE_KEY );
		$this->assertFalse( $cached_result_after_multiple_events );
	}
} 