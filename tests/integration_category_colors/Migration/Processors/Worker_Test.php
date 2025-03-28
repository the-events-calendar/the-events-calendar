<?php

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
		delete_option( Config::$migration_data_option );
		delete_option( Config::$migration_processing_option );
		Status::update_migration_status( Status::$not_started );
		$this->delete_test_categories();
	}

	/**
	 * Create test categories.
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
			wp_insert_term( $name, Tribe__Events__Main::TAXONOMY, [
				'term_id' => $id,
			] );
		}
	}

	/**
	 * Delete test categories.
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
	 * Data provider for worker scenarios
	 *
	 * @return Generator
	 */
	public function worker_data_provider(): Generator {
		$setup_basic_categories = function () {
			$migration_data = [
				'categories' => [
					'1' => [
						'taxonomy_id' => 1,
						'tec-events-cat-colors-primary' => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
					'2' => [
						'taxonomy_id' => 2,
						'tec-events-cat-colors-primary' => '#00ff00',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
				],
				'settings' => [
					'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers' => '1',
					'category-color-show-hidden-categories' => '1',
				],
			];

			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, $migration_data );
		};

		$setup_large_dataset = function () {
			$categories = [];
			for ( $i = 1; $i <= 5; $i++ ) {
				$categories[ (string) $i ] = [
					'taxonomy_id' => $i,
					'tec-events-cat-colors-primary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
					'tec-events-cat-colors-secondary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
					'tec-events-cat-colors-text' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
				];
			}

			$migration_data = [
				'categories' => $categories,
				'settings' => [
					'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers' => '1',
					'category-color-show-hidden-categories' => '1',
				],
			];

			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, $migration_data );
		};

		$setup_empty_data = function () {
			$migration_data = [];
			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, $migration_data );
		};

		$setup_invalid_category = function () {
			$migration_data = [
				'categories' => [
					'999999' => [ // Non-existent category
						'taxonomy_id' => 999999,
						'tec-events-cat-colors-primary' => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text' => '#000000',
					],
				],
				'settings' => [
					'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
					'category-color-legend-superpowers' => '1',
					'category-color-show-hidden-categories' => '1',
				],
			];

			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, $migration_data );
		};

		yield 'basic categories with settings' => [
			$setup_basic_categories,
			true,
			Status::$execution_completed,
		];

		yield 'large dataset with batching' => [
			$setup_large_dataset,
			true,
			Status::$execution_completed,
		];

		yield 'empty data' => [
			$setup_empty_data,
			false,
			Status::$execution_skipped,
		];

		yield 'invalid category' => [
			$setup_invalid_category,
			true,
			Status::$execution_completed,
		];
	}

	/**
	 * @test
	 * @dataProvider worker_data_provider
	 */
	public function should_process_migration( Closure $setup, bool $expected_result, string $expected_status ): void {
		$setup();

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
	 */
	public function should_fire_worker_hooks(): void {
		$pre_hook_fired  = false;
		$post_hook_fired = false;
		$post_hook_data  = null;

		// Set up valid data for the test
		$migration_data = [
			'categories' => [
				'1' => [
					'taxonomy_id' => 1,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [
				'category-color-legend-show' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
				'category-color-legend-superpowers' => '1',
				'category-color-show-hidden-categories' => '1',
			],
		];

		update_option( Config::$migration_data_option, $migration_data );

		add_action(
			'tec_events_category_colors_migration_runner_start',
			function () use ( &$pre_hook_fired ) {
				$pre_hook_fired = true;
			}
		);

		add_action(
			'tec_events_category_colors_migration_runner_end',
			function ( $data ) use ( &$post_hook_fired, &$post_hook_data ) {
				$post_hook_fired = true;
				$post_hook_data  = $data;
			}
		);

		$this->processor->process();

		$this->assertTrue( $pre_hook_fired );
		$this->assertTrue( $post_hook_fired );
		$this->assertIsBool( $post_hook_data );
	}

	/**
	 * @test
	 */
	public function should_process_categories_in_batches(): void {
		// Set up a large dataset
		$categories = [];
		for ( $i = 1; $i <= 5; $i++ ) {
			$categories[ (string) $i ] = [
				'taxonomy_id' => $i,
				'tec-events-cat-colors-primary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
				'tec-events-cat-colors-secondary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
				'tec-events-cat-colors-text' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
			];
		}

		$migration_data = [
			'categories' => $categories,
			'settings' => [],
		];

		update_option( Config::$migration_data_option, $migration_data );

		// Set a small batch size for testing
		$this->set_class_fn_return( Worker::class, 'get_batch_size', 2 );

		// Process first batch
		$result = $this->processor->process();
		$this->assertTrue( $result );

		// Check processing data was updated
		$processing_data = get_option( Config::$migration_processing_option );
		$this->assertCount( 3, $processing_data['categories'] ); // 5 - 2 processed

		// Process second batch
		$result = $this->processor->process();
		$this->assertTrue( $result );

		// Check processing data was updated again
		$processing_data = get_option( Config::$migration_processing_option );
		$this->assertCount( 1, $processing_data['categories'] ); // 3 - 2 processed

		// Process final batch
		$result = $this->processor->process();
		$this->assertTrue( $result );

		// Check all categories were processed
		$processing_data = get_option( Config::$migration_processing_option );
		$this->assertEmpty( $processing_data['categories'] );
	}

	/**
	 * @test
	 */
	public function should_handle_batch_processing_errors(): void {
		// Set up test data
		$migration_data = [
			'categories' => [
				'1' => [
					'taxonomy_id' => 1,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
				'2' => [
					'taxonomy_id' => 2,
					'tec-events-cat-colors-primary' => '#00ff00',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [],
		];

		update_option( Config::$migration_data_option, $migration_data );

		// Mock get_terms to return an error
		$this->set_fn_return( 'get_terms', new \WP_Error( 'test_error', 'Test error message' ) );

		$result = $this->processor->process();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( Status::$execution_failed, Status::get_migration_status()['status'] );
	}
} 