<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;
use WP_Post;

/**
 * Snapshots the admin-view markup of the Event Dates section. Every dynamic value is
 * pinned: nonce, date picker format, post slug and fixed far-future dates; the Event and
 * provisional post IDs are normalized to placeholders.
 */
class Admin_Views_SnapshotTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;
	use MatchesSnapshots;

	/**
	 * The `REQUEST_URI` value before the test pinned it.
	 *
	 * @var string|null
	 */
	private $request_uri_backup;

	/**
	 * @before
	 */
	public function pin_dynamic_values(): void {
		tribe_update_option( 'datepickerFormat', 0 );
		$this->set_fn_return( 'wp_create_nonce', '2ab7cc6b39' );

		/*
		 * Push the post IDs into a range no date or time text can contain: a small Event
		 * ID (e.g. 17) would make the placeholder normalization ambiguous ("January 17").
		 * Inserting with an explicit ID advances AUTO_INCREMENT without DDL.
		 */
		if ( ! get_post( 90000000 ) ) {
			wp_insert_post(
				[
					'import_id'  => 90000000,
					'post_type'  => 'post',
					'post_title' => 'auto increment filler',
				]
			);
		}
		// The referer field reads the request URI: pin it.
		$this->request_uri_backup  = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI'] = '/wp-admin/post.php';
	}

	/**
	 * @after
	 */
	public function restore_request_uri(): void {
		tribe_remove_option( Settings::LOCK_OPTION );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		wp_set_current_user( 0 );

		if ( null === $this->request_uri_backup ) {
			unset( $_SERVER['REQUEST_URI'] );

			return;
		}

		$_SERVER['REQUEST_URI'] = $this->request_uri_backup;
	}

	/**
	 * A rule-based Event (rules frozen) pinned far in the future, with the rows of its dates-only origin.
	 */
	private function given_a_pinned_rule_locked_event( string $slug ): WP_Post {
		$post = $this->given_a_pinned_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
			],
			$slug,
			// Far future too: the chip statuses (next/upcoming/past) depend on the current time.
			[ 'start_date' => '2050-01-03 09:00:00', 'end_date' => '2050-01-03 10:00:00' ]
		);

		// A rule-based RSET with no authored meta locks the section; the engine freezes the existing rows.
		delete_post_meta( $post->ID, '_EventRecurrence' );
		\TEC\Events\Custom_Tables\V1\Models\Event::find( $post->ID, 'post_id' )
			->update( [ 'rset' => "DTSTART;TZID=UTC:20500103T090000\nRRULE:FREQ=WEEKLY;COUNT=5" ] );

		return $post;
	}

	private function given_a_pinned_event( array $dates = [], string $slug = 'admin-snapshot-event', array $event_args = [] ): WP_Post {
		$post = $this->given_a_multi_date_event( $dates, array_merge( [ 'title' => 'Admin Snapshot Event' ], $event_args ) );
		wp_update_post(
			[
				'ID'        => $post->ID,
				'post_name' => $slug,
			]
		);

		return get_post( $post->ID );
	}

	private function render_section_html( int $event_id ): string {
		ob_start();
		tribe( Admin_Provider::class )->render_section( $event_id );

		return $this->normalize_ids( (string) ob_get_clean(), $event_id );
	}

	/**
	 * Replaces the Event post ID (and, for Occurrence rows, the provisional post IDs)
	 * with stable placeholders, matching only ID-shaped positions.
	 */
	private function normalize_ids( string $html, int $event_id ): string {
		$ids = [ $event_id ];

		foreach ( \TEC\Events\Custom_Tables\V1\Models\Occurrence::where( 'post_id', '=', $event_id )->all() as $occurrence ) {
			$ids[] = tribe( \TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator::class )->current() + (int) $occurrence->occurrence_id;
		}

		// IDs appear as `post=<id>`, `value="<id>"` or path fragments: match whole numbers only.
		foreach ( $ids as $i => $id ) {
			if ( $id <= 0 ) {
				// A `0` Event ID would match every bare zero in the markup (`viewBox`, paddings).
				continue;
			}

			$placeholder = 0 === $i ? '{{EVENT_ID}}' : '{{OCCURRENCE_ID_' . $i . '}}';
			// The boundary classes exclude date and time adjacency: `2050-01-10`, `10:00am`.
			$html = preg_replace( '/(?<![\w.\-:])' . preg_quote( (string) $id, '/' ) . '(?![\w.\-:])/', $placeholder, $html );
		}

		return $html;
	}

	/**
	 * It should render the editable rows section
	 *
	 * @test
	 */
	public function should_render_the_editable_rows_section(): void {
		$post = $this->given_a_pinned_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
				[ 'start' => '2050-01-17 14:30:00', 'end' => '2050-01-17 16:00:00' ],
			]
		);

		$this->assertMatchesSnapshot( $this->render_section_html( $post->ID ) );
	}

	/**
	 * It should render the locked section when the lock is enabled
	 *
	 * @test
	 */
	public function should_render_the_locked_section_when_the_lock_is_enabled(): void {
		$post = $this->given_a_pinned_rule_locked_event( 'admin-snapshot-locked-event' );
		tribe_update_option( Settings::LOCK_OPTION, true );

		$html = $this->render_section_html( $post->ID );

		$this->assertStringContainsString( 'tec-events-recurrence-dates--locked', $html );
		$this->assertStringContainsString( 'tec-events-recurrence-dates--lock-enabled', $html );
		$this->assertStringNotContainsString( 'Convert to individual dates', $html );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertMatchesSnapshot( $html );

		ob_start();
		tribe( Admin_Provider::class )->render_convert_form();
		$this->assertSame( '', ob_get_clean(), 'No conversion form with the lock on.' );
	}

	/**
	 * It should render the convert section when the lock is disabled
	 *
	 * @test
	 */
	public function should_render_the_convert_section_when_the_lock_is_disabled(): void {
		$post = $this->given_a_pinned_rule_locked_event( 'admin-snapshot-convertible-event' );
		tribe_update_option( Settings::LOCK_OPTION, false );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $this->render_section_html( $post->ID );

		$this->assertStringContainsString( 'tec-events-recurrence-dates--convertible', $html );
		$this->assertStringContainsString( 'form="tec-events-recurrence-convert"', $html );
		$this->assertStringNotContainsString( '<form', $html, 'The form must not nest in the post form.' );
		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * It should render the convert form in the admin footer
	 *
	 * @test
	 */
	public function should_render_the_convert_form_in_the_admin_footer(): void {
		$post = $this->given_a_pinned_rule_locked_event( 'admin-snapshot-footer-event' );
		tribe_update_option( Settings::LOCK_OPTION, false );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		// The section remembers the Event the footer form is for.
		$this->render_section_html( $post->ID );

		ob_start();
		tribe( Admin_Provider::class )->render_convert_form();
		$html = $this->normalize_ids( (string) ob_get_clean(), $post->ID );

		$this->assertStringContainsString( 'admin-post.php', $html );
		$this->assertStringContainsString( 'value="2ab7cc6b39"', $html );
		$this->assertStringContainsString( 'value="{{EVENT_ID}}"', $html );
		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * It should not render the convert form without a locked event
	 *
	 * @test
	 */
	public function should_not_render_the_convert_form_without_a_locked_event(): void {
		$post = $this->given_a_pinned_event();
		tribe_update_option( Settings::LOCK_OPTION, false );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->render_section_html( $post->ID );

		ob_start();
		tribe( Admin_Provider::class )->render_convert_form();
		$this->assertSame( '', ob_get_clean() );
	}

	/**
	 * It should render the empty state for new events
	 *
	 * @test
	 */
	public function should_render_the_empty_state_for_new_events(): void {
		$this->assertMatchesSnapshot( $this->render_section_html( 0 ) );
	}
}
