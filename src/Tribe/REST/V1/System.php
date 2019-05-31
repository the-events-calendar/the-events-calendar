<?php


/**
 * Class Tribe__Events__REST__V1__System
 *
 * Handles scanning and asserting the current WordPress installation support of
 * TEC REST API.
 */
class Tribe__Events__REST__V1__System extends Tribe__REST__System {

	/**
	 * @var string The name of the option that enables or disables the TEC REST API support.
	 */
	protected static $disable_option_name = 'rest-v1-disabled';

	/**
	 * Whether TEC REST API is supported by the WP installation or not.
	 *
	 * @return bool
	 */
	public function supports_tec_rest_api() {
		return $this->supports_wp_rest_api();
	}

	/**
	 * Whether The Events Calendar REST API is enabled or not for the WP installation.
	 *
	 * @return bool
	 */
	public function tec_rest_api_is_enabled() {
		$enabled = $this->supports_tec_rest_api() && false == tribe_get_option( self::$disable_option_name );

		/**
		 * Filters whether TEC REST API is enabled or not for the WP installation.
		 *
		 * @param bool $enabled
		 */
		return apply_filters( 'tribe_events_rest_api_enabled', $enabled );
	}


	/**
	 * Returns the name of the option used to indicate whether TEC REST API is enabled or not for
	 * the WP installation.
	 *
	 * The option is stored in The Events Calendar options database record: use `tribe_get_option()`
	 * to get it.
	 *
	 * @return string
	 */
	public static function get_disable_option_name() {
		return self::$disable_option_name;
	}
}
