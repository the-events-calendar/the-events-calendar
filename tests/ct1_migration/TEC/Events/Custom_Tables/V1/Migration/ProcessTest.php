<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class ProcessTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * Should lock the processing when attempting to run the same action.
	 *
	 * @test
	 */
	public function should_lock_start_action() {
		$this->given_action_scheduler_is_loaded();
		$this->given_a_non_migrated_single_event();
		$events  = new Events;
		$process = new Process( $events, new State( $events ) );
		$this->assertEquals( 1, $process->start() );
		$this->assertFalse( $process->start() );
	}

	/**
	 * Should lock the processing when attempting to run the same action.
	 *
	 * @test
	 */
	public function should_lock_revert_action() {
		$this->given_action_scheduler_is_loaded();
		$post = $this->given_a_non_migrated_single_event();
		update_post_meta( $post->ID, Event_Report::META_KEY_MIGRATION_PHASE, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS );
		$events  = new Events;
		$process = new Process( $events, new State( $events ) );
		$this->assertTrue( $process->revert() );
		$this->assertFalse( $process->revert() );
	}

	/**
	 * Should lock the processing when attempting to run the same action.
	 *
	 * @test
	 */
	public function should_lock_cancel_action() {
		$this->given_action_scheduler_is_loaded();
		$post = $this->given_a_non_migrated_single_event();
		update_post_meta( $post->ID, Event_Report::META_KEY_MIGRATION_PHASE, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS );
		$events  = new Events;
		$process = new Process( $events, new State( $events ) );
		$this->assertTrue( $process->cancel() );
		$this->assertFalse( $process->cancel() );
	}
}
