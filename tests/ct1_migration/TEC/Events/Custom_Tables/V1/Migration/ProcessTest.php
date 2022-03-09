<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Null_Migration_Strategy;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class ProcessTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

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
		update_post_meta( $post->ID, Event_Report::META_KEY_MIGRATION_PHASE, Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS );
		$process = new Process( new Events, new State );
		$this->assertTrue( $process->undo() );
		$this->assertFalse( $process->undo() );
	}
}
