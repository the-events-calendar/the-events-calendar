<?php
/**
 * Converts between explicit Occurrence dates and the legacy `_EventRecurrence` meta
 * date rules.
 *
 * The produced shape is the one Events Calendar Pro reads and writes for date-based
 * recurrence (rule `type` `Custom`, custom `type` `Date`), so the two plugins can author
 * and consume each other's data:
 *
 *     [
 *         'type'           => 'Custom',
 *         'custom'         => [
 *             'interval'   => 1,
 *             'type'       => 'Date',
 *             'date'       => [ 'date' => '2026-11-12' ],
 *             'same-time'  => 'no',
 *             'start-time' => '9:00am',
 *             'end-time'   => '10:00am',
 *             'end-day'    => 'same-day',
 *         ],
 *         'EventStartDate' => '2026-11-05 09:00:00',
 *         'EventEndDate'   => '2026-11-05 10:00:00',
 *     ]
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Tribe__Date_Utils;

/**
 * Class Date_Rules.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Date_Rules {
	/**
	 * Returns whether a `_EventRecurrence` meta value only contains date rules, and no
	 * exclusions, or not.
	 *
	 * An empty meta value, or one without rules, is not a dates-only one.
	 *
	 * @since TBD
	 *
	 * @param mixed $recurrence_meta The `_EventRecurrence` meta value.
	 *
	 * @return bool Whether the meta value only contains date rules or not.
	 */
	public static function is_dates_only_meta( $recurrence_meta ): bool {
		if ( ! is_array( $recurrence_meta ) || empty( $recurrence_meta['rules'] ) ) {
			return false;
		}

		if ( ! empty( $recurrence_meta['exclusions'] ) ) {
			return false;
		}

		foreach ( (array) $recurrence_meta['rules'] as $rule ) {
			if (
				! isset( $rule['type'], $rule['custom']['type'], $rule['custom']['date']['date'] )
				|| 'Custom' !== $rule['type']
				|| 'Date' !== $rule['custom']['type']
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Converts a set of periods to the legacy date rules.
	 *
	 * @since TBD
	 *
	 * @param array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> $periods     The additional dates.
	 * @param DateTimeImmutable                                                  $event_start The Event (first Occurrence) start.
	 * @param DateTimeImmutable                                                  $event_end   The Event (first Occurrence) end.
	 *
	 * @return array<string,mixed> The legacy `_EventRecurrence` meta value.
	 */
	public static function to_meta( array $periods, DateTimeImmutable $event_start, DateTimeImmutable $event_end ): array {
		$rules = [];

		foreach ( $periods as $period ) {
			$start = $period['start'];
			$end   = $period['end'];

			$end_days = (int) $start->setTime( 0, 0 )->diff( $end->setTime( 0, 0 ) )->format( '%a' );

			$rules[] = [
				'type'           => 'Custom',
				'custom'         => [
					'interval'   => 1,
					'type'       => 'Date',
					'date'       => [ 'date' => $start->format( 'Y-m-d' ) ],
					'same-time'  => 'no',
					'start-time' => $start->format( 'g:ia' ),
					'end-time'   => $end->format( 'g:ia' ),
					'end-day'    => $end_days > 0 ? $end_days : 'same-day',
				],
				'EventStartDate' => $event_start->format( 'Y-m-d H:i:s' ),
				'EventEndDate'   => $event_end->format( 'Y-m-d H:i:s' ),
			];
		}

		return [
			'rules'       => $rules,
			'exclusions'  => [],
			'description' => null,
		];
	}

	/**
	 * Converts a legacy dates-only `_EventRecurrence` meta value to a set of periods.
	 *
	 * The conversion mirrors the Events Calendar Pro date rule converter: a rule with
	 * `same-time` set uses the Event times and duration, one without uses its own
	 * `start-time`, `end-time` and `end-day` values.
	 *
	 * @since TBD
	 *
	 * @param mixed             $recurrence_meta The `_EventRecurrence` meta value.
	 * @param DateTimeImmutable $event_start     The Event (first Occurrence) start.
	 * @param DateTimeImmutable $event_end       The Event (first Occurrence) end.
	 * @param DateTimeZone      $timezone        The Event timezone.
	 *
	 * @return array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}>|null The
	 *         periods for the additional dates, or `null` when the meta value is not a
	 *         dates-only one.
	 */
	public static function to_periods( $recurrence_meta, DateTimeImmutable $event_start, DateTimeImmutable $event_end, DateTimeZone $timezone ): ?array {
		if ( ! self::is_dates_only_meta( $recurrence_meta ) ) {
			return null;
		}

		// Duration via `format( 'U' )`: PHP < 8 `getTimestamp()` is wrong in the repeated DST hour.
		$duration = (int) $event_end->format( 'U' ) - (int) $event_start->format( 'U' );
		$periods  = [];

		try {
			foreach ( (array) $recurrence_meta['rules'] as $rule ) {
				$custom = $rule['custom'];
				$date   = $custom['date']['date'];

				$start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', $timezone );

				if ( ! $start instanceof DateTimeImmutable ) {
					$start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date, $timezone );
				}

				if ( ! $start instanceof DateTimeImmutable ) {
					return null;
				}

				$same_time = isset( $custom['same-time'] ) && tribe_is_truthy( $custom['same-time'] );

				if ( $same_time || ! isset( $custom['start-time'], $custom['end-time'] ) ) {
					// Use the Event start time and duration.
					$start = $start->setTime( (int) $event_start->format( 'H' ), (int) $event_start->format( 'i' ), (int) $event_start->format( 's' ) );
					$end   = $start->add( new DateInterval( "PT{$duration}S" ) );
				} else {
					$start_time = Tribe__Date_Utils::build_date_object( $custom['start-time'], $timezone );
					$end_time   = Tribe__Date_Utils::build_date_object( $custom['end-time'], $timezone );
					$end_days   = isset( $custom['end-day'] ) ? (int) $custom['end-day'] : 0;

					$start = $start->setTime( (int) $start_time->format( 'H' ), (int) $start_time->format( 'i' ), (int) $start_time->format( 's' ) );
					$end   = $start;

					if ( $end_days > 0 ) {
						$end = $end->add( new DateInterval( "P{$end_days}D" ) );
					}

					$end = $end->setTime( (int) $end_time->format( 'H' ), (int) $end_time->format( 'i' ), (int) $end_time->format( 's' ) );
				}

				$periods[] = [
					'start' => $start,
					'end'   => $end,
				];
			}
		} catch ( Exception $e ) {
			return null;
		}

		return $periods;
	}
}
