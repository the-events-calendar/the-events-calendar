<?php
/**
 * Builds the scheduled dates of an Event for the admin edit screen.
 *
 * The single source of the per-Occurrence data the admin displays: the Event Dates
 * chips of a locked Event, in both editors, read every Occurrence generated for the
 * Event from here, with its status and links.
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
	 * Returns every scheduled date of the Event, oldest first, with its status and link.
	 *
	 * The single source of the per-Occurrence data the admin displays (the Event Dates
	 * chips in both editors). Dates are built in the Event timezone: the Occurrences
	 * table stores Event-local wall-clock values.
	 *
	 * @since TBD
	 *
	 * @param int    $post_id The Event post ID (a provisional ID is accepted).
	 * @param string $scope  All dates or only past dates.
	 * @param int    $limit  Maximum rows; zero retains the legacy unbounded result.
	 * @param int    $offset Number of rows already loaded.
	 * @param int    $as_of  UTC timestamp fixing the past boundary for pagination.
	 *
	 * @return array<int,array{
	 *     provisional_id: int,
	 *     start: DateTimeImmutable,
	 *     end: DateTimeImmutable,
	 *     status: string,
	 *     permalink: string
	 * }> The scheduled dates; `status` is one of `past`, `next` (the first upcoming one) or `upcoming`.
	 */
	public function get_scheduled_dates( int $post_id, string $scope = 'all', int $limit = 0, int $offset = 0, int $as_of = 0 ): array {
		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			// The Occurrences table might not exist at all.
			return [];
		}

		$post_id = Occurrence::normalize_id( $post_id );
		$as_of   = $as_of ?: time();
		$query   = Occurrence::where( 'post_id', '=', $post_id );
		if ( 'past' === $scope ) {
			$query->where( 'end_date_utc', '<', gmdate( 'Y-m-d H:i:s', $as_of ) );
		}
		$order = 'past' === $scope ? 'DESC' : 'ASC';
		$query->order_by( 'start_date_utc', $order )->order_by( 'occurrence_id', $order );
		if ( $limit > 0 ) {
			$query->limit( $limit )->offset( $offset );
		}
		$occurrences = iterator_to_array( $query->all(), false );

		if ( ! count( $occurrences ) ) {
			return [];
		}

		$base_provisional_id = tribe( ID_Generator::class )->current();
		$timezone            = $this->get_event_timezone( $post_id );
		$now                 = $as_of;
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
