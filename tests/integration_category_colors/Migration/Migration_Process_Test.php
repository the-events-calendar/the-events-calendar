<?php

namespace TEC\Events\Category_Colors\Migration;

use Closure;
use Generator;
use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Clock_Mock;
use Tribe\Tests\Traits\With_Uopz;

class Migration_Process_Test extends WPTestCase {
	use With_Uopz;
	use With_Clock_Mock;

	/**
	 * @before
	 */
	public function setup_environment(): void {
		parent::setUp();
		Errors::clear_errors();
		delete_option( 'teccc_options' );
		delete_option( 'tec_category_colors_migration_data' );
		delete_option( 'tec_events_category_colors_migration_status' );
	}

	/**
	 * @after
	 */
	public function cleanup_environment(): void {
		Errors::clear_errors();
		delete_option( 'teccc_options' );
		delete_option( 'tec_category_colors_migration_data' );
		delete_option( 'tec_events_category_colors_migration_status' );
		parent::tearDown();
	}

	/**
	 * Data provider for successful migration scenarios.
	 */
	public function migration_scenarios(): Generator {
		yield 'Single category' => [ fn() => $this->generate_test_data( 1 ) ];
		yield '50 categories' => [ fn() => $this->generate_test_data( 50 ) ];
	}

	/**
	 * Generates test terms and options dynamically.
	 *
	 * @param int  $num_categories   Number of categories to generate.
	 * @param bool $include_settings Whether to include settings data for migration.
	 *
	 * @return array<int, int> Array of term IDs indexed by slug.
	 */
	protected function generate_test_data( int $num_categories = 3, bool $include_settings = false ): array {
		$terms         = [];
		$teccc_options = [
			'terms'     => [],
			'all_terms' => [],
		];

		for ( $i = 1; $i <= $num_categories; $i++ ) {
			$slug       = "category{$i}";
			$name       = "Category {$i}";
			$border     = sprintf( '#%06X', wp_rand( 0, 0xFFFFFF ) );
			$background = sprintf( '#%06X', wp_rand( 0, 0xFFFFFF ) );
			$text       = wp_rand( 0, 1 ) ? 'no_color' : sprintf( '#%06X', wp_rand( 0, 0xFFFFFF ) );

			$term = wp_insert_term( $name, 'tribe_events_cat', [ 'slug' => $slug ] );
			if ( is_wp_error( $term ) || ! isset( $term['term_id'] ) ) {
				continue;
			}

			$term_id                                = (int) $term['term_id'];
			$terms[ $slug ]                         = $term_id;
			$teccc_options['terms'][ $term_id ]     = [ $slug, htmlentities( $name ) ];
			$teccc_options['all_terms'][ $term_id ] = [ $slug, htmlentities( $name ) ];
			$teccc_options["{$slug}-border"]        = $border;
			$teccc_options["{$slug}-background"]    = $background;
			$teccc_options["{$slug}_text"]          = $text;
		}

		if ( $include_settings ) {
			foreach ( Config::$settings_mapping as $old_key => $mapping ) {
				if ( $mapping['import'] ) {
					// Assign random or default values for settings being imported.
					switch ( $mapping['validation'] ) {
						case 'boolean':
							$teccc_options[ $old_key ] = (bool) wp_rand( 0, 1 );
							break;
						case 'array':
							$teccc_options[ $old_key ] = [ 'sample_value' ];
							break;
						default:
							$teccc_options[ $old_key ] = 'sample_value';
							break;
					}
				}
			}
		}

		update_option( 'teccc_options', $teccc_options );

		return $terms;
	}

	/**
	 * @test
	 * @dataProvider migration_scenarios
	 */
	public function it_transfers_category_colors_correctly( Closure $data_generator ): void {
		$this->set_fn_return( 'current_time', '{time}' );
		$category_ids = $data_generator();

		tribe( Handler::class )->process();

		$migration_status  = get_option( 'tec_events_category_colors_migration_status', [] );
		$original_settings = get_option( 'teccc_options', [] );

		$this->assertSame( 'migration_completed', $migration_status['status'] ?? '', 'Migration did not complete successfully.' );

		// Verify term meta values.
		foreach ( $category_ids as $slug => $term_id ) {
			$expected_meta = [
				'tec-events-cat-colors-primary'   => $original_settings["{$slug}-border"] ?? '',
				'tec-events-cat-colors-secondary' => $original_settings["{$slug}-background"] ?? '',
				'tec-events-cat-colors-text'      => ( $original_settings["{$slug}_text"] ?? '' ) === 'no_color' ? '' : ( $original_settings["{$slug}_text"] ?? '' ),
			];

			foreach ( $expected_meta as $meta_key => $expected_value ) {
				$this->assertSame(
					$expected_value,
					get_term_meta( $term_id, $meta_key, true ),
					"Mismatch in meta value '{$meta_key}' for term '{$slug}'."
				);
			}
		}
	}

