<?php

namespace TEC\Events\Recurrence\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Dates;
use TEC\Events\Recurrence\Dates_Generator;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe\Events\Test\Traits\CT1\CT1_Test_Utils;
use Tribe\Tests\Traits\With_Uopz;

class Date_Rules_Migration_StrategyTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;
	use CT1_Test_Utils;
	use With_Uopz;

	/**
	 * @before
	 */
	public function set_migration_phase(): void {
		$this->given_the_current_migration_phase_is( State::PHASE_MIGRATION_IN_PROGRESS );
	}

	private function make_strategy( int $post_id, bool $dry_run = false ): Date_Rules_Migration_Strategy {
		// The loader registers the engine before instantiating the strategy: mirror it.
		tribe( Migration_Provider::class )->ensure_engine();

		return new Date_Rules_Migration_Strategy( $post_id, $dry_run );
	}

	/**
	 * It should throw for a non event post
	 *
	 * @test
	 */
	public function should_throw_for_a_non_event_post(): void {
		$post = static::factory()->post->create_and_get();

		$this->expectException( Migration_Exception::class );

		new Date_Rules_Migration_Strategy( $post->ID, false );
	}

	/**
	 * It should throw for a plain single event
	 *
	 * @test
	 */
	public function should_throw_for_a_plain_single_event(): void {
		$post = $this->given_a_non_migrated_single_event();

		$this->expectException( Migration_Exception::class );

		new Date_Rules_Migration_Strategy( $post->ID, false );
	}

	/**
	 * It should throw for a rule based event
	 *
	 * @test
	 */
	public function should_throw_for_a_rule_based_event(): void {
		$post = $this->given_a_non_migrated_rule_based_event();

		$this->expectException( Migration_Exception::class );

		new Date_Rules_Migration_Strategy( $post->ID, false );
	}

	/**
	 * It should migrate a dates only event
	 *
	 * @test
	 */
	public function should_migrate_a_dates_only_event(): void {
		$post    = $this->given_a_non_migrated_dates_only_event( 2 );
		$post_id = $post->ID;

		$strategy     = $this->make_strategy( $post_id );
		$event_report = $this->apply_strategy_to( $strategy, $post_id, false );

		$this->assertInstanceOf( Event_Report::class, $event_report );
		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		$this->assertEquals( [ Date_Rules_Migration_Strategy::get_slug() ], $event_report->strategies_applied );
		$this->assertEquals( 1, Event::where( 'post_id', '=', $post_id )->count() );

		$event = Event::find( $post_id, 'post_id' );
		$this->assertInstanceOf( Event::class, $event );
		$this->assertTrue( Dates::is_dates_only( (string) $event->rset ) );

		// The Event date plus the two additional dates.
		$occurrences = iterator_to_array( Occurrence::where( 'post_id', '=', $post_id )->all(), false );
		$this->assertCount( 3, $occurrences );

		foreach ( $occurrences as $occurrence ) {
			$this->assertEquals( 1, (int) $occurrence->has_recurrence );
			$this->assertEquals( 1, (int) $occurrence->is_rdate );
			$this->assertEquals( 1, (int) $occurrence->sequence );
		}
	}

	/**
	 * It should preview without writing rows
	 *
	 * @test
	 */
	public function should_preview_without_writing_rows(): void {
		$post    = $this->given_a_non_migrated_dates_only_event( 2 );
		$post_id = $post->ID;

		$strategy     = $this->make_strategy( $post_id, true );
		$event_report = $this->apply_strategy_to( $strategy, $post_id, true );

		$this->assertInstanceOf( Event_Report::class, $event_report );
		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		$this->assertEquals( 0, Event::where( 'post_id', '=', $post_id )->count() );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post_id )->count() );
	}

	/**
	 * It should keep occurrence ids stable on rerun
	 *
	 * @test
	 */
	public function should_keep_occurrence_ids_stable_on_rerun(): void {
		$post    = $this->given_a_non_migrated_dates_only_event( 2 );
		$post_id = $post->ID;

		$first_report = $this->apply_strategy_to( $this->make_strategy( $post_id ), $post_id, false );
		$this->assertEquals( 'success', $first_report->status );

		$first_ids = array_map(
			static fn( Occurrence $occurrence ) => (int) $occurrence->occurrence_id,
			iterator_to_array( Occurrence::where( 'post_id', '=', $post_id )->all(), false )
		);

		$second_report = $this->apply_strategy_to( $this->make_strategy( $post_id ), $post_id, false );
		$this->assertEquals( 'success', $second_report->status );

		$second_ids = array_map(
			static fn( Occurrence $occurrence ) => (int) $occurrence->occurrence_id,
			iterator_to_array( Occurrence::where( 'post_id', '=', $post_id )->all(), false )
		);

		sort( $first_ids );
		sort( $second_ids );
		$this->assertEquals( $first_ids, $second_ids );
	}

	/**
	 * It should not duplicate a date rule matching the event start
	 *
	 * @test
	 */
	public function should_not_duplicate_a_date_rule_matching_the_event_start(): void {
		$post    = $this->given_a_non_migrated_dates_only_event( 2 );
		$post_id = $post->ID;

		// Add a rule duplicating the Event's own date: the parser dedupes by start.
		$timezone    = new \DateTimeZone( (string) get_post_meta( $post_id, '_EventTimezone', true ) );
		$event_start = new \DateTimeImmutable( (string) get_post_meta( $post_id, '_EventStartDate', true ), $timezone );
		$event_end   = new \DateTimeImmutable( (string) get_post_meta( $post_id, '_EventEndDate', true ), $timezone );

		$meta            = get_post_meta( $post_id, '_EventRecurrence', true );
		$duplicate       = $meta['rules'][0];
		$duplicate['custom']['date']['date']  = $event_start->format( 'Y-m-d' );
		$duplicate['custom']['start-time']    = $event_start->format( 'g:ia' );
		$duplicate['custom']['end-time']      = $event_end->format( 'g:ia' );
		$duplicate['custom']['end-day']       = 'same-day';
		$meta['rules'][]                      = $duplicate;
		update_post_meta( $post_id, '_EventRecurrence', $meta );

		$event_report = $this->apply_strategy_to( $this->make_strategy( $post_id ), $post_id, false );

		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );
		// Event date + 2 additional dates: the duplicate collapsed.
		$this->assertEquals( 3, Occurrence::where( 'post_id', '=', $post_id )->count() );
	}

	/**
	 * It should leave legacy child posts untouched
	 *
	 * @test
	 */
	public function should_leave_legacy_child_posts_untouched(): void {
		[ 'parent' => $parent, 'children' => $children ] = $this->given_a_non_migrated_dates_only_event_with_legacy_children( 2 );

		$event_report = $this->apply_strategy_to( $this->make_strategy( $parent->ID ), $parent->ID, false );
		$this->assertEquals( 'success', $event_report->status, (string) $event_report->error );

		foreach ( $children as $child_id ) {
			$child = get_post( $child_id );
			$this->assertInstanceOf( \WP_Post::class, $child );
			$this->assertEquals( 'publish', $child->post_status );
			$this->assertEquals( $parent->ID, $child->post_parent );
			// Children are not migrated as standalone Events.
			$this->assertEquals( 0, Event::where( 'post_id', '=', $child_id )->count() );
		}

		// Child posts never enter the migration queue.
		$events = new \TEC\Events\Custom_Tables\V1\Migration\Events();
		$queued = $events->get_ids_to_process( 100 );
		foreach ( $children as $child_id ) {
			$this->assertNotContains( $child_id, array_map( 'intval', (array) $queued ) );
		}
	}

	/**
	 * It should throw when the occurrence count mismatches
	 *
	 * @test
	 */
	public function should_throw_when_the_occurrence_count_mismatches(): void {
		$post    = $this->given_a_non_migrated_dates_only_event( 2 );
		$post_id = $post->ID;

		// Break the expansion: without the dates generator only one Occurrence is saved.
		$this->set_class_fn_return( Dates_Generator::class, 'get_occurrences_generator', null );

		$this->expectException( Migration_Exception::class );

		$this->make_strategy( $post_id )->apply( new Event_Report( $post ) );
	}
}
