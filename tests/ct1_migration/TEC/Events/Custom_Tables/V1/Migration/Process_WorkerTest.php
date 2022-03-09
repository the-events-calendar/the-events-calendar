<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Null_Migration_Strategy;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\Forks;

class Process_WorkerTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use Forks;

	private $uopz_allow_exit_ini_value;

	public function dry_run_flags_provider() {
		return [
			'dry-run false' => [ false ],
			'dry-run true'  => [ true ],
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

		$process = new Process_Worker( new Events, new State );
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
					throw    new \RuntimeException( 'for reasons' );
				}
			};
		} );

		$process = new Process_Worker( new Events, new State );
		$report  = $process->migrate_event( $post_id, $dry_run );

		$this->assertEquals( 'for reasons', $report->error );
		$this->assertEquals( Event_Report::STATUS_FAILURE, $report->status );
	}

	public function concurrency_settings_provider() {
		return [
			'10 Events, 3 Workers' => [ 10, 3 ]
		];
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
					$worker = new Process_Worker( new Events, new State );
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
		$migrated_events = $wpdb->get_var(
			$wpdb->prepare(
				"select count(post_id) from $wpdb->postmeta where meta_key = %s and meta_value = %s",
				Event_Report::META_KEY_MIGRATION_PHASE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS
			)
		);
		$this->assertEquals( $event_set_size, $migrated_events );
	}
}