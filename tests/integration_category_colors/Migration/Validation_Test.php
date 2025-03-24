<?php
/**
 * Tests for the validation step of the category colors migration.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;
use WP_Error;

/**
 * Class Validation_Test
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Validation_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Validator
	 */
	protected $validator;

	/**
	 * @before
	 */
	public function setup_test() {
		$this->validator = tribe( Validator::class );
		$this->create_test_categories();
		Status::update_migration_status( Status::$preprocessing_completed );

		// Add original settings
		$original_settings = [
			'category-color-show-hidden-categories' => true,
			'category-color-legend-show' => true,
			'category-color-legend-position' => 'top',
			'category-color-legend-style' => 'list',
			'category-color-legend-title' => 'Categories',
		];
		update_option( 'teccc_options', $original_settings );
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		delete_option( Config::$migration_data_option );
		delete_option( 'teccc_options' );
		$this->delete_test_categories();
		Status::reset_migration_status();
	}

	/**
	 * Create test categories.
	 */
	protected function create_test_categories() {
		$categories = [
			1 => 'Category 1',
			2 => 'Category 2',
		];

		foreach ( $categories as $id => $name ) {
			wp_insert_term( $name, Handler::$taxonomy, [
				'term_id' => $id,
			] );
		}
	}

	/**
	 * Delete test categories.
	 */
	protected function delete_test_categories() {
		$categories = [
			1 => 'Category 1',
			2 => 'Category 2',
		];

		foreach ( $categories as $id => $name ) {
			wp_delete_term( $id, Handler::$taxonomy );
		}
	}

	/**
	 * @dataProvider process_data_provider
	 * @test
	 */
	public function process( array $migration_data, array $expected ) {
		// Save migration data
		update_option( Config::$migration_data_option, $migration_data );

		// Verify data was saved correctly
		$saved_data = get_option( Config::$migration_data_option );
		$this->assertEquals( $migration_data, $saved_data, 'Migration data was not saved correctly' );

		$result = $this->validator->process();

		if ( isset( $expected['error'] ) ) {
			$this->assertEquals( Status::$validation_failed, Status::get_migration_status()['status'] );
		} else {
			$this->assertTrue( $result );
			$this->assertEquals( Status::$validation_completed, Status::get_migration_status()['status'] );
			$this->assertEquals( $expected['categories'], $migration_data['categories'] );
			$this->assertEquals( $expected['settings'], $migration_data['settings'] );
		}
	}

	/**
	 * Data provider for process test.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function process_data_provider(): array {
		return [
			'valid_data' => [
				'migration_data' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
						'2' => [
							'tec-events-cat-colors-secondary' => '#00ff00',
							'tec-events-cat-colors-text' => '#000000',
							'taxonomy_id' => 2,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
						'2' => [
							'tec-events-cat-colors-secondary' => '#00ff00',
							'tec-events-cat-colors-text' => '#000000',
							'taxonomy_id' => 2,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
			],
			'empty_categories' => [
				'migration_data' => [
					'categories' => [],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'error' => 'No categories found in migration data.',
				],
			],
			'invalid_category_data' => [
				'migration_data' => [
					'categories' => [
						'1' => 'invalid',
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'error' => 'Invalid category data structure.',
				],
			],
			'missing_required_fields_pass_gracefully' => [
				'migration_data' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							// Missing text color and taxonomy_id
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
			],
			'invalid_hex_colors' => [
				'migration_data' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => 'not-a-color',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => 'not-a-color',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
			],
			'empty_hex_colors' => [
				'migration_data' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '',
							'tec-events-cat-colors-text' => '',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '',
							'tec-events-cat-colors-text' => '',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
						'category-color-legend-show' => true,
						'category-color-legend-position' => 'top',
						'category-color-legend-style' => 'list',
						'category-color-legend-title' => 'Categories',
					],
					'ignored_terms' => [],
				],
			],
			'invalid_settings' => [
				'migration_data' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
					],
					'settings' => 'invalid',
					'ignored_terms' => [],
				],
				'expected' => [
					'error' => 'Invalid settings data structure.',
				],
			],
		];
	}

	/**
	 * @test
	 */
	public function is_runnable() {
		// Test when preprocessing is completed
		Status::update_migration_status( Status::$preprocessing_completed );
		$this->assertTrue( $this->validator->is_runnable() );

		// Test when validation failed
		Status::update_migration_status( Status::$validation_failed );
		$this->assertTrue( $this->validator->is_runnable() );

		// Test when validation is completed
		Status::update_migration_status( Status::$validation_completed );
		$this->assertFalse( $this->validator->is_runnable() );

		// Test when validation is in progress
		Status::update_migration_status( Status::$validation_in_progress );
		$this->assertFalse( $this->validator->is_runnable() );
	}
} 