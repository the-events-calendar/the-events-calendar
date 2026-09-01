<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_Post;

class Admin_ProviderTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The rows render dates in the datepicker display format: pin it for the assertions.
		tribe_update_option( 'datepickerFormat', 0 );
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
		unset( $_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ], $_POST[ Admin_Provider::FIELD ] );
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
				'title'      => 'Admin Provider Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	private function given_a_rset_only_rrule_event(): WP_Post {
		$post = $this->given_an_event();

		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ]
		);

		return $post;
	}

	private function post_dates( array $rows ): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ] = wp_create_nonce( Admin_Provider::NONCE_ACTION );
		$_POST[ Admin_Provider::FIELD ]                   = $rows;
	}

	private function render_section( int $event_id ): string {
		ob_start();
		tribe( Admin_Provider::class )->render_section( $event_id );

		return (string) ob_get_clean();
	}

	/**
	 * It should save valid rows and skip malformed ones
	 *
	 * @test
	 */
	public function should_save_valid_rows_and_skip_malformed_ones(): void {
		$post = $this->given_an_event();

		$this->post_dates(
			[
				[ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ],
				[ 'date' => 'not-a-date', 'start' => '09:00', 'end' => '10:00' ],
				[ 'date' => '2026-11-19', 'start' => '11:00', 'end' => '10:00' ], // End before start.
			]
		);

		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 2, $dates );
		$this->assertEquals( '2026-11-12 09:00:00', $dates[1]['start'] );
		$this->assertTrue( tribe_is_recurring_event( $post->ID ) );
		// The Links layer calls this as a Model method: the extension must provide it.
		$this->assertTrue( Event::find( $post->ID, 'post_id' )->has_recurrence() );
	}

	/**
	 * It should render the authored rows regardless of date ordering
	 *
	 * @test
	 */
	public function should_render_the_authored_rows_regardless_of_date_ordering(): void {
		$post = $this->given_an_event();

		// One date EARLIER than the event date: the rows must not be positional.
		$rows = [
			[ 'date' => '2026-11-01', 'start' => '08:00', 'end' => '09:00' ],
			[ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ],
		];
		$this->post_dates( $rows );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$html = $this->render_section( $post->ID );
		$this->assertStringContainsString( 'value="2026-11-01"', $html );
		$this->assertStringContainsString( 'value="2026-11-12"', $html );
		$this->assertStringNotContainsString( 'value="2026-11-05"', $html );

		// Re-saving the same rows twice must be stable: no date silently eaten.
		$this->post_dates( $rows );
		tribe( Admin_Provider::class )->save_dates( $post->ID );
		$this->post_dates( $rows );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertCount( 3, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should protect rset only rule based events
	 *
	 * @test
	 */
	public function should_protect_rset_only_rule_based_events(): void {
		$post = $this->given_a_rset_only_rrule_event();
		$rset = (string) Event::find( $post->ID, 'post_id' )->rset;

		$html = $this->render_section( $post->ID );
		$this->assertStringContainsString( 'recurrence rules', $html );
		$this->assertStringNotContainsString( '<input', $html );

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertEquals( $rset, (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should show the occurrence notice on provisional edit screens
	 *
	 * @test
	 */
	public function should_show_the_occurrence_notice_on_provisional_edit_screens(): void {
		$post = $this->given_an_event();
		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$occurrence     = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );

		$html = $this->render_section( $provisional_id );
		$this->assertStringContainsString( 'single occurrence', $html );
		$this->assertStringNotContainsString( '<input', $html );

		// A save posted from an Occurrence screen must not touch the set of dates.
		$this->post_dates( [] );
		tribe( Admin_Provider::class )->save_dates( $provisional_id );
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should derive rows from a dates only rset without meta and repair on save
	 *
	 * @test
	 */
	public function should_derive_rows_from_a_dates_only_rset_without_meta_and_repair_on_save(): void {
		$post = $this->given_an_event();
		Event::find( $post->ID, 'post_id' )->update(
			[
				'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\n"
						. "RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261112T090000/20261112T100000\n"
						. 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261105T090000/20261105T100000',
			]
		);

		$html = $this->render_section( $post->ID );
		$this->assertStringContainsString( 'value="2026-11-12"', $html );

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		// Saving repaired the canonical meta from the derived rows.
		$meta = get_post_meta( $post->ID, '_EventRecurrence', true );
		$this->assertTrue( Date_Rules::is_dates_only_meta( $meta ) );
		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 2, $dates );
	}

	/**
	 * It should collapse the event when all rows are removed
	 *
	 * @test
	 */
	public function should_collapse_the_event_when_all_rows_are_removed(): void {
		$post = $this->given_an_event();

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );

		$this->post_dates( [] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 1, $dates );
		$this->assertEquals( '2026-11-05 09:00:00', $dates[0]['start'] );
		$this->assertFalse( tribe_is_recurring_event( $post->ID ) );
	}

	/**
	 * It should not touch rule based recurrence meta
	 *
	 * @test
	 */
	public function should_not_touch_rule_based_recurrence_meta(): void {
		$post = $this->given_an_event();
		$meta = [ 'rules' => [ [ 'type' => 'Weekly' ] ] ];
		update_post_meta( $post->ID, '_EventRecurrence', $meta );

		$this->post_dates( [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertEquals( $meta, get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should ignore a save without the section nonce
	 *
	 * @test
	 */
	public function should_ignore_a_save_without_the_section_nonce(): void {
		$post = $this->given_an_event();

		$_POST[ Admin_Provider::FIELD ] = [ [ 'date' => '2026-11-12', 'start' => '09:00', 'end' => '10:00' ] ];
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertCount( 1, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}
}
