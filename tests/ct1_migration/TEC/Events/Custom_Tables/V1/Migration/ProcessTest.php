<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

class ProcessTest extends \CT1_Migration_Test_Case {
	/**
	 * It should be able to queue Action Scheduler actions
	 *
	 * @test
	 */
	public function should_be_able_to_queue_action_scheduler_actions() {
		$this->assertTrue( function_exists( 'as_enqueue_async_action' ) );
	}
}