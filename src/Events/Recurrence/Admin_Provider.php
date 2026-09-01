<?php
/**
 * Registers the Classic Editor authoring surface of the Recurrence feature.
 *
 * The Event Dates section lets an editor author the additional, explicit dates of an
 * Event one by one; rule-based recurrence stays an Events Calendar Pro feature. The
 * section renders inside the Events datetime metabox section, where Events Calendar
 * Pro mounts its recurrence UI, and its rows map to the AUTHORED `_EventRecurrence`
 * date rules, mirroring the Pro authoring model.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateTimeImmutable;
use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;

/**
 * Class Admin_Provider.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Admin_Provider extends Service_Provider {
	/**
	 * The name of the posted dates field.
	 *
	 * @since TBD
	 */
	public const FIELD = 'tec_events_recurrence_dates';

	/**
	 * The nonce action of the Event Dates section.
	 *
	 * @since TBD
	 */
	public const NONCE_ACTION = 'tec_events_recurrence_dates_save';

	/**
	 * Registers the Event Dates section and its save handler.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		$this->container->singleton( self::class, $this );

		if ( class_exists( 'Tribe__Events__Pro__Main', false ) ) {
			// Events Calendar Pro provides the full recurrence UI.
			return;
		}

		if ( ! has_action( 'tribe_events_date_display', [ $this, 'render_section' ] ) ) {
			// Below the date picker, above the Events Calendar Pro upsell (18); Pro renders at 10.
			add_action( 'tribe_events_date_display', [ $this, 'render_section' ], 15 );
		}

		if ( ! has_action( 'tribe_events_update_meta', [ $this, 'save_dates' ] ) ) {
			// The same hook and priority Events Calendar Pro consumes classic recurrence on.
			add_action( 'tribe_events_update_meta', [ $this, 'save_dates' ], 20 );
		}
	}

	/**
	 * Unregisters the hooks managed by the provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'tribe_events_date_display', [ $this, 'render_section' ], 15 );
		remove_action( 'tribe_events_update_meta', [ $this, 'save_dates' ], 20 );
	}

	/**
	 * Renders the Event Dates section inside the Events datetime metabox section.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The Event post ID; `0` when a new Event is being created.
	 *
	 * @return void
	 */
	public function render_section( $event_id = 0 ): void {
		$event_id = (int) $event_id;
		$guard    = $this->container->make( Authoring_Guard::class );

		$is_occurrence        = $event_id > 0 && $guard->is_occurrence_edit( $event_id );
		$is_locked            = ! $is_occurrence && $event_id > 0 && $guard->is_rule_locked( $event_id );
		$occurrence_edit_link = '';
		$rows                 = [];
		$summary              = [
			'count'      => 0,
			'next_dates' => [],
		];

		if ( $is_occurrence ) {
			// Built directly: link filters would rewrite the parent Event link back to the Occurrence.
			$occurrence_edit_link = admin_url( 'post.php?post=' . Occurrence::normalize_id( $event_id ) . '&action=edit' );
		} elseif ( $is_locked ) {
			$summary = $this->format_summary( $guard->get_dates_summary( $event_id ) );
		} elseif ( $event_id > 0 ) {
			$rows = array_map(
				static function ( array $period ): array {
					return [
						'date'  => $period['start']->format( 'Y-m-d' ),
						'start' => $period['start']->format( 'H:i' ),
						'end'   => $period['end']->format( 'H:i' ),
					];
				},
				$guard->get_authored_periods( $event_id )
			);
		}

		include TEC::instance()->pluginPath . 'src/admin-views/recurrence/event-dates.php';
	}

	/**
	 * Formats the dates summary of a locked Event for display.
	 *
	 * @since TBD
	 *
	 * @param array{count: int, next_dates: array<int,DateTimeImmutable>} $summary The dates summary.
	 *
	 * @return array{count: int, next_dates: array<int,string>} The summary with the dates formatted for display.
	 */
	private function format_summary( array $summary ): array {
		$format = tribe_get_datetime_format( true );

		$summary['next_dates'] = array_map(
			static function ( DateTimeImmutable $date ) use ( $format ): string {
				return date_i18n( $format, (int) $date->format( 'U' ) );
			},
			$summary['next_dates']
		);

		return $summary;
	}

	/**
	 * Saves the additional dates posted from the Event Dates section.
	 *
	 * Runs on `tribe_events_update_meta`, after the Event date meta was saved. The
	 * Dates_Service might read the pre-save Event row dates within this request;
	 * the Custom Tables update re-derives the RSET from the canonical meta at
	 * commit time, so the state converges.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The Event post ID.
	 *
	 * @return void
	 */
	public function save_dates( $event_id ): void {
		$nonce = tribe_get_request_var( self::NONCE_ACTION . '_nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			// The Event Dates section was not rendered on this save.
			return;
		}

		$event_id = (int) $event_id;

		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| wp_is_post_revision( $event_id )
			|| wp_is_post_autosave( $event_id )
			|| ! current_user_can( 'edit_post', $event_id )
		) {
			return;
		}

		$guard = $this->container->make( Authoring_Guard::class );

		if ( $guard->is_occurrence_edit( $event_id ) || $guard->is_rule_locked( $event_id ) ) {
			// A single Occurrence screen, or Pro rule data: not authored here.
			return;
		}

		$rows  = tribe_get_request_var( self::FIELD, [] );
		$dates = [];

		foreach ( (array) $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['date'] ) || empty( $row['start'] ) || empty( $row['end'] ) ) {
				continue;
			}

			$date  = sanitize_text_field( (string) $row['date'] );
			$start = sanitize_text_field( (string) $row['start'] );
			$end   = sanitize_text_field( (string) $row['end'] );

			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! preg_match( '/^\d{2}:\d{2}$/', $start ) || ! preg_match( '/^\d{2}:\d{2}$/', $end ) ) {
				continue;
			}

			if ( $end <= $start ) {
				// Same-day authoring only: an end before the start would author a negative duration.
				continue;
			}

			$dates[] = [
				'start' => "{$date} {$start}:00",
				'end'   => "{$date} {$end}:00",
			];
		}

		$service = $this->container->make( Dates_Service::class );

		if ( count( $dates ) ) {
			$service->set_dates( $event_id, $dates );
		} else {
			$service->remove_dates( $event_id );
		}
	}
}
