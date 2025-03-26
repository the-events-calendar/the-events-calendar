<?php
/**
 * The Settings class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

/**
 * Class Settings
 *
 * @since   TBD
 *
 * @package TEC\Events\QR
 */
class Settings {

	/**
	 * An array of option keys that will be used to store the values.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public static function get_option_slugs(): array {
		return [
			'title'       => 'tribe-events-qr-code-settings',
			'enabled'     => 'tribe-events-qr-code-enabled',
			'prefix'      => 'tribe-events-qr-prefix',
			'size'        => 'tribe-events-qr-size',
			'redirection' => 'tribe-events-qr-redirection-behavior',
			'specific'    => 'tribe-events-qr-specific-event-id',
			'fallback'    => 'tribe-events-qr-fallback',
		];
	}

	/**
	 * Check if the QR code is enabled.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$slugs   = static::get_option_slugs();
		$enabled = tribe_is_truthy( tribe_get_option( $slugs['enabled'], true ) );

		/**
		 * Filters the QR enabled value.
		 *
		 * @since TBD
		 *
		 * @param bool $enabled The bool that comes from the options.
		 */
		return (bool) apply_filters( 'tec_events_qr_code_enabled', $enabled );
	}
}
