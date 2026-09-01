<?php

namespace TEC\Events\Recurrence\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\Process_Worker;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\CT1\CT1_Test_Utils;

class Dates_Only_End_To_EndTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use CT1_Test_Utils;

	private function make_worker(): Process_Worker {
		$events = new Events();

		return new Process_Worker( $events, new State( $events ) );
	}

	/**
	 * It should migrate a dates only event through the worker
	 *
	 * @test
	 */
	public function should_migrate_a_dates_only_event_through_the_worker(): void {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
		tribe( Migration_Provider::class )->register();

		$post = $this->given_a_non_migrated_dates_only_event( 2 );

		$event_report = $this->make_worker()->migrate_event( $post->ID, false );

		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		$this->assertEquals( [ Date_Rules_Migration_Strategy::get_slug() ], $event_report->strategies_applied );
		$this->assertFalse( (bool) $event_report->is_single );
		$this->assertEquals( 1, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 3, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		// The migration did not abort: with the only Event migrated it completed.
		$this->assertEquals( State::PHASE_MIGRATION_COMPLETE, $this->get_phase() );
	}

	/**
	 * It should preview a dates only event through the worker
	 *
	 * @test
	 */
	public function should_preview_a_dates_only_event_through_the_worker(): void {
		$this->given_the_current_migration_phase_is( State::PHASE_PREVIEW_IN_PROGRESS );
		tribe( Migration_Provider::class )->register();

		$post = $this->given_a_non_migrated_dates_only_event( 2 );

		$event_report = $this->make_worker()->migrate_event( $post->ID, true );

		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		$this->assertEquals( [ Date_Rules_Migration_Strategy::get_slug() ], $event_report->strategies_applied );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		// The preview did not abort: with the only Event previewed it reached the prompt.
		$this->assertEquals( State::PHASE_MIGRATION_PROMPT, $this->get_phase() );
	}
}
