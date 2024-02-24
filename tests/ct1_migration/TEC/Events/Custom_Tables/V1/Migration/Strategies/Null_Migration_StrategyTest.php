<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Strategies;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Null_Migration_StrategyTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * It should mark migration as success
	 *
	 * @test
	 */
	public function should_mark_migration_as_success() {
		$strategy = new Null_Migration_Strategy();
		$event  = $this->given_a_non_migrated_single_event();
		$report = new Event_Report( $event );

		$strategy->apply( $report );

		$this->assertEquals( '', $report->error );
		$this->assertEquals( Event_Report::STATUS_SUCCESS, $report->status );
	}
}
