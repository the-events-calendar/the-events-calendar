<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Null_Migration_Strategy;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Process_Test extends \Codeception\TestCase\WPTestCase {
	use CT1_Fixtures;

	public function dry_run_flags_provider() {
		return [
			'dry-run false' => [ false ],
			'dry-run true'  => [ true ],
		];
	}

	/**
	 * Should lock the processing when attempting to run the same action.
	 *
	 * @test
	 */
	public function should_lock_start_action() {
		$this->given_a_non_migrated_single_event();
		$process = new Process( new Events, new State );
		$this->assertEquals( 1, $process->start() );
		$this->assertFalse( $process->start() );
	}

	/**
	 * Should lock the processing when attempting to run the same action.
	 *
	 * @test
	 */
	public function should_lock_undo_action() {
		$post = $this->given_a_non_migrated_single_event();
		update_post_meta($post->ID, Event_Report::META_KEY_MIGRATION_PHASE, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS );
		$process = new Process( new Events, new State );
		$this->assertEquals( 1, $process->undo() );
		$this->assertFalse( $process->undo() );
	}

	/**
	 * It should provide correct parameters to migration strategies
	 *
	 * @test
	 * @dataProvider dry_run_flags_provider
	 */
	public function should_provide_correct_parameters_to_migration_strategies( $dry_run ) {
		$post_id  = $this->given_a_non_migrated_single_event();
		$strategy = new Null_Migration_Strategy();
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function ( $strategy_param, $post_id_param, $dry_run_param ) use ( $strategy, $dry_run, $post_id ) {
			$this->assertNull( $strategy_param );
			$this->assertEquals( $post_id_param, $post_id );
			$this->assertEquals( $dry_run_param, $dry_run );

			return $strategy;
		}, 10, 3 );

		$process = new Process( new Events, new State );
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
		$post_id = $this->given_a_non_migrated_single_event()->ID;
		$dry_run = null;
		add_filter( 'tec_events_custom_tables_v1_migration_strategy', function () {
			return new class extends Null_Migration_Strategy {
				public function apply( Event_Report $event_report ) {
					throw    new \RuntimeException( 'for reasons' );
				}
			};
		} );

		$process = new Process( new Events, new State );
		$report  = $process->migrate_event( $post_id, $dry_run );

		$this->assertEquals( 'for reasons', $report->error );
		$this->assertEquals( Event_Report::STATUS_FAILURE, $report->status );
	}
}
