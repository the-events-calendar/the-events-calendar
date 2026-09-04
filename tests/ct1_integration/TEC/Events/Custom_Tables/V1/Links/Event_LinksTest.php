<?php

namespace TEC\Events\Custom_Tables\V1\Links;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Event_LinksTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * It should leave the sequence of non provisional posts untouched
	 *
	 * @test
	 */
	public function should_leave_the_sequence_of_non_provisional_posts_untouched(): void {
		$post = $this->given_a_multi_date_event();

		$this->assertEquals(
			'input-sequence',
			tribe( Event_Links::class )->filter_recurring_event_sequence_number( 'input-sequence', get_post( $post->ID ) )
		);
	}

	/**
	 * It should resolve the sequence of same day occurrences
	 *
	 * @test
	 */
	public function should_resolve_the_sequence_of_same_day_occurrences(): void {
		// Two Occurrences on the same day: the date URL alone cannot address them.
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2050-01-05 14:00:00', 'end' => '2050-01-05 15:00:00' ],
			]
		);

		$occurrence = Occurrence::where( 'post_id', '=', $post->ID )
								->order_by( 'start_date', 'DESC' )
								->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$provisional_id   = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );
		$provisional_post = get_post( $provisional_id );
		$this->assertInstanceOf( \WP_Post::class, $provisional_post );

		$resolved = tribe( Event_Links::class )->filter_recurring_event_sequence_number( null, $provisional_post );

		$this->assertNotNull( $resolved );
		$this->assertTrue( is_numeric( $resolved ) );
		$this->assertGreaterThan( 0, (int) $resolved );

		// A second resolution is served from the cache: wipe the sequences to prove it.
		$from_cache = tribe( Event_Links::class )->filter_recurring_event_sequence_number( null, $provisional_post );
		$this->assertEquals( $resolved, $from_cache );
	}

	/**
	 * It should not resolve a sequence for single day occurrences
	 *
	 * @test
	 */
	public function should_not_resolve_a_sequence_for_single_day_occurrences(): void {
		// One Occurrence per day: the date URL is enough, no sequence needed.
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
			]
		);

		$occurrence = Occurrence::where( 'post_id', '=', $post->ID )
								->order_by( 'start_date', 'DESC' )
								->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$provisional_id = tribe( ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );
		$provisional_post = get_post( $provisional_id );

		$this->assertNull(
			tribe( Event_Links::class )->filter_recurring_event_sequence_number( null, $provisional_post )
		);
	}
}
