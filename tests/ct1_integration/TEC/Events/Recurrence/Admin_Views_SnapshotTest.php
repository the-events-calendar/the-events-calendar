<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;
use WP_Post;

/**
 * Snapshots the admin-view markup of the Event Dates section and the Scheduled Dates
 * metabox. Every dynamic value is pinned: nonce, date picker format, post slug and fixed
 * far-future dates; the Event and provisional post IDs are normalized to placeholders.
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
		// The referer field and pagination links read the request URI: pin it.
		$this->request_uri_backup  = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI'] = '/wp-admin/post.php';
	}

	/**
	 * @after
	 */
	public function restore_request_uri(): void {
		if ( null === $this->request_uri_backup ) {
			unset( $_SERVER['REQUEST_URI'] );

			return;
		}

		$_SERVER['REQUEST_URI'] = $this->request_uri_backup;
	}

	private function given_a_pinned_event( array $dates = [], string $slug = 'admin-snapshot-event' ): WP_Post {
		$post = $this->given_a_multi_date_event( $dates, [ 'title' => 'Admin Snapshot Event' ] );
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
	 * It should render the locked section for rule based events
	 *
	 * @test
	 */
	public function should_render_the_locked_section_for_rule_based_events(): void {
		$post = $this->given_a_pinned_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
			],
			'admin-snapshot-locked-event'
		);

		// A rule-based RSET locks the section; the engine freezes the existing rows.
		\TEC\Events\Custom_Tables\V1\Models\Event::find( $post->ID, 'post_id' )
			->update( [ 'rset' => "DTSTART;TZID=UTC:20500103T090000\nRRULE:FREQ=WEEKLY;COUNT=5" ] );

		$this->assertMatchesSnapshot( $this->render_section_html( $post->ID ) );
	}

	/**
	 * It should render the empty state for new events
	 *
	 * @test
	 */
	public function should_render_the_empty_state_for_new_events(): void {
		$this->assertMatchesSnapshot( $this->render_section_html( 0 ) );
	}

	/**
	 * It should render the scheduled dates metabox
	 *
	 * @test
	 */
	public function should_render_the_scheduled_dates_metabox(): void {
		$post = $this->given_a_pinned_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
				[ 'start' => '2050-01-17 14:30:00', 'end' => '2050-01-17 16:00:00' ],
			],
			'admin-snapshot-metabox-event'
		);

		ob_start();
		tribe( Admin_Provider::class )->render_occurrences_metabox( get_post( $post->ID ) );
		$html = $this->normalize_ids( (string) ob_get_clean(), $post->ID );

		$this->assertMatchesSnapshot( $html );
	}
}
