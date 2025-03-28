<?php
/**
 * Tests for the Post_Processor class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Event_Category_Meta;
use Tribe__Events__Main;
use Closure;

/**
 * Class Post_Processor_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Post_Processor_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var Post_Processor
	 */
	protected Post_Processor $processor;

	/**
	 * @var array<int>
	 */
	protected static array $test_categories = [];

	/**
	 * @before
	 */
	public function setup_test(): void {
		$this->processor = tribe( Post_Processor::class );
		if ( empty( self::$test_categories ) ) {
			$this->create_test_categories();
		}
	}

	/**
	 * @after
	 */
	public function cleanup_test(): void {
		$this->delete_test_categories();
		delete_option( Config::$migration_data_option );
		delete_option( Config::$migration_processing_option );
		Status::reset_migration_status();
	}

	/**
	 * Creates test categories for testing.
	 *
	 * @since TBD
	 */
	protected function create_test_categories(): void {
		$term1 = wp_insert_term( 'Test Category 1', 'tribe_events_cat' );
		$term2 = wp_insert_term( 'Test Category 2', 'tribe_events_cat' );

		if ( ! is_wp_error( $term1 ) ) {
			self::$test_categories[] = $term1['term_id'];
		} else {
			$this->fail( 'Failed to create first test category: ' . $term1->get_error_message() );
		}
		if ( ! is_wp_error( $term2 ) ) {
			self::$test_categories[] = $term2['term_id'];
		} else {
			$this->fail( 'Failed to create second test category: ' . $term2->get_error_message() );
		}

		$this->assertNotEmpty( self::$test_categories, 'Test categories should not be empty' );
		$this->assertCount( 2, self::$test_categories, 'Should have created 2 test categories' );
	}

	/**
	 * Deletes test categories.
	 *
	 * @since TBD
	 */
	protected function delete_test_categories(): void {
		foreach ( self::$test_categories as $category_id ) {
			wp_delete_term( $category_id, 'tribe_events_cat' );
		}
		self::$test_categories = [];
	}

	/**
	 * Data provider for test cases.
	 *
	 * @since TBD
	 *
	 * @return array<string, array{0: Closure, 1: bool, 2: string}>
	 */
	public function provide_test_cases(): array {
		$setup_basic_validation = function ( Post_Processor $processor ) {
			$migration_data = [
				'categories' => [
					(string) self::$test_categories[0] => [
						'taxonomy_id'                     => self::$test_categories[0],
						'tec-events-cat-colors-primary'   => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text'      => '#000000',
					],
					(string) self::$test_categories[1] => [
						'taxonomy_id'                     => self::$test_categories[1],
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

			// Set up original settings
			$original_settings = [
				'add_legend'               => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
				'legend_superpowers'       => '1',
				'show_ignored_cats_legend' => '1',
				'custom_legend_css'        => '1',
				'reset_show'               => '1',
			];
			update_option( 'teccc_options', $original_settings );

			update_option( Config::$migration_data_option, $migration_data );
			update_option(
				Config::$migration_processing_option,
				[
					'settings'   => [],
					'categories' => [],
				]
			);
			Status::update_migration_status( Status::$execution_completed );

			// Set up the actual meta values
			foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
				$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
				foreach ( $meta_data as $meta_key => $meta_value ) {
					if ( $meta_key !== 'taxonomy_id' ) {
						$category_meta->set( $meta_key, $meta_value );
					}
				}
				$category_meta->save();
			}
		};

		$setup_dry_run = function ( Post_Processor $processor ) {
			$migration_data = [
				'categories' => [
					(string) self::$test_categories[0] => [
						'taxonomy_id'                     => self::$test_categories[0],
						'tec-events-cat-colors-primary'   => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text'      => '#000000',
					],
				],
				'settings'   => [
					'category-color-legend-show' => [ 'month', 'list', 'day' ],
				],
			];

			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, [] );
			Status::update_migration_status( Status::$execution_completed );

			$processor->set_dry_run( true );
		};

		$setup_no_data = function ( Post_Processor $processor ) {
			update_option( Config::$migration_data_option, [] );
			update_option( Config::$migration_processing_option, [] );
			Status::update_migration_status( Status::$execution_completed );
		};

		$setup_missing_meta = function ( Post_Processor $processor ) {
			$migration_data = [
				'categories' => [
					(string) self::$test_categories[0] => [
						'taxonomy_id'                     => self::$test_categories[0],
						'tec-events-cat-colors-primary'   => '#ff0000',
						'tec-events-cat-colors-secondary' => '#ffffff',
						'tec-events-cat-colors-text'      => '#000000',
					],
				],
				'settings'   => [],
			];

			update_option( Config::$migration_data_option, $migration_data );
			update_option( Config::$migration_processing_option, [] );
			Status::update_migration_status( Status::$execution_completed );
			// Don't set up any meta values to test missing meta detection
		};

		return [
			'basic validation' => [
				$setup_basic_validation,
				true,
				Status::$postprocessing_completed,
			],
			'dry run'          => [
				$setup_dry_run,
				true,
				Status::$postprocessing_completed,
			],
			'no data'          => [
				$setup_no_data,
				false,
				Status::$postprocessing_completed,
			],
			'missing meta'     => [
				$setup_missing_meta,
				false,
				Status::$postprocessing_failed,
			],
		];
	}

	/**
	 * @test
	 * @dataProvider provide_test_cases
	 */
	public function should_process_post_validation( Closure $setup, bool $expected_result, string $expected_status ): void {
		$this->assertNotEmpty( self::$test_categories, 'Test categories should not be empty before running setup' );
		$setup( $this->processor );

		$result = $this->processor->process();

		$this->assertEquals( $expected_result, $result );
		$status = Status::get_migration_status();
		$this->assertEquals( $expected_status, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_validate_execution_state(): void {
		$migration_data = [
			'categories' => [
				(string) self::$test_categories[0] => [
					'taxonomy_id'                   => self::$test_categories[0],
					'tec-events-cat-colors-primary' => '#ff0000',
				],
			],
			'settings'   => [],
		];

		update_option( Config::$migration_data_option, $migration_data );
		update_option(
			Config::$migration_processing_option,
			[
				'settings'   => [],
				'categories' => [],
			]
		);

		// Test when execution is not completed
		Status::update_migration_status( Status::$execution_in_progress );
		$this->assertFalse( $this->processor->is_runnable() );

		// Test when execution is completed
		Status::update_migration_status( Status::$execution_completed );
		$this->assertTrue( $this->processor->is_runnable() );

		// Test when post-processing failed
		Status::update_migration_status( Status::$postprocessing_failed );
		$this->assertTrue( $this->processor->is_runnable() );
	}

	/**
	 * @test
	 */
	public function should_handle_remaining_categories(): void {
		$migration_data = [
			'categories' => [
				(string) self::$test_categories[0] => [
					'taxonomy_id'                   => self::$test_categories[0],
					'tec-events-cat-colors-primary' => '#ff0000',
				],
			],
			'settings'   => [],
		];

		update_option( Config::$migration_data_option, $migration_data );
		update_option( Config::$migration_processing_option, $migration_data );
		Status::update_migration_status( Status::$execution_completed );

		$this->assertFalse( $this->processor->is_runnable() );
	}
}
