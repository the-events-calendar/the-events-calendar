<?php
/**
 * Builds the Scheduled Dates list of an Event for the admin edit screen.
 *
 * The list is display-only: it shows every Occurrence generated for the Event, with
 * a View link per date. Editing a single Occurrence requires the scoped-updates
 * machinery (an Events Calendar Pro port still to be designed), so no Edit links
 * are offered here.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Timezones as Timezones;

/**
 * Class Occurrences_List.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Occurrences_List {
	/**
	 * The query variable selecting the list view.
	 *
	 * @since TBD
	 */
	public const VIEW_VAR = 'tec_dates_view';

	/**
	 * The query variable selecting the list page.
	 *
	 * @since TBD
	 */
	public const PAGE_VAR = 'tec_dates_page';

	/**
	 * Returns the number of Occurrences scheduled for the Event.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return int The number of scheduled Occurrences.
	 */
	public function get_count( int $post_id ): int {
		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			// The Occurrences table might not exist at all.
			return 0;
		}

		$post_id = Occurrence::normalize_id( $post_id );

		return (int) Occurrence::where( 'post_id', '=', $post_id )->count();
	}

	/**
	 * Returns one page of the Event's scheduled dates, with its pagination data.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array{
	 *     view: string,
	 *     page: int,
	 *     per_page: int,
	 *     total: int,
	 *     total_pages: int,
	 *     rows: array<int,array{provisional_id: int, start: DateTimeImmutable, end: DateTimeImmutable}>
	 * } The current page of the list.
	 */
	public function get_page_data( int $post_id ): array {
		$view = 'all' === tribe_get_request_var( self::VIEW_VAR, 'upcoming' ) ? 'all' : 'upcoming';
		$page = max( 1, absint( tribe_get_request_var( self::PAGE_VAR, 1 ) ) );

		/**
		 * Filters the number of scheduled dates shown per page in the Event edit screen list.
		 *
		 * @since TBD
		 *
		 * @param int $per_page The number of dates per page.
		 * @param int $post_id  The Event post ID.
		 */
		$per_page = max( 1, (int) apply_filters( 'tec_events_recurrence_occurrences_list_per_page', 20, $post_id ) );

		$data = [
			'view'        => $view,
			'page'        => $page,
			'per_page'    => $per_page,
			'total'       => 0,
			'total_pages' => 0,
			'rows'        => [],
		];

		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			return $data;
		}

		$post_id = Occurrence::normalize_id( $post_id );

		$builder       = Occurrence::where( 'post_id', '=', $post_id );
		$count_builder = Occurrence::where( 'post_id', '=', $post_id );

		if ( 'upcoming' === $view ) {
			$now = current_time( 'mysql' );
			$builder->where( 'end_date', '>=', $now );
			$count_builder->where( 'end_date', '>=', $now );
		}

		$data['total']       = (int) $count_builder->count();
		$data['total_pages'] = (int) ceil( $data['total'] / $per_page );
		$data['page']        = min( $page, max( 1, $data['total_pages'] ) );

		if ( 0 === $data['total'] ) {
			return $data;
		}

		$occurrences = iterator_to_array(
			$builder->order_by( 'start_date_utc', 'ASC' )
					->limit( $per_page )
					->offset( ( $data['page'] - 1 ) * $per_page )
					->all(),
			false
		);

		$base_provisional_id = tribe( ID_Generator::class )->current();
		$timezone            = $this->get_event_timezone( $post_id );

		foreach ( $occurrences as $occurrence ) {
			try {
				$data['rows'][] = [
					'provisional_id' => $base_provisional_id + (int) $occurrence->occurrence_id,
					// The table stores Event-local wall-clock values: build them in the Event timezone.
					'start'          => new DateTimeImmutable( (string) $occurrence->start_date, $timezone ),
					'end'            => new DateTimeImmutable( (string) $occurrence->end_date, $timezone ),
				];
			} catch ( Exception $e ) {
				continue;
			}
		}

		if ( count( $data['rows'] ) ) {
			// The provisional posts must be in the posts cache for `get_permalink()` to resolve them.
			tribe( Provisional_Post::class )->hydrate_caches( array_column( $data['rows'], 'provisional_id' ) );
		}

		return $data;
	}

	/**
	 * Returns every scheduled date of the Event, oldest first, with its status and link.
	 *
	 * The single source of the per-Occurrence data the admin displays (the Event Dates
	 * chips in both editors). Dates are built in the Event timezone: the Occurrences
	 * table stores Event-local wall-clock values.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array<int,array{
	 *     provisional_id: int,
	 *     start: DateTimeImmutable,
	 *     end: DateTimeImmutable,
	 *     status: string,
	 *     permalink: string
	 * }> The scheduled dates; `status` is one of `past`, `next` (the first upcoming one) or `upcoming`.
	 */
	public function get_scheduled_dates( int $post_id ): array {
		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			// The Occurrences table might not exist at all.
			return [];
		}

		$post_id     = Occurrence::normalize_id( $post_id );
		$occurrences = iterator_to_array(
			Occurrence::where( 'post_id', '=', $post_id )
					->order_by( 'start_date_utc', 'ASC' )
					->all(),
			false
		);

		if ( ! count( $occurrences ) ) {
			return [];
		}

		$base_provisional_id = tribe( ID_Generator::class )->current();
		$timezone            = $this->get_event_timezone( $post_id );
		$now                 = time();
		$next_found          = false;
		$rows                = [];

		foreach ( $occurrences as $occurrence ) {
			try {
				$start = new DateTimeImmutable( (string) $occurrence->start_date, $timezone );
				$end   = new DateTimeImmutable( (string) $occurrence->end_date, $timezone );
			} catch ( Exception $e ) {
				continue;
			}

			// `format( 'U' )` over `getTimestamp()`: PHP 7.4 returns a wrong epoch for ambiguous DST wall times.
			if ( (int) $end->format( 'U' ) < $now ) {
				$status = 'past';
			} elseif ( ! $next_found ) {
				$status     = 'next';
				$next_found = true;
			} else {
				$status = 'upcoming';
			}

			$rows[] = [
				'provisional_id' => $base_provisional_id + (int) $occurrence->occurrence_id,
				'start'          => $start,
				'end'            => $end,
				'status'         => $status,
				'permalink'      => '',
			];
		}

		// The provisional posts must be in the posts cache for `get_permalink()` to resolve them.
		tribe( Provisional_Post::class )->hydrate_caches( array_column( $rows, 'provisional_id' ) );

		foreach ( $rows as &$row ) {
			$permalink        = get_permalink( $row['provisional_id'] );
			$row['permalink'] = is_string( $permalink ) ? $permalink : '';
		}
		unset( $row );

		/**
		 * Filters the scheduled dates of an Event as shown in the admin.
		 *
		 * @since TBD
		 *
		 * @param array<int,array<string,mixed>> $rows    The scheduled dates, oldest first.
		 * @param int                            $post_id The Event post ID.
		 */
		return (array) apply_filters( 'tec_events_recurrence_scheduled_dates', $rows, $post_id );
	}

	/**
	 * Returns the timezone the Event dates are authored in.
	 *
	 * The Occurrences table stores Event-local wall-clock values: this is the timezone
	 * to build and display them in, the same one the Start/End controls show.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return DateTimeZone The Event timezone, or the site one when the Event has none.
	 */
	public function get_event_timezone( int $post_id ): DateTimeZone {
		$event    = Event::find( Occurrence::normalize_id( $post_id ), 'post_id' );
		$timezone = $event instanceof Event ? (string) $event->timezone : '';

		if ( '' === $timezone ) {
			return wp_timezone();
		}

		$object = Timezones::build_timezone_object( $timezone );

		return $object instanceof DateTimeZone ? $object : wp_timezone();
	}

	/**
	 * Formats a scheduled date for display as a chip with a tooltip.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $row One scheduled date, as returned by `get_scheduled_dates()`: `provisional_id`,
	 *                                 `start` and `end` (DateTimeImmutable), `status` and `permalink`.
	 *
	 * @return array{label: string, tooltip: array<int,string>, permalink: string, edit_link: string, status: string} The
	 *                                                       chip data: a short date label, the tooltip lines, the Occurrence
	 *                                                       view and edit links, and the status.
	 */
	public function format_chip( array $row ): array {
		$timezone        = $row['start']->getTimezone();
		$start_timestamp = (int) $row['start']->format( 'U' );
		$end_timestamp   = (int) $row['end']->format( 'U' );
		$datetime_format = tribe_get_datetime_format( true );

		$start_text = wp_date( 'l, ' . $datetime_format, $start_timestamp, $timezone );
		$end_text   = $row['start']->format( 'Y-m-d' ) === $row['end']->format( 'Y-m-d' )
			? wp_date( tribe_get_time_format(), $end_timestamp, $timezone )
			: wp_date( $datetime_format, $end_timestamp, $timezone );

		$statuses = [
			'past'     => __( 'Past', 'the-events-calendar' ),
			'next'     => __( 'Next occurrence', 'the-events-calendar' ),
			'upcoming' => __( 'Upcoming', 'the-events-calendar' ),
		];

		return [
			'label'     => (string) wp_date( tribe_get_date_format( true ), $start_timestamp, $timezone ),
			'tooltip'   => [
				sprintf(
					/* translators: 1: the start date and time of the occurrence. 2: its end time (or end date and time when it spans days). */
					_x( '%1$s – %2$s', 'The scheduled date row of the event, as a start and end range.', 'the-events-calendar' ),
					$start_text,
					$end_text
				),
				$statuses[ $row['status'] ] ?? $statuses['upcoming'],
				__( 'Opens the occurrence in a new tab.', 'the-events-calendar' ),
			],
			'permalink' => (string) $row['permalink'],
			// Built directly: the link filters would rewrite the Occurrence edit link back to the Event.
			'edit_link' => admin_url( 'post.php?post=' . (int) $row['provisional_id'] . '&action=edit' ),
			'status'    => (string) $row['status'],
		];
	}
}
