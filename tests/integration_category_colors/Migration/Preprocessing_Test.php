<?php
/**
 * Tests for the preprocessing step of the category colors migration.
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
 * Class Preprocessing_Test
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Preprocessing_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Pre_Processor
	 */
	protected $pre_processor;

	/**
	 * @var array<string, mixed>
	 */
	protected $teccc_options;

	/**
	 * @before
	 */
	public function setup_test() {
		$this->pre_processor = tribe( Pre_Processor::class );
		$this->teccc_options = $this->get_teccc_options();
	}

	/**
	 * @after
	 */
	public function cleanup_test() {
		delete_option( 'teccc_options' );
		delete_option( Config::$migration_data_option );
	}

	/**
	 * @dataProvider process_data_provider
	 * @test
	 */
	public function process( array $options, array $expected ) {
		update_option( 'teccc_options', $options );

		$result = $this->pre_processor->process();

		$this->assertNotInstanceOf( WP_Error::class, $result );
		$this->assertEquals( Status::$preprocessing_completed, Status::get_migration_status()['status'] );

		$migration_data = get_option( Config::$migration_data_option );
		$this->assertEquals( $expected['categories'], $migration_data['categories'] );
		$this->assertEquals( $expected['settings'], $migration_data['settings'] );
	}

	/**
	 * Test performance with a large dataset.
	 *
	 * @test
	 */
	public function process_large_dataset() {
		$options = $this->generate_large_dataset( 100 );
		update_option( 'teccc_options', $options );

		$start_time = microtime( true );
		$result = $this->pre_processor->process();
		$end_time = microtime( true );

		$this->assertNotInstanceOf( WP_Error::class, $result );
		$this->assertEquals( Status::$preprocessing_completed, Status::get_migration_status()['status'] );

		$migration_data = get_option( Config::$migration_data_option );
		$this->assertCount( 100, $migration_data['categories'] );
	}

	/**
	 * Generate a large dataset for performance testing.
	 *
	 * @param int $count Number of categories to generate.
	 * @return array<string, mixed>
	 */
	protected function generate_large_dataset( int $count ): array {
		$options = [
			'terms' => [],
			'font_weight' => 'Bold',
			'show_ignored_cats_legend' => '1',
		];

		for ( $i = 1; $i <= $count; $i++ ) {
			$slug = "category-{$i}";
			$options['terms'][ (string) $i ] = [ $slug, "Category {$i}" ];
			$options[ "{$slug}-background" ] = sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
			$options[ "{$slug}_text" ] = sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
		}

		return $options;
	}

	/**
	 * Data provider for process test.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function process_data_provider(): array {
		return [
			'basic_migration' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
						'2' => ['category-2', 'Category 2'],
					],
					'category-1-background' => '#ff0000',
					'category-1_text' => '#ffffff',
					'category-2-background' => '#00ff00',
					'category-2_text' => '#000000',
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
					'settings' => [],
				],
			],
			'with_settings' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
					],
					'category-1-background' => '#ff0000',
					'category-1_text' => '#ffffff',
					'font_weight' => 'Bold',
					'show_ignored_cats_legend' => '1',
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							'tec-events-cat-colors-text' => '#ffffff',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [
						'category-color-show-hidden-categories' => true,
					],
				],
			],
			'empty_colors' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
					],
					'category-1-background' => '',
					'category-1_text' => '',
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '',
							'tec-events-cat-colors-text' => '',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [],
				],
			],
			'no_color_text' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
					],
					'category-1-background' => '#ff0000',
					'category-1_text' => 'no_color',
				],
				'expected' => [
					'categories' => [
						'1' => [
							'tec-events-cat-colors-secondary' => '#ff0000',
							'tec-events-cat-colors-text' => '',
							'taxonomy_id' => 1,
						],
					],
					'settings' => [],
				],
			],
		];
	}

	/**
	 * @dataProvider process_error_data_provider
	 */
	public function process_errors( array $options, string $expected_error ) {
		update_option( 'teccc_options', $options );

		$result = $this->pre_processor->process();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( $expected_error, $result->get_error_message() );
		$this->assertEquals( Status::$preprocessing_failed, Status::get_migration_status()['status'] );
	}

	/**
	 * Data provider for process_errors test.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function process_error_data_provider(): array {
		return [
			'empty_options' => [
				'options' => [],
				'expected_error' => 'No category colors data found to migrate.',
			],
			'invalid_terms' => [
				'options' => [
					'terms' => 'invalid',
				],
				'expected_error' => 'Invalid category colors data structure.',
			],
			'invalid_hex_color' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
					],
					'category-1-background' => 'not-a-color',
					'category-1_text' => '#ffffff',
				],
				'expected_error' => 'Invalid hex color format for category 1.',
			],
			'nonexistent_category' => [
				'options' => [
					'terms' => [
						'999999' => ['nonexistent', 'Nonexistent Category'],
					],
					'nonexistent-background' => '#ff0000',
					'nonexistent_text' => '#ffffff',
				],
				'expected_error' => 'Category with ID 999999 does not exist.',
			],
			'malformed_term_data' => [
				'options' => [
					'terms' => [
						'1' => ['category-1'], // Missing name
					],
					'category-1-background' => '#ff0000',
					'category-1_text' => '#ffffff',
				],
				'expected_error' => 'Invalid term data structure for category 1.',
			],
			'duplicate_category_slugs' => [
				'options' => [
					'terms' => [
						'1' => ['category-1', 'Category 1'],
						'2' => ['category-1', 'Category 2'], // Duplicate slug
					],
					'category-1-background' => '#ff0000',
					'category-1_text' => '#ffffff',
				],
				'expected_error' => 'Duplicate category slug found: category-1.',
			],
		];
	}

	/**
	 * Get the teccc options for testing.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_teccc_options(): array {
		return [
			'terms' => [
				'618' => ['category-76', 'Category 76'],
				'619' => ['category-77', 'Category 77'],
				'620' => ['category-78', 'Category 78'],
			],
			'differentname-hide' => '',
			'differentname-border_none' => '',
			'differentname-border' => '#17a45b',
			'differentname-background_none' => '',
			'differentname-background' => '#7b167f',
			'differentname_text' => '#000',
			'testcategory1-hide' => '',
			'testcategory1-border_none' => '',
			'testcategory1-border' => '#b39028',
			'testcategory1-background_none' => '',
			'testcategory1-background' => '#f1e330',
			'testcategory1_text' => 'no_color',
			'testcategory2-hide' => '',
			'testcategory2-border_none' => '',
			'testcategory2-border' => '#dcad0a',
			'testcategory2-background_none' => '',
			'testcategory2-background' => '#a5513e',
			'testcategory2_text' => 'no_color',
			'featured-event_none' => '',
			'featured-event' => '#45be86',
			'font_weight' => 'Bold',
			'show_ignored_cats_legend' => '1',
			'custom_legend_css' => '1',
			'reset_show' => '',
			'reset_label' => '',
			'reset_url' => '',
			'legend_superpowers' => '1',
			'chk_default_options_db' => '1',
			'add_legend' => ['list', 'month', 'day', 'summary'],
		];
	}
} 