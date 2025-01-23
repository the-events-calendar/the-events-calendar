<?php

namespace TEC\Events\Category_Colors\Tests;

use Closure;
use Codeception\TestCase\WPTestCase;
use ReflectionClass;
use TEC\Events\Category_Colors\Category_Colors;
use TEC\Events\Category_Colors\Settings;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main;

class SettingsTest extends WPTestCase {
	use With_Uopz;

	/**
	 * Automatically cleanup test data after each test.
	 *
	 * @after
	 */
	public function cleanup_test_data(): void {
		$terms = get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			delete_term_meta( $term->term_id, Category_Colors::$meta_selected_category_slug );
			delete_term_meta( $term->term_id, Category_Colors::$meta_foreground_slug );
			delete_term_meta( $term->term_id, Category_Colors::$meta_background_slug );
			delete_term_meta( $term->term_id, Category_Colors::$meta_text_color_slug );
			wp_delete_term( $term->term_id, Tribe__Events__Main::TAXONOMY );
		}
	}

	/**
	 * Data provider for edge case scenarios.
	 *
	 * @return \Generator
	 */
	public function initialize_terms_edgecases_data(): \Generator {
		yield 'Handles terms with special characters correctly' => [
			function () {
				wp_insert_term( 'Category@123', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-special' ] );

				return [
					'expected_terms' => [
						'category-special' => 'Category@123',
					],
				];
			},
		];
		yield 'Maintains consistent term state during caching' => [
			function () {
				$term = wp_insert_term( 'Category 1', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-1' ] );

				add_filter( 'tec_category_colors_delete_terms', fn() => [ 'category-1' ] );

				return [
					'expected_terms'         => [],
					'expected_ignored_terms' => [],
				];
			},
		];
		yield 'Correctly filters out hidden terms' => [
			function () {
				wp_insert_term( 'Category 1', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-1' ] );
				wp_insert_term( 'Category 2', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-2-hide' ] );

				add_filter( 'tec_category_colors_suffix', fn() => '-hide' );

				return [
					'expected_terms'         => [
						'category-1' => 'Category 1',
					],
					'expected_ignored_terms' => [
						'category-2' => 'Category 2',
					],
				];
			},
		];

		yield 'Ignores terms with invalid structures' => [
			function () {
				// Create a valid term.
				wp_insert_term( 'Category 1', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-1' ] );

				// Simulate invalid term data by filtering results.
				add_filter( 'tec_category_colors_categories', fn() => [ null, 'invalid', (object) [ 'slug' => 'invalid-slug' ] ] );

				return [
					'expected_terms'         => [],
					'expected_ignored_terms' => [],
				];
			},
		];

		yield 'Returns no terms when taxonomy is empty' => [
			function () {
				return [
					'expected_terms'         => [],
					'expected_ignored_terms' => [],
				];
			},
		];
	}

	/**
	 * @test         `initialize_terms` with edge cases using taxonomy meta.
	 *
	 * @dataProvider initialize_terms_edgecases_data
	 *
	 * @param Closure $fixture The fixture closure to set up the scenario.
	 */
	public function initialize_terms_edgecases( Closure $fixture ): void {
		// Set up the fixture and get the scenario data.
		$scenario = $fixture();

		// Get the Settings class instance.
		$settings   = tribe( Settings::class );
		$reflection = new ReflectionClass( $settings );

		// Access and invoke the protected initialize_terms method.
		$method = $reflection->getMethod( 'initialize_terms' );
		$method->setAccessible( true );
		$method->invoke( $settings );

		$terms = $settings->get_filtered_terms();

		// Validate expected terms.
		foreach ( $scenario['expected_terms'] as $slug => $name ) {
			$term = array_filter( $terms, fn( $term ) => $term->slug === $slug );
			$this->assertNotEmpty( $term, "Expected term {$slug} was not found." );
			$this->assertEquals( $name, current( $term )->name, "Expected term name for {$slug} did not match." );

			// Check for associated meta values.
			foreach ( [ Category_Colors::$meta_foreground_slug, Category_Colors::$meta_background_slug, Category_Colors::$meta_text_color_slug ] as $meta_key ) {
				$meta_value = get_term_meta( current( $term )->term_id, $meta_key, true );
				$this->assertNotNull( $meta_value, "Expected meta {$meta_key} for term {$slug} was not found." );
			}
		}
	}

	/**
	 * @test Test term visibility across user roles.
	 */
	public function terms_visibility_across_roles(): void {
		// Create a term as an admin.
		wp_set_current_user( 1 );
		$term = wp_insert_term( 'Admin Created Category', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'admin-category' ] );

		// Ensure term creation succeeded.
		$this->assertNotWPError( $term, 'Failed to create term as admin.' );

		// Log in as a subscriber and verify term visibility.
		$subscriber_user = $this->factory()->user->create_and_get( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber_user->ID );

		$settings   = tribe( Settings::class );
		$reflection = new ReflectionClass( $settings );

		$method = $reflection->getMethod( 'initialize_terms' );
		$method->setAccessible( true );
		$method->invoke( $settings );

		$terms = $settings->get_filtered_terms();
		$term  = array_filter( $terms, fn( $term ) => $term->slug === 'admin-category' );

		$this->assertNotEmpty( $term, 'Term created by admin is not visible to subscriber.' );
		$this->assertEquals( 'Admin Created Category', current( $term )->name, 'Term name does not match as subscriber.' );
		// Log back in as administrator.
		wp_set_current_user( 1 );
	}

	/**
	 * Data provider for saving and retrieving category colors.
	 *
	 * @return \Generator
	 */
	public function category_colors_save_data_provider(): \Generator {
		yield 'Saves valid category and color data' => [
			function () {
				// Insert valid terms into the taxonomy.
				wp_insert_term( 'Family Fun', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'family-fun' ] );
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'family-fun', 'party' ],
						'tec_category_colors_blueprint' => [
							'family-fun' => [
								'foreground' => '#ffffff',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
							'party'      => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'family-fun', 'party' ],
						'blueprint'  => [
							'family-fun' => [
								'foreground' => '#ffffff',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
							'party'      => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
				];
			},
		];

		yield 'Skips invalid categories during save' => [
			function () {
				// Insert only one valid term into the taxonomy.
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'invalid-category', 'party' ],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'party' ],
						'blueprint'  => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
				];
			},
		];

		yield 'Handles duplicate categories properly' => [
			function () {
				// Insert the term once into the taxonomy.
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'party', 'party' ],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'party' ],
						'blueprint'  => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
				];
			},
		];

		yield 'Saves partial color blueprint data' => [
			function () {
				// Insert the term into the taxonomy.
				wp_insert_term( 'Family Fun', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'family-fun' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'family-fun' ],
						'tec_category_colors_blueprint' => [
							'family-fun' => [
								'foreground' => '',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'family-fun' ],
						'blueprint'  => [
							'family-fun' => [
								'foreground' => '',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
						],
					],
				];
			},
		];

		yield 'Saves valid categories while skipping invalid ones' => [
			function () {
				// Insert valid terms into the taxonomy.
				wp_insert_term( 'Family Fun', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'family-fun' ] );
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'family-fun', 'invalid-category', 'party' ],
						'tec_category_colors_blueprint' => [
							'family-fun' => [
								'foreground' => '#ffffff',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
							'party'      => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'family-fun', 'party' ],
						'blueprint'  => [
							'family-fun' => [
								'foreground' => '#ffffff',
								'background' => '#000000',
								'text-color' => '#efefef',
							],
							'party'      => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
				];
			},
		];

		yield 'Returns no saved data for empty input' => [
			function () {
				return [
					'form_data' => [
						'tec_category_color_categories' => [],
						'tec_category_colors_blueprint' => [],
					],
					'expected'  => [
						'categories' => [],
						'blueprint'  => [],
					],
				];
			},
		];

		yield 'Skips saving blueprint without selected categories' => [
			function () {
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [], // No categories selected
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [], // No categories should be saved
						'blueprint'  => [], // No blueprint should be saved
					],
				];
			},
		];

		yield 'Saves only selected categories without blueprint' => [
			function () {
				wp_insert_term( 'Family Fun', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'family-fun' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'family-fun' ],
						'tec_category_colors_blueprint' => [],
					],
					'expected'  => [
						'categories' => [ 'family-fun' ],
						'blueprint'  => [],
					],
				];
			},
		];

		yield 'Defaults to empty for invalid color values' => [
			function () {
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'party' ],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => 123,
								'background' => [],
								'text-color' => 'not-a-color',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'party' ],
						'blueprint'  => [
							'party' => [
								'foreground' => '',
								'background' => '',
								'text-color' => '',
							],
						],
					],
				];
			},
		];

		yield 'Saves categories with special character slugs correctly' => [
			function () {
				wp_insert_term( 'Category with @special', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-with-special' ] );
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'category-with-special', 'party' ],
					],
					'expected'  => [
						'categories' => [ 'category-with-special', 'party' ],
					],
				];
			},
		];

		yield 'Handles duplicate slugs in blueprint properly' => [
			function () {
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [ 'party', 'party' ],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [ 'party' ],
						'blueprint'  => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
				];
			},
		];

		yield 'Fails to save without valid nonce' => [
			function () {
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data'  => [
						'tec_category_color_categories' => [ 'party' ],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'   => [
						'categories' => [],
						'blueprint'  => [],
					],
					'skip_nonce' => true,
				];
			},
		];
	}

	/**
	 * @test
	 *
	 * @dataProvider category_colors_save_data_provider
	 *
	 * @return void
	 */
	public function save_category_color_settings() {
		if ( ! wp_verify_nonce( tribe_get_request_var( 'tribe-save-settings' ), 'saving' ) ) {
			return;
		}

		// Get selected categories from the form.
		$selected_categories = $this->process_selected_categories();

		// Get all terms in the taxonomy.
		$all_terms = get_terms(
			[
				'taxonomy'   => $this->taxonomy,
				'hide_empty' => false,
			]
		);

		// Update `tec-event-selected` meta for selected terms.
		foreach ( $all_terms as $term ) {
			if ( in_array( $term->slug, $selected_categories, true ) ) {
				update_term_meta( $term->term_id, 'tec-event-selected', true );
			} else {
				delete_term_meta( $term->term_id, 'tec-event-selected' );
			}
		}

		// Save colors to term meta.
		$submitted_colors = tec_get_request_var( 'tec_category_colors_blueprint', [] );
		foreach ( $submitted_colors as $slug => $colors ) {
			$term = get_term_by( 'slug', $slug, $this->taxonomy );
			if ( $term ) {
				update_term_meta( $term->term_id, self::$meta_foreground_slug, $this->validate_hex_color( $colors['foreground'] ?? '' ) );
				update_term_meta( $term->term_id, self::$meta_background_slug, $this->validate_hex_color( $colors['background'] ?? '' ) );
				update_term_meta( $term->term_id, self::$meta_text_color_slug, $this->validate_hex_color( $colors['text-color'] ?? '' ) );
			}
		}
	}

}
