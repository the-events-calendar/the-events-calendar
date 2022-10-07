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
use Tribe\Events\Test\Traits\With_Uopz;

class Process_WorkerTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use CT1_Test_Utils;
	use Forks;
	use With_Uopz;

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
		$post_id = $this->given_a_non_migrated_single_event()->ID;
		$strategy = new Null_Migration_Strategy();
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function ( $strategy_param, $post_id_param, $dry_run_param ) use ( $strategy, $dry_run, $post_id ) {
			$this->assertNull( $strategy_param );
			$this->assertEquals( $post_id_param, $post_id );
			$this->assertEquals( $dry_run_param, $dry_run );

			return $strategy;
		}, 10, 3 );

		$events = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report = $process->migrate_event( $post_id, $dry_run );

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
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function () {
			return new class extends Null_Migration_Strategy {
				public function apply( Event_Report $event_report ) {
					throw new \RuntimeException( 'for reasons' );
				}
			};
		} );

		$events = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report = $process->migrate_event( $post_id, false );

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

		$events = new Events;
		$process = new Process_Worker( $events, new State( $events ) );
		$report = $process->migrate_event( $post_id, $dry_run );

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
		$tables = $schema_builder->get_registered_table_schemas();
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

	public function migration_notices_and_warnings_data_provider(): \Generator {
		yield 'E_NOTICE from tec plugins' => [
			function () {
				$test_array = [];
				$foo = $test_array['undefined_index'];
			},
			true,
			'failure'
		];

		yield 'E_WARNING from tec plugins' => [
			function () {
				$o->property = 23;
			},
			true,
			'failure'
		];

		yield 'E_USER_NOTICE from tec plugins' => [
			function () {
				trigger_error( 'This is a user notice', E_USER_NOTICE );
			},
			true,
			'failure'
		];

		yield 'E_USER_WARNING from tec plugins' => [
			function () {
				trigger_error( 'This is a user warning', E_USER_WARNING );
			},
			true,
			'failure'
		];

		yield 'E_NOTICE from non-tec plugins' => [
			function () {
				$test_array = [];
				$foo = $test_array['undefined_index'];
			},
			false,
			'success'
		];

		yield 'E_WARNING from not-tec plugins' => [
			function () {
				include 'undefined_file.php';
			},
			false,
			'success'
		];

		yield 'E_USER_NOTICE from non-tec plugins' => [
			function () {
				trigger_error( 'This is a user notice', E_USER_NOTICE );
			},
			false,
			'success'
		];

		yield 'E_USER_WARNING from non-tec plugins' => [
			function () {
				trigger_error( 'This is a user warning', E_USER_WARNING );
			},
			false,
			'success'
		];
	}

	/**
	 * It should correctly handle notices and warnings during migration
	 *
	 * @test
	 * @dataProvider migration_notices_and_warnings_data_provider
	 */
	public function should_correctly_handle_notices_and_warnings_during_migration(
		\Closure $before_migration,
		bool $from_tec_plugins,
		string $expected_status
	): void {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		$post = $this->given_a_non_migrated_single_event();
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		// The following code will trigger an E_NOTICE error.
		add_action( 'tec_events_custom_tables_v1_before_migration_applied', $before_migration );
		$this->uopz_set_return( 'tec_is_file_from_plugins', $from_tec_plugins );

		$events = new Events;
		$worker = new Process_Worker( $events, new State( $events ) );

		$event_report = $worker->migrate_event( $post->ID, true );

		$this->assertEquals( $expected_status, $event_report->status );
	}

	public function migrate_many_events_data_provider(): \Generator {
		yield 'no events to migrate' => [ 0, 0, 0, 10, 0, 0 ];
		yield '5 events to migrate, no claims' => [ 5, 0, 0, 10, 0, 5 ];
		yield '5 events to migrate, no claims, migrate 3' => [ 5, 0, 0, 3, 0, 3 ];
		yield '5 events to migrate, 3 pending claims' => [ 5, 3, 0, 10, 0, 2 ];
		yield '5 events to migrate, 5 pending claims' => [ 5, 5, 0, 10, 0, 5 ];
		yield '5 events to migrate, 1 pending claim, 2 running claims' => [ 5, 1, 2, 10, 0, 2 ];
		yield '5 events to migrate, no pending claim, 5 running claims' => [ 5, 0, 5, 10, 0, 0 ];
		yield '5 events to migrate, no pending claim, 3 running claims' => [ 5, 0, 3, 10, 0, 2 ];
		yield '5 events to migrate, no pending claim, 1 running claim, error on 3rd' => [ 5, 0, 2, 10, 3, 3 ];
		yield '5 events to migrate, 2 pending claims, no running claim, error on 2nd' => [ 5, 2, 0, 10, 2, 2 ];
		yield '5 events to migrate, no pending claim, no running claim, migrate 10' => [ 5, 0, 0, 10, 0, 5 ];
		yield '5 events to migrate, no pending claim, no running claim, error on 3rd' => [ 5, 0, 0, 10, 3, 3 ];
	}

	/**
	 * It should allow migrating many events in dry-run
	 *
	 * @test
	 * @dataProvider migrate_many_events_data_provider
	 */
	public function should_allow_migrating_many_events_in_dry_run(
		int $events_to_migrate,
		int $as_claimed_pending,
		int $as_claimed_running,
		int $migrate_count,
		int $migration_error_on,
		int $expected
	) {
		$events = [];
		$events_repository = new Events;
		global $wpdb;

		if ( $events_to_migrate ) {
			// Create the Events to migrate.
			foreach ( range( 1, $events_to_migrate ) as $k ) {
				$event = $this->given_a_non_migrated_single_event( [ 'post_title' => "Event $k" ] );
				$events[] = $event;
			}

			if ( $as_claimed_pending ) {
				// Claim some of the events to be processed by Action Scheduler, but not yet running.
				foreach ( range( 1, $as_claimed_pending ) as $k ) {
					$event = $events[ $k - 1 ];
					$action_id = as_enqueue_async_action( Process_Worker::ACTION_PROCESS, [ $event->ID, true ] );
					$this->assertNotEmpty( $action_id );
				}
			}

			if ( $as_claimed_running ) {
				// Claim some of the events to be processed by Action Scheduler, and currently running.
				foreach ( range( 1, $as_claimed_running ) as $k ) {
					$event = $events[ $k - 1 ];
					$action_id = as_enqueue_async_action( Process_Worker::ACTION_PROCESS, [ $event->ID, true ] );
					$this->assertNotEmpty( $action_id );
					$wpdb->query( "UPDATE {$wpdb->prefix}actionscheduler_actions SET status = 'running' WHERE action_id = {$action_id}" );
				}
			}

			if ( $as_claimed_running || $as_claimed_pending ) {
				// Lock as many IDs as are claimed.
				$locked = $events_repository->get_ids_to_process( $as_claimed_pending + $as_claimed_running );
				$this->assertCount( $as_claimed_pending + $as_claimed_running, $locked );
				$this->assertArraySubset( $locked, wp_list_pluck( $events, 'ID' ) );
			}

			if ( $migration_error_on ) {
				add_filter( 'tec_events_custom_tables_v1_before_migration_applied', function () use ( $migration_error_on ) {
					static $hits;
					$hits = $hits ? $hits + 1 : 1;
					if ( $hits === $migration_error_on ) {
						throw new \RuntimeException( 'Error on migration' );
					}
				} );
			}
		}

		$this->given_the_current_migration_phase_is( State::PHASE_PREVIEW_IN_PROGRESS );

		$worker = new Process_Worker( $events_repository, new State( $events_repository ) );
		$migrated = $worker->migrate_many_events( $migrate_count );

		$this->assertEquals( $expected, $migrated );
	}
}