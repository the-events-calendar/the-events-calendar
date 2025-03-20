<?php
/**
 * Test the Category Color Dropdown Provider functionality.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Meta_Keys;

class Category_Color_Dropdown_Provider_Test extends WPTestCase {
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
	 * @test
	 * @dataProvider dropdown_provider_data_provider
	 */
	public function should_return_correct_options( $category_name, $colors, $expected_keys, $hidden = false, $skip_primary = false, $priority = 1 ) {
		// Create test category
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => $category_name,
			]
		);

		// Set color meta using the meta class
		$this->category_meta
			->set_term( $term_id );

		if ( ! $skip_primary ) {
			$this->category_meta->set( Meta_Keys::get_key( 'primary' ), $colors['foreground'] );
		}
		$this->category_meta
			->set( Meta_Keys::get_key( 'secondary' ), $colors['background'] )
			->set( Meta_Keys::get_key( 'text' ), $colors['foreground'] )
			->set( Meta_Keys::get_key( 'priority' ), $priority )
			->set( Meta_Keys::get_key( 'hide_from_legend' ), $hidden )
			->save();

		// Get dropdown options
		$options = $this->dropdown_provider->get_dropdown_categories();

		// Assert options contain the expected data
		$this->assertIsArray( $options );

		if ( $hidden || $skip_primary ) {
			$this->assertEmpty( $options, 'Hidden or invalid categories should not appear in dropdown' );

			return;
		}

		$this->assertNotEmpty( $options, 'Dropdown options should not be empty' );
		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $options[0] );
		}
	}

	/**
	 * Data provider for dropdown provider tests
	 */
	public function dropdown_provider_data_provider() {
		yield 'category with colors' => [
			'category_name' => 'Test Category',
			'colors'        => [
				'foreground' => '#ff0000',
				'background' => '#00ff00',
			],
			'expected_keys' => [ 'slug', 'name', 'priority', 'primary', 'hidden' ],
		];

		yield 'hidden category should not appear' => [
			'category_name' => 'Hidden Category',
			'colors'        => [
				'foreground' => '#ff0000',
				'background' => '#00ff00',
			],
			'expected_keys' => [ 'slug', 'name', 'priority', 'primary', 'hidden' ],
			'hide_from_legend'        => true,
		];

		yield 'category without primary color should not appear' => [
			'category_name' => 'No Primary Color',
			'colors'        => [
				'foreground' => '',
				'background' => '#00ff00',
			],
			'expected_keys' => [ 'slug', 'name', 'priority', 'primary', 'hidden' ],
			'skip_primary'  => true,
		];

		yield 'category with invalid color should not appear' => [
			'category_name' => 'Invalid Color',
			'colors'        => [
				'foreground' => 'invalid-color',
				'background' => '#00ff00',
			],
			'expected_keys' => [ 'slug', 'name', 'priority', 'primary', 'hidden' ],
		];

		yield 'category with high priority should appear first' => [
			'category_name' => 'High Priority',
			'colors'        => [
				'foreground' => '#ff0000',
				'background' => '#00ff00',
			],
			'expected_keys' => [ 'slug', 'name', 'priority', 'primary', 'hidden' ],
			'priority'      => 10,
		];
	}

	/**
	 * @test
	 */
	public function should_sort_categories_by_priority() {
		// Create multiple categories with different priorities
		$categories = [
			[ 'name' => 'Low Priority', 'priority' => 1, 'color' => '#ff0000' ],
			[ 'name' => 'High Priority', 'priority' => 10, 'color' => '#00ff00' ],
			[ 'name' => 'Medium Priority', 'priority' => 5, 'color' => '#0000ff' ],
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
				->set( Meta_Keys::get_key( 'primary' ), $category['color'] )
				->set( Meta_Keys::get_key( 'secondary' ), '#ffffff' )
				->set( Meta_Keys::get_key( 'text' ), '#000000' )
				->set( Meta_Keys::get_key( 'priority' ), $category['priority'] )
				->set( Meta_Keys::get_key( 'hide_from_legend' ), false )
				->save();

			$term_ids[] = $term_id;
		}

		// Get dropdown options
		$options = $this->dropdown_provider->get_dropdown_categories();

		// Assert categories are sorted by priority (highest first)
		$this->assertCount( 3, $options );
		$this->assertEquals( 'High Priority', $options[0]['name'] );
		$this->assertEquals( 'Medium Priority', $options[1]['name'] );
		$this->assertEquals( 'Low Priority', $options[2]['name'] );
	}

	/**
	 * @test
	 */
	public function should_handle_empty_categories() {
		// Create a category with no events
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Empty Category',
			]
		);

		$this->category_meta
			->set_term( $term_id )
			->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#000000' )
			->set( Meta_Keys::get_key( 'priority' ), 1 )
			->set( Meta_Keys::get_key( 'hide_from_legend' ), false )
			->save();

		// Get dropdown options
		$options = $this->dropdown_provider->get_dropdown_categories();

		// Assert empty category is included
		$this->assertNotEmpty( $options );
		$this->assertEquals( 'Empty Category', $options[0]['name'] );
	}
}
