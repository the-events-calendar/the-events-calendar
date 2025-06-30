<?php
/**
 * Tests for the complete migration flow.
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
use Helper\Teccc_Options_Generator;

/**
 * Class Migration_Flow_Test
 *
 * Tests the complete migration flow from start to finish, ensuring all processors
 * work together correctly.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Migration_Flow_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var Pre_Processor
	 */
	protected Pre_Processor $pre_processor;

	/**
	 * @var Validator
	 */
	protected Validator $validator;

	/**
	 * @var Worker
	 */
	protected Worker $worker;

	/**
	 * @var Post_Processor
	 */
	protected Post_Processor $post_processor;

	/**
	 * @var array<int>
	 */
	protected static array $test_categories = [];

	/**
	 * @before
	 */
	public function setup_test(): void {
		$this->pre_processor  = tribe( Pre_Processor::class );
		$this->validator      = tribe( Validator::class );
		$this->worker         = tribe( Worker::class );
		$this->post_processor = tribe( Post_Processor::class );

		if ( empty( self::$test_categories ) ) {
			$this->create_test_categories();
		}
	}

	/**
	 * @after
	 */
	public function cleanup_test(): void {
		$this->delete_test_categories();
		delete_option( Config::MIGRATION_DATA_OPTION );
		delete_option( Config::MIGRATION_PROCESSING_OPTION );
		delete_option( 'teccc_options' );
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
	 * @test
	 */
	public function should_complete_migration_flow(): void {
		// Set up original settings using the helper
		$original_settings = Teccc_Options_Generator::generate_teccc_options(
			2,
			[
				'all_terms'                 => [
					self::$test_categories[0] => [ 'test-category-1', 'Test Category 1' ],
					self::$test_categories[1] => [ 'test-category-2', 'Test Category 2' ],
				],
				'legend_superpowers'        => true,
				'show_ignored_cats_legend'  => true,
				'custom_legend_css'         => true,
				'reset_show'                => true,
				'category-color-custom-CSS' => true,
			]
		);

		// Add color settings for each category
		$original_settings['test-category-1-border']     = '#ff0000';
		$original_settings['test-category-1-background'] = '#ffffff';
		$original_settings['test-category-1-text']       = '#000000';
		$original_settings['test-category-2-border']     = '#00ff00';
		$original_settings['test-category-2-background'] = '#ffffff';
		$original_settings['test-category-2-text']       = '#000000';

		update_option( 'teccc_options', $original_settings );

		// Step 1: Pre-processing
		$pre_result = $this->pre_processor->process();
		$this->assertTrue( $pre_result, 'Pre-processing should succeed' );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$preprocessing_completed, $status['status'] );

		// Add the chk_default_options_db setting to the migration data
		$migration_data                                       = get_option( Config::MIGRATION_DATA_OPTION );
		$migration_data['settings']['chk_default_options_db'] = '1';
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Step 2: Validation
		$validation_result = $this->validator->process();
		$this->assertTrue( $validation_result, 'Validation should succeed' );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$validation_completed, $status['status'] );

		// Step 3: Worker
		$worker_result = $this->worker->process();
		$this->assertTrue( $worker_result, 'Worker should succeed' );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$execution_completed, $status['status'] );

		// Step 4: Post-processing
		$post_result = $this->post_processor->process();
		$this->assertTrue( $post_result, 'Post-processing should succeed' );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$postprocessing_completed, $status['status'] );

		// Verify final state
		$migration_data = get_option( Config::MIGRATION_DATA_OPTION );
		$this->assertNotEmpty( $migration_data['categories'], 'Migration data should have categories' );
		$this->assertNotEmpty( $migration_data['settings'], 'Migration data should have settings' );

		// Verify settings were migrated correctly
		$existing_settings = get_option( Tribe__Events__Main::OPTIONNAME );
		$this->assertEquals(
			$migration_data['settings']['category-color-legend-show'],
			$existing_settings['category-color-legend-show'],
			'Legend show settings should match'
		);
		$this->assertEquals(
			$migration_data['settings']['category-color-legend-superpowers'],
			$existing_settings['category-color-legend-superpowers'],
			'Legend superpowers setting should match'
		);

		// Verify category meta was migrated correctly
		foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
			$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
			$actual_meta   = $category_meta->get();

			foreach ( $meta_data as $meta_key => $expected_value ) {
				if ( $meta_key === 'taxonomy_id' ) {
					continue;
				}

				// Check if the meta key exists
				$this->assertArrayHasKey(
					$meta_key,
					$actual_meta,
					"Meta key '{$meta_key}' should exist for category {$category_id}"
				);
			}
		}
	}
}
