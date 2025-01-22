<?php

namespace TEC\Events\Category_Colors\Tests;

use Closure;
use Codeception\TestCase\WPTestCase;
use ReflectionClass;
use TEC\Events\Category_Colors\Settings;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main;

class SettingsTest extends WPTestCase {
	use With_Uopz;

	/**
	 * Data provider for edge case scenarios.
	 *
	 * @return \Generator
	 */
	public function initialize_terms_edgecases_data(): \Generator {
		yield 'term with special characters' => [
			function () {
				wp_insert_term( 'Category@123', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-special' ] );

				return [
					'expected_terms' => [
						'category-special' => 'Category@123',
					],
				];
			},
		];
		yield 'caching or state consistency' => [
			function () {
				$term = wp_insert_term( 'Category 1', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'category-1' ] );

				add_filter( 'tec_category_colors_delete_terms', fn() => [ 'category-1' ] );

				return [
					'expected_terms'         => [],
					'expected_ignored_terms' => [],
				];
			},
		];
		yield 'filter hidden terms' => [
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

		yield 'term structure validation' => [
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

		yield 'no terms available' => [
			function () {
				return [
					'expected_terms'         => [],
					'expected_ignored_terms' => [],
				];
			},
		];
	}

	/**
	 * @test         `initialize_terms` with edge cases.
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
		yield 'valid data' => [
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

		yield 'invalid categories' => [
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

		yield 'duplicate categories' => [
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

		yield 'partially empty blueprint' => [
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

		yield 'mixed valid and invalid categories' => [
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

		yield 'empty input' => [
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

		yield 'blueprint without categories' => [
			function () {
				wp_insert_term( 'Party', Tribe__Events__Main::TAXONOMY, [ 'slug' => 'party' ] );

				return [
					'form_data' => [
						'tec_category_color_categories' => [],
						'tec_category_colors_blueprint' => [
							'party' => [
								'foreground' => '#ff0000',
								'background' => '#00ff00',
								'text-color' => '#0000ff',
							],
						],
					],
					'expected'  => [
						'categories' => [],
						'blueprint'  => [],
					],
				];
			},
		];

		yield 'categories without blueprint' => [
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

		yield 'non-string and invalid blueprint values' => [
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

		yield 'special characters in slugs' => [
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

		yield 'duplicate slugs in blueprint' => [
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

		yield 'no nonce' => [
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
	 * @test         Save and retrieve category colors.
	 *
	 * @dataProvider category_colors_save_data_provider
	 *
	 * @param Closure $fixture The fixture closure to set up the scenario.
	 */
	public function save_and_get_category_colors( Closure $fixture ): void {
		// Set up the fixture and get the scenario data.
		$scenario  = $fixture();
		$form_data = $scenario['form_data'];
		$expected  = $scenario['expected'];

		// Simulate request data.
		$_POST = array_merge( $_POST, $form_data );

		// Handle the `wp_verify_nonce` behavior based on the test case.
		$this->set_fn_return(
			'wp_verify_nonce',
			isset( $scenario['skip_nonce'] ) && $scenario['skip_nonce'] === true ? false : true
		);

		// Save category colors.
		$settings = tribe( \TEC\Events\Category_Colors\Settings::class );
		$settings->save_category_color_settings();

		// Retrieve and assert saved categories.
		$saved_categories = get_option( 'tec_category_color_categories', [] );
		$this->assertSame(
			$expected['categories'],
			$saved_categories,
			'Saved categories do not match expected values.'
		);

		// Retrieve and assert saved blueprints, if applicable.
		if ( isset( $expected['blueprint'] ) ) {
			$saved_blueprint = get_option( 'tec_category_color_blueprint', [] );
			$this->assertSame(
				$expected['blueprint'],
				$saved_blueprint,
				'Saved blueprints do not match expected values.'
			);
		}

		// Clean up options to avoid test interference.
		delete_option( 'tec_category_color_categories' );
		delete_option( 'tec_category_color_blueprint' );
	}

}
