<?php

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
	 * The option key that will be used to store the value.
	 *
	 * @since TBD
	 *
	 * @var string The option key.
	 */
	protected static string $enabled_option_key = 'events-enable-qr-codes';

	/**
	 * The option key that will be used to store the value.
	 *
	 * @since TBD
	 *
	 * @var string The option key.
	 */
	protected static string $enable_shortcode_option_key = 'events-enable-qr-shortcode';

	/**
	 * Get the option key that will be used to store the value.
	 *
	 * Option keys that are user controlled, need to be kept as public.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_enabled_option_slug(): string {
		return static::$enabled_option_key;
	}

	/**
	 * Get the option key that will be used to store the value.
	 *
	 * Option keys that are user controlled, need to be kept as public.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_enable_shortcode_option_slug(): string {
		return static::$enable_shortcode_option_key;
	}

	/**
	 * Check if the QR code is enabled.
	 *
	 * @since TBD
	 *
	 * @param mixed $context The context of the check.
	 *
	 * @return bool
	 */
	public function is_enabled( $context = null ): bool {
		$controller = tribe( Controller::class );
		$enabled    = false;

		// Only if the controller can use the QR code.
		if ( $controller->can_use() ) {
			$enabled = tribe_is_truthy( tribe_get_option( static::get_enabled_option_slug(), true ) );
		}

		/**
		 * Filters the QR enabled value.
		 *
		 * @since TBD
		 *
		 * @param bool  $enabled The bool that comes from the options.
		 * @param array $context Context for this check, normally the event.
		 */
		return apply_filters( 'tec_events_qr_code_enabled', $enabled, $context );
	}
}
