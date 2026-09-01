<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use WP_Post;

class Blocks_ProviderTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model static extensions cache: it may have been locked before the engine registered.
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Blocks Provider Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	/**
	 * It should consume the mirror meta into canonical dates
	 *
	 * @test
	 */
	public function should_consume_the_mirror_meta_into_canonical_dates(): void {
		$post = $this->given_an_event();

		update_post_meta(
			$post->ID,
			Blocks_Provider::META_KEY,
			wp_json_encode(
				[
					[ 'date' => '2026-11-12', 'start' => '09:00:00', 'end' => '10:00:00' ],
					[ 'date' => 'bogus', 'start' => 'xx', 'end' => 'yy' ],
				]
			)
		);

		tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $post->ID ) );

		$meta = get_post_meta( $post->ID, '_EventRecurrence', true );
		$this->assertTrue( Date_Rules::is_dates_only_meta( $meta ) );
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should not consume when the mirror meta is absent
	 *
	 * @test
	 */
	public function should_not_consume_when_the_mirror_meta_is_absent(): void {
		$post = $this->given_an_event();

		tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );
		delete_post_meta( $post->ID, Blocks_Provider::META_KEY );

		tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $post->ID ) );

		// The dates authored elsewhere were not cleared.
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should refuse consuming into a rule locked event
	 *
	 * @test
	 */
	public function should_refuse_consuming_into_a_rule_locked_event(): void {
		$post = $this->given_an_event();
		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ]
		);
		$rset = (string) Event::find( $post->ID, 'post_id' )->rset;

		update_post_meta(
			$post->ID,
			Blocks_Provider::META_KEY,
			wp_json_encode( [ [ 'date' => '2026-11-12', 'start' => '09:00:00', 'end' => '10:00:00' ] ] )
		);

		tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $post->ID ) );

		$this->assertEquals( $rset, (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should collapse the event when the mirror is an empty list
	 *
	 * @test
	 */
	public function should_collapse_the_event_when_the_mirror_is_an_empty_list(): void {
		$post = $this->given_an_event();
		tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );

		update_post_meta( $post->ID, Blocks_Provider::META_KEY, '[]' );
		tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $post->ID ) );

		$this->assertCount( 1, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should keep the mirror in sync with the canonical meta
	 *
	 * @test
	 */
	public function should_keep_the_mirror_in_sync_with_the_canonical_meta(): void {
		$post = $this->given_an_event();

		tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );

		$mirror = json_decode( (string) get_post_meta( $post->ID, Blocks_Provider::META_KEY, true ), true );
		$this->assertEquals(
			[ [ 'date' => '2026-11-12', 'start' => '09:00:00', 'end' => '10:00:00' ] ],
			$mirror
		);

		delete_post_meta( $post->ID, '_EventRecurrence' );
		$this->assertEmpty( get_post_meta( $post->ID, Blocks_Provider::META_KEY, true ) );
	}

	/**
	 * It should add the dates attribute and rehydrate a stale mirror
	 *
	 * @test
	 */
	public function should_add_the_dates_attribute_and_rehydrate_a_stale_mirror(): void {
		$post = $this->given_an_event();
		tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );

		// Stale the mirror, as a Pro-era edit would.
		update_post_meta( $post->ID, Blocks_Provider::META_KEY, '[{"date":"2020-01-01","start":"09:00:00","end":"10:00:00"}]' );

		$_GET['post'] = $post->ID;
		try {
			$block_data = tribe( Blocks_Provider::class )->add_block_attribute( [ 'attributes' => [] ] );
		} finally {
			unset( $_GET['post'] );
		}

		$this->assertEquals(
			[ 'type' => 'string', 'source' => 'meta', 'meta' => Blocks_Provider::META_KEY ],
			$block_data['attributes']['dates']
		);
		$mirror = json_decode( (string) get_post_meta( $post->ID, Blocks_Provider::META_KEY, true ), true );
		$this->assertEquals( [ [ 'date' => '2026-11-12', 'start' => '09:00:00', 'end' => '10:00:00' ] ], $mirror );
	}

	/**
	 * It should expose the locked event dates summary in the editor config
	 *
	 * @test
	 */
	public function should_expose_the_locked_dates_summary_in_the_editor_config(): void {
		$post = $this->given_an_event();

		// A rule-based rset with no authored meta: locked for free authoring.
		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ]
		);

		$_GET['post'] = $post->ID;
		try {
			$config = tribe( Blocks_Provider::class )->add_editor_config( [ 'events' => [] ] );
		} finally {
			unset( $_GET['post'] );
		}

		$recurrence_dates = $config['events']['recurrenceDates'];
		$this->assertTrue( $recurrence_dates['locked'] );
		// The event's own occurrence is the only generated row here.
		$this->assertEquals( 1, $recurrence_dates['summary']['count'] );
		$this->assertCount( 1, $recurrence_dates['summary']['nextDates'] );
		$this->assertIsString( $recurrence_dates['summary']['nextDates'][0] );
	}

	/**
	 * It should expose an empty summary for unlocked events
	 *
	 * @test
	 */
	public function should_expose_an_empty_summary_for_unlocked_events(): void {
		$post = $this->given_an_event();

		$_GET['post'] = $post->ID;
		try {
			$config = tribe( Blocks_Provider::class )->add_editor_config( [ 'events' => [] ] );
		} finally {
			unset( $_GET['post'] );
		}

		$recurrence_dates = $config['events']['recurrenceDates'];
		$this->assertFalse( $recurrence_dates['locked'] );
		$this->assertEquals( [ 'count' => 0, 'nextDates' => [] ], $recurrence_dates['summary'] );
	}
}
