<?php

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

/**
 * Test that only past events are purged
 */
class Tribe__Events__Event_Cleaner_SchedulerTest extends \Codeception\TestCase\WPTestCase {
	use CT1_Fixtures;
	use \Spatie\Snapshots\MatchesSnapshots;

	protected function given_an_old_event(): WP_Post {
		$timezone  = new DateTimeZone( 'Europe/Paris' );
		$utc       = new DateTimeZone( 'UTC' );
		$two_hours = new DateInterval( 'PT2H' );
		$start     = new DateTimeImmutable( '2018-01-01 00:00:00', $timezone );
		$args      = [
			'meta_input' => [
				'_EventStartDate'    => $start->format( Tribe__Date_Utils::DBDATETIMEFORMAT ),
				'_EventEndDate'      => $start->add( $two_hours )->format( Tribe__Date_Utils::DBDATETIMEFORMAT ),
				'_EventStartDateUTC' => $start->setTimezone( $utc )->format( Tribe__Date_Utils::DBDATETIMEFORMAT ),
				'_EventEndDateUTC'   => $start->setTimezone( $utc )->add( $two_hours )->format( Tribe__Date_Utils::DBDATETIMEFORMAT ),
				'_EventDuration'     => 7200,
				'_EventTimezone'     => $timezone->getName(),
				'_EventTimezoneAbbr' => Tribe__Timezones::abbr( $start, $timezone )
			]
		];

		return $this->given_a_migrated_single_event( $args );
	}

	/**
	 * Check to make sure that past events are being correctly selected.
	 *
	 * @test
	 */
	public function should_trash_old_events() {
		$post         = $this->given_an_old_event();
		$skip_me_post = $this->given_a_migrated_single_event();

		// Sanity check before start
		$this->assertInstanceOf( Event::class, Event::find( $post->ID, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		$this->assertEquals( 'publish', $post->post_status );

		// Limit our cleaner to one occurrence (the id above should be hit)
		add_filter( 'tribe_events_delete_old_events_sql_args', function ( $args ) {
			$args['limit'] = 1;

			return $args;
		} );

		$cleaner            = new Tribe__Events__Event_Cleaner_Scheduler();
		$event_ids_to_purge = $cleaner->select_events_to_purge( 3 );

		$this->assertContains( $post->ID, $event_ids_to_purge, 'Past events should be selected' );
		$this->assertNotContains( $skip_me_post->ID, $event_ids_to_purge, 'Upcoming events should never be selected' );


		// Should be trashed - results will not contain the updates to the posts.
		$results = $cleaner->move_old_events_to_trash();

		$this->assertIsArray( $results );
		$this->assertCount( 1, $results );
		$this->assertInstanceOf( WP_Post::class, $results[ $post->ID ] );
		$this->assertEquals( $post->ID, $results[ $post->ID ]->ID );

		// Should still have occurrence, but the post should be trashed
		$this->assertInstanceOf( Event::class, Event::find( $post->ID, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		// Get trashed post
		$trashed_post = get_post( $post->ID );
		$this->assertEquals( 'trash', $trashed_post->post_status );

		// Should be here still
		$this->assertInstanceOf( Event::class, Event::find( $trashed_post->ID, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $trashed_post->ID )->count() );
	}

	/**
	 * @test
	 */
	public function should_delete_trashed_on_wp_scheduled_delete() {
		// Limit our cleaner to one occurrence (the provisional id above should be hit)
		add_filter( 'tribe_events_delete_old_events_sql_args', function ( $args ) {
			$args['limit'] = 1;

			return $args;
		} );

		$post = $this->given_an_old_event();

		// Sanity check
		$this->assertInstanceOf( Event::class, Event::find( $post->ID, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		// Should be trashed - results will not contain the updates to the posts.
		$cleaner = new Tribe__Events__Event_Cleaner_Scheduler();
		$results = $cleaner->move_old_events_to_trash();
		$this->assertCount( 1, $results );

		// Now let's force WP's trash cleanup. Default is to delete posts 30 days old trash, let's modify the meta to flag this ready to remove
		$meta_value = time() - ( ( EMPTY_TRASH_DAYS * DAY_IN_SECONDS ) + 1 );
		update_post_meta( $post->ID, '_wp_trash_meta_time', $meta_value );
		wp_scheduled_delete();

		// Should be gone now
		$trashed_post = get_post( $post->ID );
		$this->assertEmpty( $trashed_post );
		$this->assertNull( Event::find( $post->ID, 'post_id' ) );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $post->ID )->count() );
	}

	/**
	 * @test
	 */
	public function should_have_occurrence_sql() {
		$sql_snapshot = '';
		add_filter( 'tribe_events_delete_old_events_sql', function ( $sql ) use ( &$sql_snapshot ) {
			$sql_snapshot = $sql;

			return $sql;
		}, 999 );
		// Should generate CT1 SQL for the purge query.
		$cleaner = new Tribe__Events__Event_Cleaner_Scheduler();
		$cleaner->select_events_to_purge( 3 );

		$this->assertNotEmpty( $sql_snapshot );
		$this->assertMatchesSnapshot( $sql_snapshot );
	}
}