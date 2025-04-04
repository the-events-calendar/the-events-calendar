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
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class Settings {
	/**
	 * The QR code slug used for shortcodes and hashing.
	 *
	 * @since TBD
	 * @var string
	 */
	public const QR_SLUG = 'tec_event_qr';

	/**
	 * Get the QR code slug.
	 *
	 * @since TBD
	 * @return string The QR code slug.
	 */
	public static function get_qr_slug(): string {
		return static::QR_SLUG;
	}

	/**
	 * An array of option keys that will be used to store the values.
	 *
	 * @since TBD
	 * @return array
	 */
	public static function get_option_slugs(): array {
		return [
			'title'       => 'tribe-events-qr-code-settings',
			'enabled'     => 'tribe-events-qr-code-enabled',
			'size'        => 'tribe-events-qr-size',
			'redirection' => 'tribe-events-qr-redirection-behavior',
			'event_id'    => 'tribe-events-qr-specific-event-id',
			'series_id'   => 'tribe-events-qr-next-event-in-series-id',
			'fallback'    => 'tribe-events-qr-fallback',
		];
	}
}
