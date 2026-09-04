<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_Post;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Dates_ServiceTest extends WPTestCase {
	use With_Recurrence_Engine;

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Dates Service Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-05 09:00:00',
				'end_date'   => '2050-01-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	/**
	 * It should author dates writing the canonical meta and derived rset
	 *
	 * @test
	 */
	public function should_author_dates_writing_the_canonical_meta_and_derived_rset(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		$set = $service->set_dates(
			$post->ID,
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
				[ 'start' => '2050-01-20 14:00:00', 'end' => '2050-01-20 15:30:00' ],
			]
		);

		$this->assertTrue( $set );

		// The canonical authored format is the legacy meta, in the Pro-readable shape.
		$meta = get_post_meta( $post->ID, '_EventRecurrence', true );
		$this->assertTrue( Date_Rules::is_dates_only_meta( $meta ) );
		$this->assertCount( 2, $meta['rules'] );

		// The rset is derived from it.
		$event = Event::find( $post->ID, 'post_id' );
		$this->assertTrue( Dates::is_dates_only( (string) $event->rset ) );

		// The Occurrences are regenerated.
		$dates = $service->get_dates( $post->ID );
		$this->assertCount( 3, $dates );
		$this->assertEquals(
			[ '2050-01-05 09:00:00', '2050-01-12 09:00:00', '2050-01-20 14:00:00' ],
			array_column( $dates, 'start' )
		);
		$this->assertTrue( tribe_is_recurring_event( $post->ID ) );
	}

	/**
	 * It should accept a provisional ID as input
	 *
	 * @test
	 */
	public function should_accept_a_provisional_id_as_input(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		$service->set_dates(
			$post->ID,
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
			]
		);

		$second = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date', 'DESC' )->first();

		$set = $service->set_dates(
			$second->provisional_id,
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
				[ 'start' => '2050-01-27 09:00:00', 'end' => '2050-01-27 10:00:00' ],
			]
		);

		$this->assertTrue( $set );
		$this->assertCount( 3, $service->get_dates( $post->ID ) );
	}

	/**
	 * It should remove dates collapsing the event to a single occurrence
	 *
	 * @test
	 */
	public function should_remove_dates_collapsing_the_event_to_a_single_occurrence(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		$service->set_dates(
			$post->ID,
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
			]
		);
		$this->assertCount( 2, $service->get_dates( $post->ID ) );

		$removed = $service->remove_dates( $post->ID );

		$this->assertTrue( $removed );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
		$this->assertEquals( '', (string) Event::find( $post->ID, 'post_id' )->rset );
		$dates = $service->get_dates( $post->ID );
		$this->assertCount( 1, $dates );
		// The surviving Occurrence must be the Event's own, not a stale extra date row.
		$this->assertEquals( '2050-01-05 09:00:00', $dates[0]['start'] );
		$this->assertFalse( tribe_is_recurring_event( $post->ID ) );
	}

	/**
	 * It should reject a date entry missing its start or end
	 *
	 * @test
	 */
	public function should_reject_a_date_entry_missing_its_start_or_end(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		$this->assertFalse( $service->set_dates( $post->ID, [ [ 'start' => '2050-01-12 09:00:00' ] ] ) );
		$this->assertFalse( $service->set_dates( $post->ID, [ 'not-an-array' ] ) );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
		$this->assertCount( 1, $service->get_dates( $post->ID ) );
	}

	/**
	 * It should not remove rule based recurrence
	 *
	 * @test
	 */
	public function should_not_remove_rule_based_recurrence(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		update_post_meta(
			$post->ID,
			'_EventRecurrence',
			[
				'rules' => [
					[ 'type' => 'Weekly' ],
				],
			]
		);

		$this->assertFalse( $service->remove_dates( $post->ID ) );
		$this->assertNotEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should re derive the rset when the event dates change
	 *
	 * @test
	 */
	public function should_re_derive_the_rset_when_the_event_dates_change(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		$service->set_dates(
			$post->ID,
			[
				[ 'start' => '2050-01-12 09:00:00', 'end' => '2050-01-12 10:00:00' ],
			]
		);

		$rset_before = (string) Event::find( $post->ID, 'post_id' )->rset;

		// Move the Event (first Occurrence) one hour later, as an editor date save would.
		update_post_meta( $post->ID, '_EventStartDate', '2050-01-05 10:00:00' );
		update_post_meta( $post->ID, '_EventEndDate', '2050-01-05 11:00:00' );
		update_post_meta( $post->ID, '_EventStartDateUTC', '2050-01-05 13:00:00' );
		update_post_meta( $post->ID, '_EventEndDateUTC', '2050-01-05 14:00:00' );

		// Run the update pipeline entry the Meta_Watcher would trigger.
		wp_cache_delete( $post->ID, 'tec_occurrence_matches' );
		$updated = tribe( \TEC\Events\Custom_Tables\V1\Updates\Events::class )->update( $post->ID );

		$this->assertTrue( $updated );

		$rset_after = (string) Event::find( $post->ID, 'post_id' )->rset;

		$this->assertNotEquals( $rset_before, $rset_after );
		$this->assertStringContainsString( 'DTSTART;TZID=America/Sao_Paulo:20500105T100000', $rset_after );
		// The additional date is preserved.
		$this->assertStringContainsString( '20500112T090000', $rset_after );
	}

	/**
	 * It should refuse an empty date bound instead of authoring an occurrence at the current time
	 *
	 * `isset()` passes an empty string, and `new DateTimeImmutable( '' )` is "now".
	 *
	 * @test
	 */
	public function should_refuse_an_empty_date_bound(): void {
		$post    = $this->given_an_event();
		$service = tribe( Dates_Service::class );

		foreach ( [ [ 'start' => '', 'end' => '2050-01-12 10:00:00' ], [ 'start' => '2050-01-12 09:00:00', 'end' => ' ' ] ] as $date ) {
			$this->assertFalse( $service->set_dates( $post->ID, [ $date ] ) );
		}

		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}
}
