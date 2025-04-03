<?php
/**
 * Provides methods to manipulate timezones.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Timezones
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */
trait With_Timezones {
	/**
	 * Detect if a provided timezone is using a variant of the UTC+0 timezone.
	 *
	 * Depending on the system providing the timezone string, the UTC+0 timezone might
	 * have a different name, but still mean the same. This methods discriminates it.
	 *
	 * @since 6.0.0
	 *
	 * @param string $time_zone_name The timezone name to check.
	 *
	 * @return bool Whether the provided timezone is using a variant of the UTC+0 timezone or not.
	 */
	private function is_utc( $time_zone_name ) {
		$alias = [
			'UTC',
			'Z',
			'GMT',
			'GMT0',
			'+00:00',
			'GMT+0',
			'GMT-0',
			'Etc/UTC',
			'Etc/Zulu',
			'ZULU',
		];

		return in_array( $time_zone_name, $alias, true );
	}
}
