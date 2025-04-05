<?php
/**
 * Provides methods to manipulate date representations.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

use DateTimeInterface;
use DateTimeZone;
use Tribe__Date_Utils as Dates;

/**
 * Trait With_Dates_Representation
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */
trait With_Dates_Representation {
	/**
	 * The object is formatted as JS (Gutenberg Blocks). The data is converted to UTC as specified to the ISO SPEC,
	 * then formatted according to the JS format.
	 *
	 * @see: https://262.ecma-international.org/6.0/#sec-date-time-string-format
	 *
	 * JS Format: YYYY-MM-DDTHH:mm:ss.sssZ
	 * PHP Format: Y-m-d\T-H:i:s:000Z
	 *
	 * @param  string|DateTimeInterface  $date_time
	 *
	 * @return string The formatted date as ISO 8601.
	 */
	protected function to_iso_8601( $date_time ) {
		$format    = Dates::DBDATEFORMAT . '\T' . Dates::DBTIMEFORMAT;
		$date      = Dates::immutable( $date_time, new DateTimeZone( 'UTC' ) );
		$formatted = $date->format( $format );

		$milliseconds = (int) $date->format( 'u' );

		if ( $milliseconds > 0 ) {
			$milliseconds = floor( $milliseconds / 1000 );
		}

		$milliseconds = str_pad( $milliseconds, 3, '0', STR_PAD_LEFT );

		return "{$formatted}.{$milliseconds}Z";
	}
}
