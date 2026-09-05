<?php

namespace TEC\Events\Custom_Tables\V1\Events;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

/**
 * Ported from the Events Calendar Pro suite together with the class; the rule-based
 * fixtures are replaced by dates-only Events.
 */
class Event_SequenceTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * An Event on 2050-01-05 from 00:00 to 02:00 with the given extra dates.
	 */
	private function given_an_event_with_dates( array $dates, string $title = 'Event Sequence Test Event' ): WP_Post {
		return $this->given_a_multi_date_event(
			$dates,
			[
				'title'      => $title,
				'start_date' => '2050-01-05 00:00:00',
				'end_date'   => '2050-01-05 02:00:00',
			]
		);
	}

	public function sync_occurrences_data_provider(): array {
		return [
			'1 extra date, same day => 2 expected'        => [
				[ [ 'start' => '2050-01-05 03:00:00', 'end' => '2050-01-05 04:00:00' ] ],
				'2050-01-05 00:00:00',
				2,
			],
			'2 extra dates, same/other day => 2 expected' => [
				[
					[ 'start' => '2050-01-05 03:00:00', 'end' => '2050-01-05 04:00:00' ],
					[ 'start' => '2050-01-06 06:00:00', 'end' => '2050-01-06 07:00:00' ],
				],
				'2050-01-05 03:00:00',
				2,
			],
			'2 extra dates, same day => 3 expected'       => [
				[
					[ 'start' => '2050-01-05 03:00:00', 'end' => '2050-01-05 04:00:00' ],
					[ 'start' => '2050-01-05 06:00:00', 'end' => '2050-01-05 07:00:00' ],
				],
				'2050-01-05 03:00:00',
				3,
			],
			'2 extra dates, other days => 0 expected'     => [
				[
					[ 'start' => '2050-01-06 03:00:00', 'end' => '2050-01-06 04:00:00' ],
					[ 'start' => '2050-01-07 06:00:00', 'end' => '2050-01-07 07:00:00' ],
				],
				'2050-01-05 00:00:00',
				0,
			],
		];
	}

	/**
	 * Test the sync sequence meta.
	 *
	 * @dataProvider sync_occurrences_data_provider
	 * @test
	 */
	public function should_sync( array $dates, string $occurrence_date, int $expected_synced ): void {
		$post       = $this->given_an_event_with_dates( $dates );
		$occurrence = Occurrence::where( 'start_date', $occurrence_date )
								->where( 'post_id', $post->ID )
								->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$num_synced = Event_Sequence::sync_sequences_for( $occurrence );

		$this->assertEquals( $expected_synced, $num_synced, "Should see $expected_synced occurrences added to the list." );
		$this->assertEquals( 0, Event_Sequence::sync_sequences_for( $occurrence ), 'Should only update occurrences on first run.' );

		$meta = get_post_meta( $post->ID, Event_Sequence::meta_key( $occurrence_date ), true );
		if ( $expected_synced > 0 ) {
			$this->assertIsArray( $meta );
			$this->assertCount( $expected_synced, $meta );
		} else {
			$this->assertEmpty( $meta );
		}
	}

	/**
	 * Should generate an occurrence sequence meta properly.
	 *
	 * @test
	 */
	public function should_find_occurrence_by_sequence(): void {
		$occurrence_date = '2050-01-05';
		$first_date      = "$occurrence_date 00:00:00";
		$second_date     = "$occurrence_date 03:00:00";
		$third_date      = "$occurrence_date 05:00:00";

		$post = $this->given_an_event_with_dates(
			[
				[ 'start' => $second_date, 'end' => "$occurrence_date 05:00:00" ],
				[ 'start' => $third_date, 'end' => "$occurrence_date 07:00:00" ],
			]
		);
		$id   = $post->ID;

		[ $first_occurrence, $second_occurrence, $third_occurrence ] = Occurrence::where( 'post_id', $id )
																				->order_by( 'start_date', 'ASC' )
																				->get();

		// Did not sync yet - should fail to locate.
		$this->assertNull( Event_Sequence::find_occurrence_by_sequence( $id, 1, $first_date ) );

		Event_Sequence::sync_sequences_for( $first_occurrence );

		// Invalid sequence number should still be null.
		$this->assertNull( Event_Sequence::find_occurrence_by_sequence( $id, 0, $first_date ) );

		// Valid sequence numbers should match occurrence.
		$this->assertEquals( $first_occurrence->occurrence_id, Event_Sequence::find_occurrence_by_sequence( $id, 1, $first_date )->occurrence_id );
		$this->assertEquals( $second_occurrence->occurrence_id, Event_Sequence::find_occurrence_by_sequence( $id, 2, $first_date )->occurrence_id );
		$this->assertEquals( $third_occurrence->occurrence_id, Event_Sequence::find_occurrence_by_sequence( $id, 3, $first_date )->occurrence_id );
	}

	/**
	 * @test
	 */
	public function should_detect_occurrences_on_the_same_day(): void {
		// A single-date Event.
		$single = tribe_events()->set_args(
			[
				'title'      => 'Single Event',
				'start_date' => '2050-01-23 10:00:00',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'status'     => 'publish',
			]
		)->create();
		$this->assertEquals( 1, Occurrence::where( 'post_id', $single->ID )->count() );
		$single_occurrence = Occurrence::where( 'post_id', $single->ID )->first();

		// Three Occurrences on different days.
		$different_days = $this->given_an_event_with_dates(
			[
				[ 'start' => '2050-01-06 00:00:00', 'end' => '2050-01-06 02:00:00' ],
				[ 'start' => '2050-01-07 00:00:00', 'end' => '2050-01-07 02:00:00' ],
			],
			'Different Days Event'
		);
		$this->assertEquals( 3, Occurrence::where( 'post_id', $different_days->ID )->count() );

		// Two Occurrences on the same day.
		$same_day = $this->given_an_event_with_dates(
			[
				[ 'start' => '2050-01-05 11:00:00', 'end' => '2050-01-05 13:00:00' ],
			],
			'Same Day Event'
		);
		$this->assertEquals( 2, Occurrence::where( 'post_id', $same_day->ID )->count() );

		$this->assertFalse( Event_Sequence::has_occurrence_on_same_day( $single_occurrence ) );

		foreach ( Occurrence::where( 'post_id', $different_days->ID )->all() as $occurrence ) {
			$this->assertFalse( Event_Sequence::has_occurrence_on_same_day( $occurrence ) );
		}

		foreach ( Occurrence::where( 'post_id', $same_day->ID )->all() as $occurrence ) {
			$this->assertTrue( Event_Sequence::has_occurrence_on_same_day( $occurrence ) );
		}

		$this->assertInstanceOf( Occurrence::class, Event_Sequence::get_occurrence_on_same_day( $same_day->ID, '2050-01-05' ) );
		$this->assertNull( Event_Sequence::get_occurrence_on_same_day( $same_day->ID, '2050-01-09' ) );
	}
}
