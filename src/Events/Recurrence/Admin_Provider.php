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

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use WP_Post;

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

		if ( ! has_action( 'add_meta_boxes_' . TEC::POSTTYPE, [ $this, 'register_occurrences_metabox' ] ) ) {
			add_action( 'add_meta_boxes_' . TEC::POSTTYPE, [ $this, 'register_occurrences_metabox' ] );
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
		remove_action( 'add_meta_boxes_' . TEC::POSTTYPE, [ $this, 'register_occurrences_metabox' ] );
	}

	/**
	 * Registers the Scheduled Dates metabox for Events with more than one Occurrence.
	 *
	 * The metabox renders in the Classic Editor and in the Block Editor metaboxes
	 * area alike, and is display-only: editing a single Occurrence requires the
	 * scoped-updates machinery, not available yet.
	 *
	 * @since TBD
	 *
	 * @param WP_Post|mixed $post The post the edit screen is rendering.
	 *
	 * @return void
	 */
	public function register_occurrences_metabox( $post ): void {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( tribe( Occurrences_List::class )->get_count( (int) $post->ID ) < 2 ) {
			// A single-date Event has nothing to list.
			return;
		}

		add_meta_box(
			'tec-events-recurrence-occurrences',
			__( 'Scheduled Dates', 'the-events-calendar' ),
			[ $this, 'render_occurrences_metabox' ],
			TEC::POSTTYPE,
			'normal',
			'low'
		);
	}

	/**
	 * Renders the Scheduled Dates metabox.
	 *
	 * @since TBD
	 *
	 * @param WP_Post|mixed $post The post the metabox is rendered for.
	 *
	 * @return void
	 */
	public function render_occurrences_metabox( $post ): void {
		$event_id = $post instanceof WP_Post ? (int) $post->ID : 0;
		$data     = tribe( Occurrences_List::class )->get_page_data( $event_id );

		include TEC::instance()->pluginPath . 'src/admin-views/recurrence/occurrences-list.php';
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
		$chips                = [
			'count'    => 0,
			'upcoming' => [],
			'past'     => [],
		];

		if ( $is_occurrence ) {
			// Built directly: link filters would rewrite the parent Event link back to the Occurrence.
			$occurrence_edit_link = admin_url( 'post.php?post=' . Occurrence::normalize_id( $event_id ) . '&action=edit' );
		} elseif ( $is_locked ) {
			$chips = $this->get_chips( $event_id );
		} elseif ( $event_id > 0 ) {
			// The same display formats the Start/End pickers above the section use.
			$date_format = \Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );
			$time_format = \Tribe__View_Helpers::is_24hr_format() ? 'H:i' : 'g:ia';

			$rows = array_map(
				static function ( array $period ) use ( $date_format, $time_format ): array {
					// The authored meta stores times without seconds: an all-day date spans 00:00 to 23:59.
					$all_day = '00:00' === $period['start']->format( 'H:i' ) && '23:59' === $period['end']->format( 'H:i' );

					return [
						'date'   => $period['start']->format( $date_format ),
						'start'  => $period['start']->format( $time_format ),
						'end'    => $period['end']->format( $time_format ),
						'allday' => $all_day,
					];
				},
				$guard->get_authored_periods( $event_id )
			);
		}

		include TEC::instance()->pluginPath . 'src/admin-views/recurrence/event-dates.php';
	}

	/**
	 * Builds the scheduled dates chips of a locked Event, split between upcoming and past.
	 *
	 * @since TBD
	 *
	 * @param int $event_id The Event post ID.
	 *
	 * @return array{
	 *     count: int,
	 *     upcoming: array<int,array{label: string, tooltip: array<int,string>, permalink: string, status: string}>,
	 *     past: array<int,array{label: string, tooltip: array<int,string>, permalink: string, status: string}>
	 * } The chips: the upcoming ones (the next one first) and the past ones, oldest first.
	 */
	private function get_chips( int $event_id ): array {
		$list  = $this->container->make( Occurrences_List::class );
		$chips = [
			'count'    => 0,
			'upcoming' => [],
			'past'     => [],
		];

		foreach ( $list->get_scheduled_dates( $event_id ) as $row ) {
			$chip = $list->format_chip( $row );

			$chips[ 'past' === $chip['status'] ? 'past' : 'upcoming' ][] = $chip;
			++$chips['count'];
		}

		return $chips;
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

		// The rows post the same formats the Start/End pickers do: parse them the way the API does.
		$datepicker_format = \Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

		foreach ( (array) $rows as $row ) {
			$all_day = is_array( $row ) && tribe_is_truthy( $row['allday'] ?? '' );

			if ( ! is_array( $row ) || empty( $row['date'] ) || ( ! $all_day && ( empty( $row['start'] ) || empty( $row['end'] ) ) ) ) {
				continue;
			}

			$date = \Tribe__Date_Utils::datetime_from_format( $datepicker_format, sanitize_text_field( (string) $row['date'] ) );

			if ( ! is_string( $date ) || '' === $date || false === strtotime( $date ) ) {
				continue;
			}

			if ( $all_day ) {
				$dates[] = [
					'start' => "{$date} 00:00:00",
					'end'   => "{$date} 23:59:59",
				];

				continue;
			}

			$start = strtotime( $date . ' ' . sanitize_text_field( (string) $row['start'] ) );
			$end   = strtotime( $date . ' ' . sanitize_text_field( (string) $row['end'] ) );

			if ( false === $start || false === $end ) {
				continue;
			}

			if ( $end <= $start ) {
				// Same-day authoring only: an end before the start would author a negative duration.
				continue;
			}

			$dates[] = [
				// The strings were parsed as UTC: gmdate() round-trips the wall time exactly.
				'start' => gmdate( 'Y-m-d H:i:s', $start ),
				'end'   => gmdate( 'Y-m-d H:i:s', $end ),
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
