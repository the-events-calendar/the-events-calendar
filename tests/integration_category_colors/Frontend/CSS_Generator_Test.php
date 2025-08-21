<?php
/**
 * Test the CSS Generator functionality.
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\CSS\Generator as CSS_Generator;
use Tribe__Events__Main;
use Spatie\Snapshots\MatchesSnapshots;
use Closure;
use Generator;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe\Tests\Traits\With_Uopz;

class CSS_Generator_Test extends WPTestCase {
	use MatchesSnapshots;
	use Meta_Keys_Trait;
	use With_Uopz;

	/**
	 * @var Generator
	 */
	protected $css_generator;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @var array<int>
	 */
	protected $created_term_ids;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->css_generator    = tribe( CSS_Generator::class );
		$this->category_meta    = tribe( Event_Category_Meta::class );
		$this->created_term_ids = []; // Initialize term tracking
	}

	/**
	 * @after
	 */
	public function cleanup_created_terms(): void {
		foreach ( $this->created_term_ids as $term_id ) {
			wp_delete_term( $term_id, Tribe__Events__Main::TAXONOMY );
		}
	}

	/**
	 * Helper function to create a term and track it for cleanup.
	 */
	protected function create_test_term( array $args ): int {
		$term_id                  = $this->factory()->term->create( $args );
		$this->created_term_ids[] = $term_id; // Track term for deletion

		return $term_id;
	}

	/**
	 * @test
	 */
	public function should_generate_correct_css() {
		// Create a test category with color meta
		$term_id = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		// Set color meta using the meta class
		$this->category_meta
			->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#0000ff' )
			->save();

		// Generate CSS
		$css = $this->css_generator->generate_css();

		// Replace dynamic values with placeholders
		$css = str_replace(
			[
				$term_id,
			],
			[
				'{TERM_ID}',
			],
			$css
		);

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * Data provider for CSS generator tests
	 */
	public function css_generator_data_provider(): Generator {
		yield 'empty category' => [
			'fixture' => function () {
				return $this->factory()->term->create(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Empty Category',
					]
				);
			},
		];

		yield 'category with only primary color' => [
			'fixture' => function () {
				$term_id = $this->create_test_term(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Primary Only',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( $this->get_key( 'primary' ), '#ff0000' )
					->save();

				return $term_id;
			},
		];

		yield 'category with invalid colors' => [
			'fixture' => function () {
				$term_id = $this->create_test_term(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Invalid Colors',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( $this->get_key( 'primary' ), 'invalid-color' )
					->set( $this->get_key( 'secondary' ), 'not-a-color' )
					->set( $this->get_key( 'text' ), 'wrong' )
					->save();

				return $term_id;
			},
		];

		yield 'category with special characters in name' => [
			'fixture' => function () {
				$term_id = $this->create_test_term(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Special @#$%^&*()',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( $this->get_key( 'primary' ), '#ff0000' )
					->set( $this->get_key( 'secondary' ), '#00ff00' )
					->set( $this->get_key( 'text' ), '#0000ff' )
					->save();

				return $term_id;
			},
		];

		yield 'category with very long name' => [
			'fixture' => function () {
				$term_id = $this->create_test_term(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => str_repeat( 'Very Long Category Name ', 5 ),
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( $this->get_key( 'primary' ), '#ff0000' )
					->set( $this->get_key( 'secondary' ), '#00ff00' )
					->set( $this->get_key( 'text' ), '#0000ff' )
					->save();

				return $term_id;
			},
		];

		yield 'large number of categories' => [
			'fixture' => function () {
				$num_categories = 100;
				$term_ids       = [];

				for ( $i = 0; $i < $num_categories; $i++ ) {
					$term_id = $this->create_test_term(
						[
							'taxonomy' => Tribe__Events__Main::TAXONOMY,
							'name'     => "Category $i",
						]
					);

					// Fixed color sequence (cycling through predefined values)
					$colors      = [ '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff' ];
					$color_index = $i % count( $colors ); // Cycle through colors

					// Assign sequential, predictable values
					$this->category_meta->set_term( $term_id )
						->set( $this->get_key( 'primary' ), $colors[ $color_index ] )
						->set( $this->get_key( 'secondary' ), $colors[ ( $color_index + 1 ) % count( $colors ) ] ) // Next color
						->set( $this->get_key( 'text' ), $colors[ ( $color_index + 2 ) % count( $colors ) ] ) // Next-next color
						->set( $this->get_key( 'priority' ), $i + 1 ) // Incremental priority
						->save();

					$term_ids[] = $term_id;
				}

				return $term_ids;
			},
		];
	}

	/**
	 * @test
	 * @dataProvider css_generator_data_provider
	 */
	public function should_handle_various_category_scenarios( Closure $fixture ) {
		$fixture = $fixture->bindTo( $this, self::class );

		// Execute the fixture
		$term_ids = $fixture();
		$css      = $this->css_generator->generate_css();

		// Ensure we always work with an array
		if ( ! is_array( $term_ids ) ) {
			$term_ids = [ $term_ids ];
		}

		$replacement_count = 1;

		$css = preg_replace_callback(
			'/\.tribe_events_cat-[^\s{]+/', // Matches `.tribe_events_cat-` followed by any non-whitespace/non-{ characters
			function () use ( &$replacement_count ) {
				return ".tribe_events_cat-{Placeholder-" . $replacement_count++ . "}";
			},
			$css
		);

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * @test
	 */
	public function should_respect_category_priority() {
		// Create categories with different priorities
		$high_priority = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'High Priority',
			]
		);
		$this->category_meta
			->set_term( $high_priority )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#0000ff' )
			->set( $this->get_key( 'priority' ), '999999' )
			->save();

		$low_priority = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Low Priority',
			]
		);
		$this->category_meta
			->set_term( $low_priority )
			->set( $this->get_key( 'primary' ), '#0000ff' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#ff0000' )
			->set( $this->get_key( 'priority' ), '1' )
			->save();

		$css = $this->css_generator->generate_css();

		// Replace dynamic values with placeholders
		$css = str_replace(
			[
				$high_priority,
				$low_priority,
			],
			[
				'{HIGH_PRIORITY_ID}',
				'{LOW_PRIORITY_ID}',
			],
			$css
		);

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * @test
	 */
	public function should_handle_hidden_categories() {
		// Create a visible category
		$visible = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Visible Category',
			]
		);
		$this->category_meta
			->set_term( $visible )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#0000ff' )
			->set( $this->get_key( 'priority' ), '1' )
			->save();

		// Create a hidden category
		$hidden = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Hidden Category',
			]
		);
		$this->category_meta
			->set_term( $hidden )
			->set( $this->get_key( 'primary' ), '#0000ff' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#ff0000' )
			->set( $this->get_key( 'priority' ), '2' )
			->set( $this->get_key( 'hide_from_legend' ), '1' )
			->save();

		$css = $this->css_generator->generate_css();

		// Replace dynamic values with placeholders
		$css = str_replace(
			[
				$visible,
				$hidden,
			],
			[
				'{VISIBLE_ID}',
				'{HIDDEN_ID}',
			],
			$css
		);

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * @test
	 */
	public function should_handle_multiple_categories_with_same_priority() {
		// Create categories with same priority
		$category1 = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Category One',
			]
		);
		$this->category_meta
			->set_term( $category1 )
			->set( $this->get_key( 'primary' ), '#ff0000' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#0000ff' )
			->set( $this->get_key( 'priority' ), '100' )
			->save();

		$category2 = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Category Two',
			]
		);
		$this->category_meta
			->set_term( $category2 )
			->set( $this->get_key( 'primary' ), '#0000ff' )
			->set( $this->get_key( 'secondary' ), '#00ff00' )
			->set( $this->get_key( 'text' ), '#ff0000' )
			->set( $this->get_key( 'priority' ), '100' )
			->save();

		$css = $this->css_generator->generate_css();

		// Replace dynamic values with placeholders
		$css = str_replace(
			[
				$category1,
				$category2,
			],
			[
				'{CATEGORY_1_ID}',
				'{CATEGORY_2_ID}',
			],
			$css
		);

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * @test
	 */
	public function should_refresh_css_when_categories_change() {
		// Mock the nonce verification to always return true
		$this->set_fn_return( 'wp_verify_nonce', true );

		// Generate initial CSS
		$this->css_generator->generate_css();
		$initial_css = get_option( 'tec_events_category_color_css' );

		// Create a new category
		$term_id = $this->create_test_term(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'New Category',
			]
		);

		// Assign color meta
		$this->category_meta->set_term( $term_id )
			->set( $this->get_key( 'primary' ), '#123456' )
			->save();

		// Regenerate CSS
		$this->css_generator->generate_and_save_css();
		$updated_css = get_option( 'tec_events_category_color_css' );

		// Ensure CSS has changed
		$this->assertNotEquals( $initial_css, $updated_css, 'CSS should update when new categories are added.' );
	}

	/**
	 * @test
	 */
	public function should_save_css_in_wp_options() {
		update_option( 'tec_events_category_color_css', '', true );

		$this->assertEmpty( get_option( 'tec_events_category_color_css' ) );
		// Create multiple categories
		$categories = [
			[ 'name' => 'Category One', 'primary' => '#ff0000', 'secondary' => '#00ff00', 'text' => '#0000ff', 'priority' => 5 ],
			[ 'name' => 'Category Two', 'primary' => '#abcdef', 'secondary' => '#123456', 'text' => '#654321', 'priority' => 10 ],
			[ 'name' => 'Category Three', 'primary' => '#222222', 'secondary' => '#333333', 'text' => '#444444', 'priority' => 1 ],
		];

		$term_ids = [];

		// Create and set up meta for each category
		foreach ( $categories as $category ) {
			$term_id = $this->create_test_term(
				[
					'taxonomy' => Tribe__Events__Main::TAXONOMY,
					'name'     => $category['name'],
				]
			);

			$this->category_meta
				->set_term( $term_id )
				->set( $this->get_key( 'primary' ), $category['primary'] )
				->set( $this->get_key( 'secondary' ), $category['secondary'] )
				->set( $this->get_key( 'text' ), $category['text'] )
				->set( $this->get_key( 'priority' ), $category['priority'] )
				->save();
		}

		// Generate CSS
		$this->css_generator->generate_and_save_css();

		// Retrieve saved CSS
		$saved_css = get_option( 'tec_events_category_color_css' );
		// Ensure the CSS is saved
		$this->assertNotEmpty( $saved_css, 'Generated CSS should be saved in wp_options.' );

		$replacement_count = 1;

		$saved_css = preg_replace_callback(
			'/\.tribe_events_cat-[^\s{]+/', // Matches `.tribe_events_cat-` followed by any non-whitespace/non-{ characters
			function () use ( &$replacement_count ) {
				return ".tribe_events_cat-{Placeholder-" . $replacement_count++ . "}";
			},
			$saved_css
		);

		// Assert against snapshot
		$this->assertMatchesSnapshot( $saved_css );
	}

}
