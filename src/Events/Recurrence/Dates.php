<?php
/**
 * Serializes and parses dates-only RSET strings.
 *
 * A dates-only RSET models an Event with multiple explicitly listed Occurrences — no
 * recurrence rules. The format is the subset of the RFC 5545 grammar Events Calendar Pro
 * derives for date-based recurrence, so the two plugins can read each other's data:
 *
 *     DTSTART;TZID=America/Sao_Paulo:20261105T090000
 *     RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261112T090000/20261112T100000
 *     RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261105T090000/20261105T100000
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

/**
 * Class Dates.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Dates {
	/**
	 * The date and time format used in RSET strings.
	 *
	 * @since TBD
	 */
	public const RSET_DATETIME_FORMAT = 'Ymd\THis';

	/**
	 * Returns whether an RSET string only contains explicitly listed dates or not.
	 *
	 * @since TBD
	 *
	 * @param string $rset The RSET string to check.
	 *
	 * @return bool Whether the RSET string only contains a DTSTART, DTEND and RDATE lines.
	 */
	public static function is_dates_only( string $rset ): bool {
		$rset = trim( $rset );

		if ( '' === $rset ) {
			return false;
		}

		foreach ( self::get_lines( $rset ) as $line ) {
			if ( ! preg_match( '/^(DTSTART|DTEND|RDATE)[;:]/', $line ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Parses a dates-only RSET string into its timezone, start and periods.
	 *
	 * @since TBD
	 *
	 * @param string $rset             The RSET string to parse.
	 * @param int    $default_duration The duration, in seconds, to use for values that do
	 *                                 not carry an end (RDATEs with a DATE-TIME or DATE value).
	 *
	 * @return array{timezone: DateTimeZone, dtstart: ?DateTimeImmutable, periods: array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}>}|null
	 *         The parsed RSET data, or `null` if the RSET string is not a dates-only one.
	 */
	public static function parse( string $rset, int $default_duration = 7200 ) {
		if ( ! self::is_dates_only( $rset ) ) {
			return null;
		}

		$timezone = new DateTimeZone( 'UTC' );
		$dtstart  = null;
		$dtend    = null;
		$rdates   = [];

		try {
			foreach ( self::get_lines( $rset ) as $line ) {
				list( $property, $value ) = self::split_line( $line );
				list( $name, $params )    = self::split_property( $property );

				$line_timezone = isset( $params['TZID'] ) ? new DateTimeZone( $params['TZID'] ) : null;

				if ( 'DTSTART' === $name ) {
					$timezone = $line_timezone ?: $timezone;
					$dtstart  = self::parse_datetime( $value, $line_timezone ?: $timezone );
					continue;
				}

				if ( 'DTEND' === $name ) {
					$dtend = self::parse_datetime( $value, $line_timezone ?: $timezone );
					continue;
				}

				// RDATE values can be a comma-separated list.
				foreach ( explode( ',', $value ) as $entry ) {
					$rdates[] = [ trim( $entry ), $params['VALUE'] ?? 'DATE-TIME', $line_timezone ];
				}
			}

			$periods = [];

			foreach ( $rdates as list( $entry, $value_type, $line_timezone ) ) {
				$entry_timezone = $line_timezone ?: $timezone;

				if ( 'PERIOD' === $value_type ) {
					list( $start_string, $end_string ) = array_pad( explode( '/', $entry, 2 ), 2, '' );
					$start                             = self::parse_datetime( $start_string, $entry_timezone );

					if ( 0 === strpos( $end_string, 'P' ) ) {
						// The period is expressed as a start and a duration.
						$end = $start->add( new DateInterval( $end_string ) );
					} else {
						$end = self::parse_datetime( $end_string, $entry_timezone );
					}
				} elseif ( 'DATE' === $value_type ) {
					// A plain date: apply the DTSTART time, or midnight, and the default duration.
					$time  = $dtstart ? $dtstart->format( 'H:i:s' ) : '00:00:00';
					$start = new DateTimeImmutable( substr( $entry, 0, 4 ) . '-' . substr( $entry, 4, 2 ) . '-' . substr( $entry, 6, 2 ) . ' ' . $time, $entry_timezone );
					$end   = $start->add( new DateInterval( "PT{$default_duration}S" ) );
				} else {
					$start = self::parse_datetime( $entry, $entry_timezone );
					$end   = $start->add( new DateInterval( "PT{$default_duration}S" ) );
				}

				$periods[ $start->format( self::RSET_DATETIME_FORMAT ) ] = [
					'start' => $start,
					'end'   => $end,
				];
			}

			if ( $dtstart instanceof DateTimeImmutable ) {
				$key = $dtstart->format( self::RSET_DATETIME_FORMAT );

				if ( ! isset( $periods[ $key ] ) ) {
					// The DTSTART models the first Occurrence when no RDATE covers it.
					$end             = $dtend ?: $dtstart->add( new DateInterval( "PT{$default_duration}S" ) );
					$periods[ $key ] = [
						'start' => $dtstart,
						'end'   => $end,
					];
				}
			}
		} catch ( Exception $e ) {
			return null;
		}

		if ( ! count( $periods ) ) {
			return null;
		}

		ksort( $periods );

		return [
			'timezone' => $timezone,
			'dtstart'  => $dtstart,
			'periods'  => array_values( $periods ),
		];
	}

	/**
	 * Serializes an Event first Occurrence and a set of additional dates to a dates-only
	 * RSET string in the same shape Events Calendar Pro derives for date-based recurrence.
	 *
	 * @since TBD
	 *
	 * @param DateTimeImmutable                                                  $start The Event (first Occurrence) start.
	 * @param DateTimeImmutable                                                  $end   The Event (first Occurrence) end.
	 * @param array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> $dates The additional dates.
	 *
	 * @return string The serialized dates-only RSET string.
	 */
	public static function serialize( DateTimeImmutable $start, DateTimeImmutable $end, array $dates ): string {
		$timezone = $start->getTimezone()->getName();
		$format   = self::RSET_DATETIME_FORMAT;

		$lines   = [];
		$lines[] = sprintf( 'DTSTART;TZID=%s:%s', $timezone, $start->format( $format ) );

		foreach ( $dates as $date ) {
			$lines[] = sprintf(
				'RDATE;TZID=%s;VALUE=PERIOD:%s/%s',
				$timezone,
				$date['start']->format( $format ),
				$date['end']->format( $format )
			);
		}

		// The first Occurrence is listed last, matching the derived Events Calendar Pro shape.
		$lines[] = sprintf(
			'RDATE;TZID=%s;VALUE=PERIOD:%s/%s',
			$timezone,
			$start->format( $format ),
			$end->format( $format )
		);

		return implode( "\n", $lines );
	}

	/**
	 * Splits an RSET string into its non-empty, trimmed lines.
	 *
	 * @since TBD
	 *
	 * @param string $rset The RSET string to split.
	 *
	 * @return array<int,string> The RSET lines.
	 */
	private static function get_lines( string $rset ): array {
		return array_values(
			array_filter(
				array_map( 'trim', explode( "\n", str_replace( "\r\n", "\n", $rset ) ) )
			)
		);
	}

	/**
	 * Splits an RSET line into its property and value parts.
	 *
	 * @since TBD
	 *
	 * @param string $line The RSET line to split.
	 *
	 * @return array{0: string, 1: string} The property and value parts.
	 */
	private static function split_line( string $line ): array {
		// The property/value separator is the first `:` not part of a parameter.
		$position = strpos( $line, ':' );

		if ( false === $position ) {
			return [ $line, '' ];
		}

		return [ substr( $line, 0, $position ), substr( $line, $position + 1 ) ];
	}

	/**
	 * Splits an RSET property into its name and parameters.
	 *
	 * @since TBD
	 *
	 * @param string $property The property to split, e.g. `RDATE;TZID=UTC;VALUE=PERIOD`.
	 *
	 * @return array{0: string, 1: array<string,string>} The property name and its parameters.
	 */
	private static function split_property( string $property ): array {
		$pieces = explode( ';', $property );
		$name   = array_shift( $pieces );
		$params = [];

		foreach ( $pieces as $piece ) {
			list( $param, $param_value )    = array_pad( explode( '=', $piece, 2 ), 2, '' );
			$params[ strtoupper( $param ) ] = $param_value;
		}

		return [ strtoupper( $name ), $params ];
	}

	/**
	 * Parses an RSET date and time string into an immutable date object.
	 *
	 * @since TBD
	 *
	 * @param string       $value    The date and time string to parse, e.g. `20261105T090000`.
	 * @param DateTimeZone $timezone The timezone to parse the value in; UTC values (with a
	 *                               trailing `Z`) are converted to the given timezone.
	 *
	 * @return DateTimeImmutable The parsed date object.
	 *
	 * @throws Exception If the value cannot be parsed.
	 */
	private static function parse_datetime( string $value, DateTimeZone $timezone ): DateTimeImmutable {
		$value = trim( $value );

		if ( '' === $value ) {
			throw new Exception( 'Empty date and time value.' );
		}

		if ( 'Z' === substr( $value, -1 ) ) {
			$utc = new DateTimeImmutable( substr( $value, 0, -1 ), new DateTimeZone( 'UTC' ) );

			return $utc->setTimezone( $timezone );
		}

		return new DateTimeImmutable( $value, $timezone );
	}
}
