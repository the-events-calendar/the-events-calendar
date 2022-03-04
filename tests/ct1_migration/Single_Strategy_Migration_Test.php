<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Strategies;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy as Strategy;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Single_Strategy_Migration_Test extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * It should correctly preview an event migration
	 *
	 * @test
	 */
	public function should_correctly_preview_an_event_migration() {
		$post    = $this->given_a_non_migrated_single_event();
		$report  = new Event_Report( $post );
		$post_id = $post->ID;

		$strategy = new Strategy( $post_id, true );
		$strategy->apply( $report );

		$event = Event::find( $post_id, 'post_id' );

		$this->assertNull( Event::class, $event, 'No Event model should have been inserted during preview.' );

		$occurrences = Occurrence::where( 'post_id', '=', $post_id )
		                         ->get();

		$this->assertCount( 0, $occurrences, 'No Occurrence models should have been inserted during preview.' );
	}
}