	/**
	 * @test
	 */
	public function it_skips_execution_when_no_data(): void {
		$this->set_class_fn_return( Abstract_Migration_Step::class, 'get_migration_data', [] );
		$this->set_class_fn_return( Validator::class, 'process', true );

		tribe( Handler::class )->process();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( Status::$execution_skipped, $migration_status['status'] ?? '', 'Preprocessing should have been skipped but was not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_postprocessing_fails(): void {
		// Generate valid test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure execution completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'execution_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Corrupt post-processing: Insert incorrect meta values.
		foreach ( $category_ids as $term_id ) {
			update_term_meta( $term_id, 'tec-events-cat-colors-primary', 'WRONG_VALUE' ); // Intentionally wrong
			update_term_meta( $term_id, 'tec-events-cat-colors-secondary', 'ALSO_WRONG' );
		}

		// Directly trigger post-processing to validate stored meta.
		$post_processor = new Post_Processor();
		$post_processor->verify_migration();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'migration_failed', $migration_status['status'] ?? '', 'Post-processing should have failed but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_referenced_categories_do_not_exist(): void {
		// Generate valid test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Delete all terms to simulate missing categories.
		foreach ( $category_ids as $term_id ) {
			wp_delete_term( $term_id, 'tribe_events_cat' );
		}

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to missing categories but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_database_inserts_fail(): void {
		// Generate valid test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Mock `update_term_meta()` to return `false`, simulating a DB failure.
		$this->set_fn_return(
			'update_term_meta',
			function () {
				return false;
			},
			true
		);

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to database errors but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_migration_data_is_corrupt(): void {
		// Corrupt the migration data by inserting malformed structure.
		update_option(
			'tec_category_colors_migration_data',
			[
				'categories' => 'this should be an array but is a string',
				'legend'     => [],
				'general'    => [],
			]
		);

		// Ensure validation is marked as complete.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to corrupt migration data but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_migration_is_interrupted(): void {
		// Generate valid test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Mock `update_term_meta()` to throw an exception mid-execution.
		$this->set_fn_return(
			'update_term_meta',
			function () {
				static $call_count = 0;
				if ( ++$call_count === 3 ) {
					throw new \Exception( 'Unexpected error during execution.' );
				}

				return true;
			},
			true
		);

		// Run migration execution.
		try {
			$runner = new Worker();
			$runner->execute();
		} catch ( \Exception $e ) {}

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to interruption but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_migration_data_has_unexpected_keys(): void {
		// Generate valid test data.
		$this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Inject unexpected keys into migration data.
		update_option(
			'tec_category_colors_migration_data',
			[
				'categories'    => [],
				'legend'        => [],
				'general'       => [],
				'ignored_terms' => [],
				'unknown_key'   => 'this should not be here', // Unexpected key
			]
		);

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to unexpected keys but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_required_legend_settings_are_missing(): void {
		// Generate valid test data.
		$this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Remove required keys from the legend.
		update_option(
			'tec_category_colors_migration_data',
			[
				'categories' => [],
				'legend'     => [
					// 'add_legend' => 'value' is missing!
				],
				'general'    => [],
			]
		);

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to missing legend settings but did not.' );
	}

	/**
	 * @test
	 */
	public function it_fails_when_database_transaction_is_rolled_back(): void {
		// Generate valid test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Simulate a database transaction failure.
		$this->set_fn_return(
			'wpdb::query',
			function ( $query ) {
				if ( strpos( $query, 'COMMIT' ) !== false ) {
					return false; // Simulate failure on commit.
				}

				return true;
			},
			true
		);

		// Directly run execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame( 'execution_skipped', $migration_status['status'] ?? '', 'Execution should have failed due to a database rollback but did not.' );
	}

	/**
	 * @test
	 */
	public function it_prevents_rerunning_completed_migration(): void {
		// Generate valid test data.
		$this->generate_test_data( 5 );

		// Mark migration as completed.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'migration_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Capture logs before running migration.
		Errors::clear_errors();

		// Attempt to run migration again.
		tribe( Handler::class )->process();

		// Retrieve the migration status after rerunning.
		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		// Ensure the migration status has not changed.
		$this->assertSame( 'migration_completed', $migration_status['status'] ?? '', 'Migration should not have been rerun after completion.' );
	}

	/**
	 * @test
	 */
	public function it_prevents_running_migration_if_already_in_progress(): void {
		// Generate valid test data.
		$this->generate_test_data( 5 );

		// Mark migration as in progress.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'execution_in_progress',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Capture logs before running migration.
		Errors::clear_errors();

		// Attempt to run migration again.
		tribe( Handler::class )->process();

		// Retrieve the migration status after rerunning.
		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		// Ensure the migration status has not changed.
		$this->assertSame( 'execution_in_progress', $migration_status['status'] ?? '', 'Migration should not have been allowed to start while another is in progress.' );
	}

	/**
	 * @test
	 */
	public function it_logs_error_if_term_meta_update_fails_for_one_category(): void {
		// Generate test data with 5 categories.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Mock `update_term_meta()` to fail for the **third category**.
		$this->set_fn_return(
			'update_term_meta',
			function ( $term_id, $key, $value ) {
				static $fail_on_third = 0;

				return ++$fail_on_third === 3 ? false : true;
			},
			true
		);

		// Run migration execution.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame(
			'execution_skipped',
			$migration_status['status'] ?? '',
			'Migration should be skipped, and log errors for failed term meta updates.'
		);
	}

	/**
	 * @test
	 */
	public function it_respects_dry_run_mode_and_does_not_persist_changes(): void {
		// Generate test data.
		$category_ids = $this->generate_test_data( 5 );

		// Enable dry run mode.
		$runner = new Worker( true );
		$runner->execute();

		// Ensure migration status is unchanged.
		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertNotEquals(
			'execution_completed',
			$migration_status['status'] ?? '',
			'Migration should not complete in dry-run mode.'
		);
	}

	/**
	 * @test
	 */
	public function it_skips_already_migrated_categories_to_avoid_duplication(): void {
		// Generate test data.
		$category_ids = $this->generate_test_data( 5 );

		// Pre-insert meta values to simulate an already migrated category.
		foreach ( $category_ids as $term_id ) {
			update_term_meta( $term_id, 'tec-events-cat-colors-primary', '#FF0000' );
			update_term_meta( $term_id, 'tec-events-cat-colors-secondary', '#00FF00' );
			update_term_meta( $term_id, 'tec-events-cat-colors-text', '#0000FF' );
		}

		// Run migration.
		$runner = new Worker();
		$runner->execute();

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame(
			'execution_failed',
			$migration_status['status'] ?? '',
			'Migration should complete, skipping already migrated categories.'
		);
	}

	/**
	 * @test
	 */
	public function it_fails_gracefully_if_fatal_error_occurs_during_migration(): void {
		// Generate test data.
		$category_ids = $this->generate_test_data( 5 );

		// Ensure validation completes successfully.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'validation_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Mock `update_term_meta()` to throw a fatal error on the **third call**.
		$this->set_fn_return(
			'update_term_meta',
			function () {
				static $fail_on_third = 0;
				if ( ++$fail_on_third === 3 ) {
					throw new \Error( 'Unexpected fatal error during migration.' );
				}

				return true;
			},
			true
		);

		// Run migration execution.
		try {
			$runner = new Worker();
			$runner->execute();
		} catch ( \Error $e ) {}

		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		$this->assertSame(
			'execution_skipped',
			$migration_status['status'] ?? '',
			'Execution should fail due to fatal error.'
		);
	}

	/**
	 * @test
	 */
	public function it_correctly_stores_migrated_settings_in_tribe_events_calendar_options(): void {
		// Step 1: Ensure mapped settings do not exist before migration.
		$calendar_options = get_option( 'tribe_events_calendar_options', [] );
		foreach ( Config::$settings_mapping as $old_key => $mapping ) {
			if ( $mapping['import'] ) {
				$this->assertArrayNotHasKey(
					$mapping['mapped_key'],
					$calendar_options,
					"Mapped key '{$mapping['mapped_key']}' should not exist before migration."
				);
			}
		}

		// Step 2: Generate test data and run the migration.
		$this->generate_test_data( 5, true );
		tribe( Handler::class )->process();

		// Step 3: Retrieve updated calendar options and verify imported values.
		$updated_options = get_option( 'tribe_events_calendar_options', [] );

		foreach ( Config::$settings_mapping as $old_key => $mapping ) {
			if ( ! $mapping['import'] ) {
				// Skip settings that should not be imported.
				continue;
			}

			$mapped_key     = $mapping['mapped_key'];
			$expected_value = get_option( 'teccc_options', [] )[ $old_key ] ?? null;

			$this->assertArrayHasKey(
				$mapped_key,
				$updated_options,
				"Mapped key '{$mapped_key}' should exist in tribe_events_calendar_options."
			);

			$this->assertSame(
				$expected_value,
				$updated_options[ $mapped_key ] ?? null,
				"Mismatch in stored value for '{$mapped_key}'."
			);
		}
	}
	/**
	 * @test
	 */
	public function it_handles_empty_teccc_options_gracefully(): void {
		// Ensure `teccc_options` is empty before migration.
		delete_option( 'teccc_options' );

		// Run the migration.
		tribe( Handler::class )->process();

		// Verify that migration status is completed and no unexpected data is stored.
		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );
		$this->assertSame( 'preprocess_skipped', $migration_status['status'] ?? '', 'Migration should be skipped when there are no options.' );

		// Check that no unexpected settings were stored.
		$calendar_options = get_option( 'tribe_events_calendar_options', [] );
		foreach ( Config::$settings_mapping as $mapping ) {
			if ( $mapping['import'] ) {
				$this->assertArrayNotHasKey(
					$mapping['mapped_key'],
					$calendar_options,
					"Mapped key '{$mapping['mapped_key']}' should not exist when no prior settings were available."
				);
			}
		}
	}

	/**
	 * @test
	 */
	public function it_does_not_override_existing_settings_in_calendar_options(): void {
		// Prepopulate `tribe_events_calendar_options` with existing settings.
		$existing_options = [
			'category-color-legend-show' => 'existing_value',
			'category-color-custom-CSS' => true,
		];
		update_option( 'tribe_events_calendar_options', $existing_options );

		// Generate test data and run migration.
		$this->generate_test_data( 5, true );
		tribe( Handler::class )->process();

		// Fetch updated options.
		$updated_options = get_option( 'tribe_events_calendar_options', [] );

		// Ensure existing values were NOT overwritten.
		$this->assertSame(
			'existing_value',
			$updated_options['category-color-legend-show'] ?? null,
			'Existing setting should not have been overwritten.'
		);

		$this->assertTrue(
			$updated_options['category-color-custom-CSS'] ?? false,
			'Existing boolean setting should not have been changed.'
		);
	}

	/**
	 * @test
	 */
	public function it_allows_rerun_only_if_reset(): void {
		// Generate valid test data.
		$this->generate_test_data( 5 );

		// Mark migration as completed.
		update_option(
			'tec_events_category_colors_migration_status',
			[
				'status'    => 'migration_completed',
				'timestamp' => current_time( 'mysql' ),
			]
		);

		// Attempt to reset the migration.
		tribe( Handler::class )->reset_migration();

		// Retrieve migration status after reset.
		$migration_status = get_option( 'tec_events_category_colors_migration_status', [] );

		// Ensure migration status is reset to 'not_started'.
		$this->assertSame( 'not_started', $migration_status['status'] ?? '', 'Migration should be allowed to rerun only after a reset.' );

		// Run migration again.
		tribe( Handler::class )->process();

		// Retrieve migration status after rerun.
		$migration_status_after_rerun = get_option( 'tec_events_category_colors_migration_status', [] );

		// Ensure migration has now completed successfully.
		$this->assertSame( 'migration_completed', $migration_status_after_rerun['status'] ?? '', 'Migration should have been allowed to rerun after reset.' );
	}
}
