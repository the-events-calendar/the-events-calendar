<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Null_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\CT1\CT1_Test_Utils;
use Tribe\Events\Test\Traits\Forks;

class Process_WorkerTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use CT1_Test_Utils;
	use Forks;

	private $uopz_allow_exit_ini_value;

	public function dry_run_flags_provider() {
		return [
			'dry-run false' => [ false ],
			'dry-run true'  => [ true ],
		];
	}

	public function concurrency_settings_provider() {
		return [
			'10 Events, 3 Workers' => [ 10, 3 ]
		];
	}

	/**
	 * It should provide correct parameters to migration strategies
	 *
	 * @test
	 * @dataProvider dry_run_flags_provider
	 */
	public function should_provide_correct_parameters_to_migration_strategies( $dry_run ) {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post_id  = $this->given_a_non_migrated_single_event();
		$strategy = new Null_Migration_Strategy();
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function ( $strategy_param, $post_id_param, $dry_run_param ) use ( $strategy, $dry_run, $post_id ) {
			$this->assertNull( $strategy_param );
			$this->assertEquals( $post_id_param, $post_id );
			$this->assertEquals( $dry_run_param, $dry_run );

			return $strategy;
		}, 10, 3 );

		$events  = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report  = $process->migrate_event( $post_id, $dry_run );

		$this->assertEquals( '', $report->error );
		$this->assertEquals( Event_Report::STATUS_SUCCESS, $report->status );
	}

	/**
	 * It should correctly handle throwing migration strategy
	 *
	 * @test
	 */
	public function should_correctly_handle_throwing_migration_strategy() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post_id = $this->given_a_non_migrated_single_event()->ID;
		$dry_run = null;
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function () {
			return new class extends Null_Migration_Strategy {
				public function apply( Event_Report $event_report ) {
					throw new \RuntimeException( 'for reasons' );
				}
			};
		} );

		$events  = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report  = $process->migrate_event( $post_id, $dry_run );

		$this->assertContains( 'for reasons', $report->error );
		$this->assertEquals( Event_Report::STATUS_FAILURE, $report->status );
		$this->assertEquals( State::PHASE_MIGRATION_FAILURE_IN_PROGRESS, $this->get_phase() );
	}

	/**
	 * It should correctly handle error in migration strategy
	 *
	 * @test
	 */
	public function should_correctly_handle_error_migration_strategy() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post_id = $this->given_a_non_migrated_single_event()->ID;

		$dry_run = false;
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function () {
			return new class extends Null_Migration_Strategy {
				public function apply( Event_Report $event_report ) {
					trigger_error( "Random error" );
					// Should fail on first error.
					trigger_error( "Should not hit this error" );
				}
			};
		} );

		$events  = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report  = $process->migrate_event( $post_id, $dry_run );

		$this->assertContains( "Random error", $report->error );
		$this->assertEquals( Event_Report::STATUS_FAILURE, $report->status );
		// If an error, we abort migration and work to a rollback state.
		$this->assertEquals( State::PHASE_MIGRATION_FAILURE_IN_PROGRESS, $this->get_phase() );
	}

	/**
	 * It should correctly handle impromptu die in migration strategy
	 *
	 * @test
	 * @dataProvider concurrency_settings_provider
	 */
	public function should_correctly_handle_die_migration_strategy( $event_set_size, $parallelism ) {
		if ( ! function_exists( 'pcntl_fork' ) ) {
			$this->markTestSkipped( 'The pcntl_fork function is required to run this test.' );
		}

		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post_ids = array_map( function () {
			return $this->given_a_non_migrated_single_event()->ID;
		}, range( 1, $event_set_size ) );

		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function () {
			return new class extends Null_Migration_Strategy {
				public function apply( Event_Report $event_report ) {
					die();
				}
			};
		} );

		$generate_workers_for = static function ( $post_ids ) {
			foreach ( $post_ids as $post_id ) {
				yield static function () use ( $post_id ) {
					$events = new Events;
					$worker = new Process_Worker( $events, new State( $events ) );
					$worker->migrate_event( $post_id );
				};
			}
		};

		// ACT
		$this->fork_loop_wait( $generate_workers_for( $post_ids ), $parallelism );

		// Make sure the db connection is fine.
		// Make sure the db connection is fine.
		global $wpdb;
		$wpdb->check_connection( false );

		// Should have transitioned phase and all events should have failed.
		foreach ( $post_ids as $post_id ) {
			$event_report = new Event_Report( get_post( $post_id ) );
			$this->assertNotEmpty( $event_report->error );
			$this->assertEquals( Event_Report::STATUS_FAILURE, $event_report->status );
		}

		// The option will have been updated in a fork, the local cache will be not up-to-date.
		$this->assertEquals( State::PHASE_MIGRATION_COMPLETE, $this->get_phase() );
	}

	/**
	 * It should handle concurrency
	 *
	 * @test
	 * @dataProvider concurrency_settings_provider
	 */
	public function should_handle_concurrency( $event_set_size, $parallelism ) {
		if ( ! function_exists( 'pcntl_fork' ) ) {
			$this->markTestSkipped( 'The pcntl_fork function is required to run this test.' );
		}

		// ARRANGE.
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post_ids = array_map( function () {
			return $this->given_a_non_migrated_single_event()->ID;
		}, range( 1, $event_set_size ) );
		$this->assertCount( $event_set_size, $post_ids );

		$generate_workers_for = static function ( $post_ids ) {
			foreach ( $post_ids as $post_id ) {
				yield static function () use ( $post_id ) {
					$events = new Events;
					$worker = new Process_Worker( $events, new State( $events ) );
					$worker->migrate_event( $post_id );
				};
			}
		};

		// Let's make sure there are no migrated events to begin with.
		global $wpdb;
		$migrated_events = $wpdb->get_var(
			$wpdb->prepare(
				"select count(post_id) from $wpdb->postmeta where meta_key = %s and meta_value = %s",
				Event_Report::META_KEY_MIGRATION_PHASE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS
			)
		);
		$this->assertEquals( 0, $migrated_events );

		// ACT
		$this->fork_loop_wait( $generate_workers_for( $post_ids ), $parallelism );


		// ASSERT
		global $wpdb;
		// Make sure the db connection is fine.
		$wpdb->check_connection( false );
		$migrated_events = $wpdb->get_var(
			$wpdb->prepare(
				"select count(post_id) from $wpdb->postmeta where meta_key = %s and meta_value = %s",
				Event_Report::META_KEY_MIGRATION_PHASE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS
			)
		);
		$this->assertEquals( $event_set_size, $migrated_events );
	}

	/**
	 * It should handle missing transaction support in preview
	 *
	 * @test
	 */
	public function should_handle_missing_transaction_support_in_preview() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post = $this->given_a_non_migrated_single_event();
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_false' );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		$events = new Events;
		$worker = new Process_Worker( $events, new State( $events ) );

		$event_report = $worker->migrate_event( $post->ID, true );

		$this->assertInstanceOf( Event_Report::class, $event_report );
		$this->assertEquals( 'success', $event_report->status );
		$this->assertEquals( [], $event_report->strategies_applied );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}

	/**
	 * It should handle missing transaction support in migration
	 *
	 * @test
	 */
	public function should_handle_missing_transaction_support_in_migration() {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post = $this->given_a_non_migrated_single_event();
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_false' );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		$events = new Events;
		$worker = new Process_Worker( $events, new State( $events ) );

		$event_report = $worker->migrate_event( $post->ID, false );

		$this->assertInstanceOf( Event_Report::class, $event_report );
		$this->assertEquals( 'success', $event_report->status );
		$this->assertEquals( [ Single_Event_Migration_Strategy::get_slug() ], $event_report->strategies_applied );
		$this->assertEquals( 1, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}


	/**
	 * It should handle undo migration
	 *
	 * @test
	 */
	public function should_handle_undo_migration() {
		// Setup our state + sanity check
		$schema_builder = new Schema_Builder();
		$tables         = $schema_builder->get_registered_table_schemas();
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post = $this->given_a_non_migrated_single_event();
		add_filter( 'tec_events_custom_tables_v1_db_transactions_supported', '__return_false' );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		$events       = new Events;
		$worker       = new Process_Worker( $events, new State( $events ) );
		$event_report = $worker->migrate_event( $post->ID, false );
		$this->assertInstanceOf( Event_Report::class, $event_report );
		$this->assertEquals( 'success', $event_report->status );
		foreach ( $tables as $table ) {
			$this->assertTrue( $table->exists(), 'The table should be here before undo is fired.' );
		}

		// Undo our migration.
		$worker->undo_event_migration( [] );

		// Test that the state is what we expect afterwards.
		foreach ( $tables as $table ) {
			$this->assertFalse( $table->exists(), 'The table should be gone after undo is fired.' );
		}
		$meta_keys = [
			Event_Report::META_KEY_REPORT_DATA,
			Event_Report::META_KEY_MIGRATION_PHASE,
			Event_Report::META_KEY_MIGRATION_CATEGORY
		];
		foreach ( $meta_keys as $meta_key ) {
			$this->assertEmpty( get_post_meta( $post->ID, $meta_key, true ), "The meta ($meta_key) should be gone after an undo." );
		}

		// Rebuild required tables for other tests.
		$schema_builder->up();
	}
}