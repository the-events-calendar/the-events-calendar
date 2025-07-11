<?php
/**
 * Tests for the Worker class.
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use Closure;
use Generator;
use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use Helper\Teccc_Options_Generator;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main;

/**
 * Class Worker_Test
 *
 * @since   6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Worker_Test extends WPTestCase {
	use With_Uopz;
	use MatchesSnapshots;

	/**
	 * @var Worker
	 */
	private Worker $processor;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();

		$this->processor = tribe( Worker::class );

		// Reset migration status before each test
		Status::update_migration_status( Status::$validation_completed );

		// Create test categories
		$this->create_test_categories();
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		delete_option( Config::MIGRATION_DATA_OPTION );
		delete_option( Config::MIGRATION_PROCESSING_OPTION );
		Status::update_migration_status( Status::$not_started );
		$this->delete_test_categories();
	}

	/**
	 * Create test categories.
	 *
	 * @since 6.14.0
	 */
	protected function create_test_categories(): void {
		$categories = [
			1 => 'Category 1',
			2 => 'Category 2',
			3 => 'Category 3',
			4 => 'Category 4',
			5 => 'Category 5',
		];

		foreach ( $categories as $id => $name ) {
			wp_insert_term(
				$name,
				Tribe__Events__Main::TAXONOMY,
				[
					'term_id' => $id,
				]
			);
		}
	}

	/**
	 * Delete test categories.
	 *
	 * @since 6.14.0
	 */
	protected function delete_test_categories(): void {
		$categories = [
			1 => 'Category 1',
			2 => 'Category 2',
			3 => 'Category 3',
			4 => 'Category 4',
			5 => 'Category 5',
		];

		foreach ( $categories as $id => $name ) {
			wp_delete_term( $id, Tribe__Events__Main::TAXONOMY );
		}
	}

	/**
	 * Data provider for test cases.
	 *
	 * @since 6.14.0
	 *
	 * @return Generator
	 */
	public function provide_test_cases(): Generator {
		$setup_valid_data = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id'                     => 1,
						'tec-events-cat-colors-primary'   => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text'      => '#000000',
					],
					'2' => [
						'taxonomy_id'                     => 2,
						'tec-events-cat-colors-primary'   => '#00ff00',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text'      => '#000000',
					],
				],
				'settings'   => [
					'category-color-legend-show'            => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers'     => '1',
					'category-color-show-hidden-categories' => '1',
					'category-color-custom-CSS'             => '1',
					'category-color-reset-button'           => '1',
				],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		$setup_empty_data = function () {
			update_option( Config::MIGRATION_DATA_OPTION, [] );
		};

		$setup_invalid_data = function () {
			$migration_data = [
				'categories' => [
					'999999' => [ // Invalid category ID
					              'taxonomy_id'                     => 999999,
					              'tec-events-cat-colors-primary'   => '#ff0000',
					              'tec-events-cat-colors-secondary' => '#ffffff',
					              'tec-events-cat-colors-text'      => '#000000',
					],
				],
				'settings'   => [
					'dummy-setting' => '1',
				],
			];

			update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		};

		yield 'valid data' => [
			$setup_valid_data,
			true,
			Status::$execution_completed,
		];

		yield 'empty data' => [
			$setup_empty_data,
			false,
			Status::$execution_skipped,
		];

		yield 'invalid data' => [
			$setup_invalid_data,
			true,
			Status::$execution_completed,
		];
	}

	/**
	 * @test
	 * @dataProvider provide_test_cases
	 */
	public function should_process_migration( Closure $setup, bool $expected_result, string $expected_status ): void {
		$setup();

		// Copy migration data to processing option after setup closure runs
		$migration_data = get_option( Config::MIGRATION_DATA_OPTION, [] );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Debug assertions
		$this->assertTrue( $this->processor->is_runnable(), 'Worker should be runnable' );
		$processing_data = $this->processor->get_processing_data();

		// Only check for categories and settings if we expect success
		if ( $expected_result ) {
			$this->assertNotEmpty( $processing_data['categories'], 'Processing data should have categories' );
			$this->assertNotEmpty( $processing_data['settings'], 'Processing data should have settings' );
		}

		$result = $this->processor->process();

		$this->assertEquals( $expected_result, $result );
		$status = Status::get_migration_status();
		$this->assertEquals( $expected_status, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_be_runnable_when_validation_completed(): void {
		$this->assertTrue( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_not_be_runnable_when_execution_in_progress(): void {
		Status::update_migration_status( Status::$execution_in_progress );
		$this->assertFalse( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 * Confirms that hidden categories are migrated to tec-events-cat-colors-hidden term meta after full migration.
	 */
	public function it_migrates_hidden_categories_to_term_meta() {
		// Create a term with a known slug.
		$term_id = $this->factory()->term->create(
			[
				'taxonomy' => 'tribe_events_cat',
				'name'     => 'Library',
				'slug'     => 'library',
			]
		);

		// Simulate legacy teccc_options with a hide flag for this category.
		$teccc_options = [
			'terms'        => [
				$term_id => [ 'library', 'Library' ],
			],
			'all_terms'    => [
				$term_id => [ 'library', 'Library' ],
			],
			'library-hide' => true,
		];
		update_option( 'teccc_options', $teccc_options );

		// Run the pre-processor to prepare migration data.
		tribe( Pre_Processor::class )->process();
		// Run the worker to actually write the meta.
		tribe( Worker::class )->process();

		// Assert the new meta is set to 1 (hidden).
		$this->assertEquals( '1', get_term_meta( $term_id, 'tec-events-cat-colors-hidden', true ) );
	}

	/**
	 * @test
	 * Confirms that the custom CSS option is migrated to category-color-custom-CSS in settings after full migration.
	 */
	public function it_migrates_custom_css_option_to_settings() {
		// Simulate legacy teccc_options with custom_legend_css enabled.
		$teccc_options = [
			'custom_legend_css' => true,
		];
		update_option( 'teccc_options', $teccc_options );

		// Run the pre-processor to prepare migration data.
		tribe( Pre_Processor::class )->process();
		// Run the worker to actually write the settings.
		tribe( Worker::class )->process();

		// Get the migrated settings from the processing option.
		$migrated_data = get_option( \TEC\Events\Category_Colors\Migration\Config::MIGRATION_PROCESSING_OPTION, [] );
		$settings      = $migrated_data['settings'] ?? [];

		// Assert the new setting is set to '1' (enabled).
		$this->assertEquals( '1', $settings['category-color-custom-css'] ?? null );
	}
}
