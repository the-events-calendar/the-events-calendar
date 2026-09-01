<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Occurrences_MaintenanceTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * It should match a regenerated occurrence to its existing row
	 *
	 * @test
	 */
	public function should_match_a_regenerated_occurrence_to_its_existing_row(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$existing = Occurrence::where( 'post_id', '=', $post->ID )
							->order_by( 'start_date', 'DESC' )
							->first();
		$this->assertInstanceOf( Occurrence::class, $existing );

		$candidate = new Occurrence(
			[
				'post_id'        => $post->ID,
				'start_date'     => $existing->start_date,
				'end_date'       => $existing->end_date,
				'start_date_utc' => $existing->start_date_utc,
				'end_date_utc'   => $existing->end_date_utc,
				'duration'       => $existing->duration,
			]
		);

		$match = tribe( Occurrences_Maintenance::class )->get_occurrence_match( null, $candidate, $post->ID );

		$this->assertInstanceOf( Occurrence::class, $match );
		$this->assertEquals( $existing->occurrence_id, $match->occurrence_id );

		// The set is memoized for the request.
		$this->assertNotFalse( wp_cache_get( $post->ID, 'tec_occurrence_matches' ) );
	}

	/**
	 * It should serve matches from the memoized set
	 *
	 * @test
	 */
	public function should_serve_matches_from_the_memoized_set(): void {
		$post = $this->given_a_multi_date_event();

		// Seed the cache with a sentinel row: a hit must be served from it, not the DB.
		$sentinel = [
			'occurrence_id'  => 987654,
			'post_id'        => $post->ID,
			'start_date'     => '2026-12-24 09:00:00',
			'end_date'       => '2026-12-24 10:00:00',
			'start_date_utc' => '2026-12-24 09:00:00',
			'end_date_utc'   => '2026-12-24 10:00:00',
			'duration'       => 3600,
		];
		wp_cache_set( $post->ID, [ $sentinel ], 'tec_occurrence_matches' );

		$candidate = new Occurrence(
			[
				'post_id'        => $post->ID,
				'start_date'     => '2026-12-24 09:00:00',
				'end_date'       => '2026-12-24 10:00:00',
				'start_date_utc' => '2026-12-24 09:00:00',
				'end_date_utc'   => '2026-12-24 10:00:00',
				'duration'       => 3600,
			]
		);

		$match = tribe( Occurrences_Maintenance::class )->get_occurrence_match( null, $candidate, $post->ID );

		$this->assertInstanceOf( Occurrence::class, $match );
		$this->assertEquals( 987654, $match->occurrence_id );

		wp_cache_delete( $post->ID, 'tec_occurrence_matches' );
	}

	/**
	 * It should prevent reusing an unrelated row when no match exists
	 *
	 * @test
	 */
	public function should_prevent_reusing_an_unrelated_row_when_no_match_exists(): void {
		$post = $this->given_a_multi_date_event();

		$existing = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$this->assertInstanceOf( Occurrence::class, $existing );

		$candidate = new Occurrence(
			[
				'post_id'        => $post->ID,
				'start_date'     => '2027-01-15 09:00:00',
				'end_date'       => '2027-01-15 10:00:00',
				'start_date_utc' => '2027-01-15 09:00:00',
				'end_date_utc'   => '2027-01-15 10:00:00',
				'duration'       => 3600,
			]
		);

		// The base logic proposed to reuse an existing row: with no date match it must not.
		$match = tribe( Occurrences_Maintenance::class )->get_occurrence_match( $existing, $candidate, $post->ID );

		$this->assertNull( $match );
	}

	/**
	 * It should let the base logic apply to events without an rset
	 *
	 * @test
	 */
	public function should_let_the_base_logic_apply_to_events_without_an_rset(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Plain Single Event',
				'status'     => 'publish',
				'start_date' => '2026-11-06 09:00:00',
				'end_date'   => '2026-11-06 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$existing = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$this->assertInstanceOf( Occurrence::class, $existing );

		$candidate = new Occurrence(
			[
				'post_id'        => $post->ID,
				'start_date'     => '2027-01-15 09:00:00',
				'end_date'       => '2027-01-15 10:00:00',
				'start_date_utc' => '2027-01-15 09:00:00',
				'end_date_utc'   => '2027-01-15 10:00:00',
				'duration'       => 3600,
			]
		);

		// Passthrough: the input match is returned unchanged.
		$this->assertSame(
			$existing,
			tribe( Occurrences_Maintenance::class )->get_occurrence_match( $existing, $candidate, $post->ID )
		);
	}

	/**
	 * It should prune only the stale rows of the target event
	 *
	 * @test
	 */
	public function should_prune_only_the_stale_rows_of_the_target_event(): void {
		$post  = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);
		$other = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-13 09:00:00', 'end' => '2026-11-13 10:00:00' ],
			],
			[ 'title' => 'Other Multi Date Event' ]
		);

		$other_count_before = Occurrence::where( 'post_id', '=', $other->ID )->count();

		// Removing one date bumps the sequence and prunes the stale row on save.
		$this->assertTrue(
			tribe( Dates_Service::class )->set_dates(
				$post->ID,
				[
					[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
				]
			)
		);

		// The event date plus the one authored date.
		$this->assertEquals( 2, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		// The other event is untouched: pruning is scoped to the saved event.
		$this->assertEquals( $other_count_before, Occurrence::where( 'post_id', '=', $other->ID )->count() );
	}

	/**
	 * It should keep a single row when the event collapses to one date
	 *
	 * @test
	 */
	public function should_keep_a_single_row_when_the_event_collapses_to_one_date(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
				[ 'start' => '2026-11-19 09:00:00', 'end' => '2026-11-19 10:00:00' ],
			]
		);

		$this->assertEquals( 3, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		$this->assertTrue( tribe( Dates_Service::class )->remove_dates( $post->ID ) );

		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}

	/**
	 * It should not prune single occurrence events
	 *
	 * @test
	 */
	public function should_not_prune_single_occurrence_events(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Sequenceless Event',
				'status'     => 'publish',
				'start_date' => '2026-11-06 09:00:00',
				'end_date'   => '2026-11-06 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		// An Event without an RSET takes the collapse branch: its only row is kept.
		$this->assertEquals( 0, tribe( Occurrences_Maintenance::class )->prune_occurrences( $post->ID ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}
}
