<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Admin_ProviderTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * @before
	 */
	public function pin_datepicker_format(): void {
		// The rows render dates in the datepicker display format: pin it for the assertions.
		tribe_update_option( 'datepickerFormat', 0 );
	}

	/**
	 * @after
	 */
	public function reset_request_state(): void {
		unset( $_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ], $_POST[ Admin_Provider::FIELD ] );
		tribe_remove_option( Settings::LOCK_OPTION );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		wp_set_current_user( 0 );
	}

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Admin Provider Test Event',
				'status'     => 'publish',
				'start_date' => '2050-01-05 09:00:00',
				'end_date'   => '2050-01-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	private function given_a_rset_only_rrule_event(): WP_Post {
		$post = $this->given_an_event();

		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ]
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
	 * It should save valid rows
	 *
	 * @test
	 */
	public function should_save_valid_rows(): void {
		$post = $this->given_an_event();

		$this->post_dates(
			[
				[ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ],
			]
		);

		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 2, $dates );
		$this->assertEquals( '2050-01-12 09:00:00', $dates[1]['start'] );
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
			[ 'date' => '2050-01-01', 'start' => '08:00', 'end' => '09:00' ],
			[ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ],
		];
		$this->post_dates( $rows );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$html = $this->render_section( $post->ID );
		$this->assertStringContainsString( 'value="2050-01-01"', $html );
		$this->assertStringContainsString( 'value="2050-01-12"', $html );
		$this->assertStringNotContainsString( 'value="2050-01-05"', $html );

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

		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertEquals( $rset, (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should render every scheduled date of a locked event as a chip, past ones collapsed
	 *
	 * @test
	 */
	public function should_render_the_scheduled_dates_of_a_locked_event_as_chips(): void {
		$post = $this->given_a_multi_date_event(
			[
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '+10 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '+10 days' ) ) ],
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '+20 days' ) ) ],
			],
			[
				'start_date' => date( 'Y-m-d 09:00:00', strtotime( '-10 days' ) ),
				'end_date'   => date( 'Y-m-d 10:00:00', strtotime( '-10 days' ) ),
			]
		);
		// A rule-based RSET with no authored meta locks the section; the engine freezes the existing rows.
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART;TZID=UTC:20500103T090000\nRRULE:FREQ=WEEKLY;COUNT=5" ] );

		$html = $this->render_section( $post->ID );

		$this->assertStringContainsString( 'tec-events-recurrence-dates--locked', $html );
		$this->assertStringContainsString( '3 dates are scheduled.', $html );
		$this->assertEquals( 3, substr_count( $html, 'class="tec-events-recurrence-dates__chip ' ) );
		$this->assertEquals( 3, substr_count( $html, 'role="tooltip"' ) );
		$this->assertEquals( 1, substr_count( $html, 'tec-events-recurrence-dates__chip--next' ) );
		$this->assertEquals( 1, substr_count( $html, 'tec-events-recurrence-dates__chip--upcoming' ) );
		$this->assertEquals( 1, substr_count( $html, 'tec-events-recurrence-dates__chip--past' ) );
		$this->assertStringContainsString( 'Next occurrence', $html );
		// Each chip pairs with an edit button opening the occurrence edit screen in a new tab.
		$this->assertEquals( 3, substr_count( $html, 'tec-events-recurrence-dates__chip-edit' ) );
		// `esc_url()` encodes the query separator as `&#038;`.
		$this->assertEquals( 3, preg_match_all( '/post\.php\?post=\d+(?:&|&amp;|&#038;)action=edit/', $html ) );
		// The past date is collapsed behind the toggle.
		$this->assertStringContainsString( 'Show 1 past date<', $html );
		$this->assertStringContainsString( 'tec-events-recurrence-dates__chips--past', $html );
		$this->assertStringNotContainsString( '<input', $html );
	}

	/**
	 * It should show the occurrence notice on provisional edit screens
	 *
	 * @test
	 */
	public function should_show_the_occurrence_notice_on_provisional_edit_screens(): void {
		$post = $this->given_an_event();
		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ] );
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
				'rset' => "DTSTART;TZID=America/Sao_Paulo:20500105T090000\n"
						. "RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20500112T090000/20500112T100000\n"
						. 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20500105T090000/20500105T100000',
			]
		);

		$html = $this->render_section( $post->ID );
		$this->assertStringContainsString( 'value="2050-01-12"', $html );

		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ] );
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

		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );
		$this->assertCount( 2, tribe( Dates_Service::class )->get_dates( $post->ID ) );

		$this->post_dates( [] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 1, $dates );
		$this->assertEquals( '2050-01-05 09:00:00', $dates[0]['start'] );
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

		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ] );
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

		$_POST[ Admin_Provider::FIELD ] = [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00' ] ];
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$this->assertCount( 1, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should author an all day date ignoring the row times
	 *
	 * @test
	 */
	public function should_author_an_all_day_date_ignoring_the_row_times(): void {
		$post = $this->given_an_event();

		$this->post_dates( [ [ 'date' => '2050-01-12', 'start' => '09:00', 'end' => '10:00', 'allday' => 'yes' ] ] );
		tribe( Admin_Provider::class )->save_dates( $post->ID );

		$dates = tribe( Dates_Service::class )->get_dates( $post->ID );
		$this->assertCount( 2, $dates );
		$this->assertEquals( '2050-01-12 00:00:00', $dates[1]['start'] );
		// The authored meta stores times without seconds: 23:59 either way.
		$this->assertStringStartsWith( '2050-01-12 23:59', $dates[1]['end'] );

		// The row renders back as an all-day one.
		$this->assertStringContainsString( 'tec-events-recurrence-dates-row--allday', $this->render_section( $post->ID ) );
	}

	/**
	 * It should flag the date controls as locked for rule locked events
	 *
	 * @test
	 */
	public function should_flag_the_date_controls_as_locked_for_rule_locked_events(): void {
		$locked = $this->given_an_event();
		Event::find( $locked->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=3" ]
		);
		$plain = $this->given_an_event();

		$_GET['post'] = $locked->ID;
		try {
			$vars = apply_filters( 'tribe_events_meta_box_vars', [ 'existing' => 1 ], null );
		} finally {
			unset( $_GET['post'] );
		}

		$this->assertEquals( 1, $vars['existing'] );
		$this->assertTrue( $vars['dates_locked'] );
		$this->assertEquals( Admin_Provider::LOCK_REASON_ID, $vars['dates_locked_describedby'] );

		$_GET['post'] = $plain->ID;
		try {
			$vars = apply_filters( 'tribe_events_meta_box_vars', [ 'existing' => 1 ], null );
		} finally {
			unset( $_GET['post'] );
		}

		$this->assertArrayNotHasKey( 'dates_locked', $vars );

		// An Occurrence of a locked Event follows the Event's frozen rules: its controls lock too.
		$occurrence     = Occurrence::where( 'post_id', '=', $locked->ID )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		$_GET['post']   = $provisional_id;
		try {
			$vars = apply_filters( 'tribe_events_meta_box_vars', [], null );
		} finally {
			unset( $_GET['post'] );
		}

		$this->assertTrue( $vars['dates_locked'] );
		$this->assertEquals( Admin_Provider::LOCK_REASON_ID, $vars['dates_locked_describedby'] );
	}

	/**
	 * It should lock the section on an occurrence of a rule locked event
	 *
	 * @test
	 */
	public function should_lock_the_section_on_an_occurrence_of_a_rule_locked_event(): void {
		$post           = $this->given_a_rset_only_rrule_event();
		$occurrence     = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		tribe_update_option( Settings::LOCK_OPTION, true );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $this->render_section( $provisional_id );

		$this->assertStringContainsString( 'tec-events-recurrence-dates--locked', $html );
		$this->assertStringContainsString( 'tec-events-recurrence-dates--occurrence', $html );
		$this->assertStringContainsString( 'tec-events-recurrence-dates--lock-enabled', $html );
		$this->assertStringContainsString( 'This is one date of an event', $html );
		$this->assertStringContainsString( 'post.php?post=' . $post->ID . '&#038;action=edit', $html, 'The notice links to the recurring Event.' );
		$this->assertStringContainsString( 'tab=' . Settings::TAB_SLUG, $html, 'With the lock on, the notice links to the setting.' );
		$this->assertStringNotContainsString( 'single occurrence', $html, 'Not the dates-only occurrence paragraph.' );
		$this->assertStringNotContainsString( 'tec-events-recurrence-dates__chips', $html, 'The chips belong to the Event screen.' );
		$this->assertStringNotContainsString( 'form="', $html );
		$this->assertStringNotContainsString( 'Convert to individual dates', $html );

		ob_start();
		tribe( Admin_Provider::class )->render_convert_form();
		$this->assertSame( '', ob_get_clean(), 'No conversion form with the lock on.' );

		// The metabox date controls lock like the Event's, mirroring the Occurrence's own dates.
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );
		$_GET['post']    = $provisional_id;
		$GLOBALS['post'] = get_post( $provisional_id );
		try {
			ob_start();
			new \Tribe__Events__Admin__Event_Meta_Box( get_post( $provisional_id ) );
			$metabox_html = ob_get_clean();
		} finally {
			unset( $_GET['post'], $GLOBALS['post'] );
		}

		$this->assertRegExp( '/id="EventStartDate"[^>]*\sdisabled/s', $metabox_html );
		$this->assertRegExp( '/id="EventEndDate"[^>]*\sdisabled/s', $metabox_html );
		$this->assertStringContainsString( 'tribe-datetime-block--locked', $metabox_html );
		$this->assertStringContainsString( '<input type="hidden" name="EventStartDate" value="2050-01-05"', $metabox_html );
	}

	/**
	 * It should offer the conversion on an occurrence of a rule locked event when the lock is off
	 *
	 * @test
	 */
	public function should_offer_the_conversion_on_an_occurrence_of_a_rule_locked_event_when_the_lock_is_off(): void {
		$post           = $this->given_a_rset_only_rrule_event();
		$occurrence     = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		tribe_update_option( Settings::LOCK_OPTION, false );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $this->render_section( $provisional_id );

		$this->assertStringContainsString( 'tec-events-recurrence-dates--occurrence', $html );
		$this->assertStringContainsString( 'tec-events-recurrence-dates--convertible', $html );
		$this->assertStringContainsString( 'until the event is converted', $html );
		$this->assertStringContainsString( 'Converting sends you to the recurring event.', $html );
		$this->assertStringContainsString( 'name="' . Updates\Rules_Conversion_Request::ACK_FIELD . '"', $html );
		$this->assertStringContainsString( 'form="' . Updates\Rules_Conversion_Request::FORM_ID . '"', $html );
		$this->assertStringContainsString( 'Convert to individual dates', $html );
		$this->assertStringNotContainsString( '<form', $html );

		// The footer form posts, and is nonced for, the real Event: the request normalizes the ID.
		ob_start();
		tribe( Admin_Provider::class )->render_convert_form();
		$form = ob_get_clean();

		$this->assertStringContainsString( 'name="' . Updates\Rules_Conversion_Request::POST_FIELD . '" value="' . $post->ID . '"', $form );
		$this->assertRegExp( '/name="_wpnonce" value="([^"]+)"/', $form );
		preg_match( '/name="_wpnonce" value="([^"]+)"/', $form, $matches );
		$this->assertNotFalse( wp_verify_nonce( $matches[1], Updates\Rules_Conversion_Request::NONCE_ACTION . $post->ID ) );
	}

	/**
	 * It should render disabled date controls for a rule locked event
	 *
	 * @test
	 */
	public function should_render_disabled_date_controls_for_a_rule_locked_event(): void {
		$locked = $this->given_an_event();
		Event::find( $locked->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=3" ]
		);
		$plain = $this->given_an_event();

		$_GET['post'] = $locked->ID;
		// The linked posts fields the metabox includes read the global post.
		$GLOBALS['post'] = get_post( $locked->ID );
		try {
			ob_start();
			new \Tribe__Events__Admin__Event_Meta_Box( get_post( $locked->ID ) );
			$locked_html = ob_get_clean();
		} finally {
			unset( $_GET['post'], $GLOBALS['post'] );
		}

		$this->assertRegExp( '/id="EventStartDate"[^>]*\sdisabled/s', $locked_html );
		$this->assertRegExp( '/id="EventEndDate"[^>]*\sdisabled/s', $locked_html );
		$this->assertRegExp( '/id="allDayCheckbox"[^>]*\sdisabled/s', $locked_html );
		$this->assertRegExp( '/id="event-timezone"[^>]*\sdisabled/s', $locked_html );
		$this->assertStringContainsString( 'tribe-datetime-block--locked', $locked_html );
		$this->assertStringContainsString( '<input type="hidden" name="EventStartDate" value="2050-01-05"', $locked_html );
		$this->assertStringContainsString( '<input type="hidden" name="EventTimezone" value="America/Sao_Paulo"', $locked_html );
		$this->assertStringContainsString( 'aria-describedby="' . Admin_Provider::LOCK_REASON_ID . '"', $locked_html );

		$_GET['post']    = $plain->ID;
		$GLOBALS['post'] = get_post( $plain->ID );
		try {
			ob_start();
			new \Tribe__Events__Admin__Event_Meta_Box( get_post( $plain->ID ) );
			$plain_html = ob_get_clean();
		} finally {
			unset( $_GET['post'], $GLOBALS['post'] );
		}

		$this->assertNotRegExp( '/id="EventStartDate"[^>]*\sdisabled/s', $plain_html );
		$this->assertStringNotContainsString( 'tribe-datetime-block--locked', $plain_html );
		$this->assertStringNotContainsString( '<input type="hidden" name="EventStartDate"', $plain_html );
	}
}
