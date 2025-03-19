<?php
/**
 * Test the CSS Generator functionality.
 *
 * @since   TBD
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

class CSS_Generator_Test extends WPTestCase {
	use MatchesSnapshots;

	/**
	 * @var Generator
	 */
	protected $css_generator;

	/**
	 * @var Event_Category_Meta
	 */
	protected $category_meta;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->css_generator = tribe( CSS_Generator::class );
		$this->category_meta = tribe( Event_Category_Meta::class );
	}

	/**
	 * @test
	 */
	public function should_generate_correct_css() {
		// Create a test category with color meta
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Test Category',
			]
		);

		// Set color meta using the meta class
		$this->category_meta
			->set_term( $term_id )
			->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
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
				$term_id = $this->factory()->term->create(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Primary Only',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
					->save();

				return $term_id;
			},
		];

		yield 'category with invalid colors' => [
			'fixture' => function () {
				$term_id = $this->factory()->term->create(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Invalid Colors',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( Meta_Keys::get_key( 'primary' ), 'invalid-color' )
					->set( Meta_Keys::get_key( 'secondary' ), 'not-a-color' )
					->set( Meta_Keys::get_key( 'text' ), 'wrong' )
					->save();

				return $term_id;
			},
		];

		yield 'category with special characters in name' => [
			'fixture' => function () {
				$term_id = $this->factory()->term->create(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => 'Special @#$%^&*()',
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
					->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
					->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
					->save();

				return $term_id;
			},
		];

		yield 'category with very long name' => [
			'fixture' => function () {
				$term_id = $this->factory()->term->create(
					[
						'taxonomy' => Tribe__Events__Main::TAXONOMY,
						'name'     => str_repeat( 'Very Long Category Name ', 5 ),
					]
				);
				$this->category_meta
					->set_term( $term_id )
					->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
					->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
					->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
					->save();

				return $term_id;
			},
		];

		yield 'large number of categories' => [
			'fixture' => function () {
				$num_categories = 100;
				$term_ids       = [];

				for ( $i = 0; $i < $num_categories; $i++ ) {
					$term_id = $this->factory()->term->create(
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
						->set( Meta_Keys::get_key( 'primary' ), $colors[ $color_index ] )
						->set( Meta_Keys::get_key( 'secondary' ), $colors[ ( $color_index + 1 ) % count( $colors ) ] ) // Next color
						->set( Meta_Keys::get_key( 'text' ), $colors[ ( $color_index + 2 ) % count( $colors ) ] ) // Next-next color
						->set( Meta_Keys::get_key( 'priority' ), $i + 1 ) // Incremental priority
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

		// Replace dynamic values with placeholders
		foreach ( $term_ids as $index => $term_id ) {
			$css = str_replace( (string) $term_id, "{TERM_ID_$index}", $css );
		}

		$this->assertMatchesSnapshot( $css );
	}

	/**
	 * @test
	 */
	public function should_respect_category_priority() {
		// Create categories with different priorities
		$high_priority = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'High Priority',
			]
		);
		$this->category_meta
			->set_term( $high_priority )
			->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
			->set( Meta_Keys::get_key( 'priority' ), '999999' )
			->save();

		$low_priority = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Low Priority',
			]
		);
		$this->category_meta
			->set_term( $low_priority )
			->set( Meta_Keys::get_key( 'primary' ), '#0000ff' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'priority' ), '1' )
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
		$visible = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Visible Category',
			]
		);
		$this->category_meta
			->set_term( $visible )
			->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
			->save();

		// Create a hidden category
		$hidden = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Hidden Category',
			]
		);
		$this->category_meta
			->set_term( $hidden )
			->set( Meta_Keys::get_key( 'primary' ), '#0000ff' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'hidden' ), true )
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
		$category1 = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Category One',
			]
		);
		$this->category_meta
			->set_term( $category1 )
			->set( Meta_Keys::get_key( 'primary' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#0000ff' )
			->set( Meta_Keys::get_key( 'priority' ), '100' )
			->save();

		$category2 = $this->factory()->term->create(
			[
				'taxonomy' => Tribe__Events__Main::TAXONOMY,
				'name'     => 'Category Two',
			]
		);
		$this->category_meta
			->set_term( $category2 )
			->set( Meta_Keys::get_key( 'primary' ), '#0000ff' )
			->set( Meta_Keys::get_key( 'secondary' ), '#00ff00' )
			->set( Meta_Keys::get_key( 'text' ), '#ff0000' )
			->set( Meta_Keys::get_key( 'priority' ), '100' )
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
}
