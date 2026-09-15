<?php
/**
 * Line-level edits of an RSET string that need no recurrence rule engine.
 *
 * A rule-based RSET (RRULE) cannot be expanded by this plugin, but moving one of its
 * Occurrences only needs two lines added in the shape Events Calendar Pro emits and
 * parses: an EXDATE excluding the original date and an RDATE adding the new one. The
 * line order Pro serializes is preserved: DTSTART, DTEND, RRULE, RDATE, EXRULE, EXDATE.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

use DateTimeImmutable;
use TEC\Events\Recurrence\Dates;

/**
 * Class Rset_Lines.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */
class Rset_Lines {
	/**
	 * The serialization order of the RSET properties, matching Events Calendar Pro.
	 *
	 * @since TBD
	 *
	 * @var array<string,int>
	 */
	private const ORDER = [
		'DTSTART' => 0,
		'DTEND'   => 1,
		'RRULE'   => 2,
		'RDATE'   => 3,
		'EXRULE'  => 4,
		'EXDATE'  => 5,
	];

	/**
	 * Adds an excluded date to the RSET.
	 *
	 * EXDATE lines carry no timezone: the value is the wall-clock time in the RSET
	 * timezone, the one Events Calendar Pro infers from the DTSTART line on parse.
	 *
	 * @since TBD
	 *
	 * @param string            $rset The RSET string.
	 * @param DateTimeImmutable $date The date to exclude, in the Event timezone.
	 *
	 * @return string The RSET string with the exclusion added, unchanged if already excluded.
	 */
	public static function add_exdate( string $rset, DateTimeImmutable $date ): string {
		$value = $date->format( Dates::RSET_DATETIME_FORMAT );
		$lines = self::get_lines( $rset );

		foreach ( $lines as $index => $line ) {
			if ( 'EXDATE' !== self::get_name( $line ) ) {
				continue;
			}

			$values = self::get_values( $line );

			if ( in_array( $value, $values, true ) ) {
				return self::join( $lines );
			}

			$values[]        = $value;
			$lines[ $index ] = self::get_property( $line ) . ':' . implode( ',', $values );

			return self::join( $lines );
		}

		$lines[] = 'EXDATE:' . $value;

		return self::join( $lines );
	}

	/**
	 * Adds an explicit date period to the RSET.
	 *
	 * @since TBD
	 *
	 * @param string            $rset  The RSET string.
	 * @param DateTimeImmutable $start The period start, in the Event timezone.
	 * @param DateTimeImmutable $end   The period end.
	 *
	 * @return string The RSET string with the RDATE added, unchanged if already present.
	 */
	public static function add_rdate_period( string $rset, DateTimeImmutable $start, DateTimeImmutable $end ): string {
		$format = Dates::RSET_DATETIME_FORMAT;
		$line   = sprintf(
			'RDATE;TZID=%s;VALUE=PERIOD:%s/%s',
			$start->getTimezone()->getName(),
			$start->format( $format ),
			$end->setTimezone( $start->getTimezone() )->format( $format )
		);
		$lines  = self::get_lines( $rset );

		if ( in_array( $line, $lines, true ) ) {
			return self::join( $lines );
		}

		$lines[] = $line;

		return self::join( $lines );
	}

	/**
	 * Returns whether the RSET excludes a date or not.
	 *
	 * @since TBD
	 *
	 * @param string            $rset The RSET string.
	 * @param DateTimeImmutable $date The date to look for, in the Event timezone.
	 *
	 * @return bool Whether an EXDATE line lists the date or not.
	 */
	public static function has_exdate( string $rset, DateTimeImmutable $date ): bool {
		$value = $date->format( Dates::RSET_DATETIME_FORMAT );

		foreach ( self::get_lines( $rset ) as $line ) {
			if ( 'EXDATE' === self::get_name( $line ) && in_array( $value, self::get_values( $line ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Joins RSET lines back into a string, in the Events Calendar Pro property order.
	 *
	 * The sort is stable: lines of the same property keep their relative order.
	 *
	 * @since TBD
	 *
	 * @param array<int,string> $lines The RSET lines.
	 *
	 * @return string The RSET string.
	 */
	private static function join( array $lines ): string {
		$ranked = [];

		foreach ( array_values( $lines ) as $index => $line ) {
			$ranked[] = [ self::ORDER[ self::get_name( $line ) ] ?? count( self::ORDER ), $index, $line ];
		}

		usort(
			$ranked,
			static function ( array $a, array $b ): int {
				return [ $a[0], $a[1] ] <=> [ $b[0], $b[1] ];
			}
		);

		return implode( "\n", array_column( $ranked, 2 ) );
	}

	/**
	 * Splits an RSET string into its non-empty, trimmed lines.
	 *
	 * @since TBD
	 *
	 * @param string $rset The RSET string.
	 *
	 * @return array<int,string> The lines.
	 */
	private static function get_lines( string $rset ): array {
		return array_values(
			array_filter(
				array_map( 'trim', explode( "\n", str_replace( "\r\n", "\n", $rset ) ) )
			)
		);
	}

	/**
	 * Returns the property part of a line, e.g. `RDATE;TZID=UTC;VALUE=PERIOD`.
	 *
	 * @since TBD
	 *
	 * @param string $line The RSET line.
	 *
	 * @return string The property part.
	 */
	private static function get_property( string $line ): string {
		$position = strpos( $line, ':' );

		return false === $position ? $line : substr( $line, 0, $position );
	}

	/**
	 * Returns the upper-cased property name of a line, e.g. `RDATE`.
	 *
	 * @since TBD
	 *
	 * @param string $line The RSET line.
	 *
	 * @return string The property name.
	 */
	private static function get_name( string $line ): string {
		return strtoupper( (string) strtok( self::get_property( $line ), ';' ) );
	}

	/**
	 * Returns the comma-separated values of a line.
	 *
	 * @since TBD
	 *
	 * @param string $line The RSET line.
	 *
	 * @return array<int,string> The values.
	 */
	private static function get_values( string $line ): array {
		$position = strpos( $line, ':' );

		if ( false === $position ) {
			return [];
		}

		return array_values( array_filter( array_map( 'trim', explode( ',', substr( $line, $position + 1 ) ) ) ) );
	}
}
