<?php
/**
 * Registers the Custom Tables based version of the plugin (v1), if possible.
 *
 * The provider will completely register, or not, the Custom Tables based
 * implementation.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */

namespace TEC\Custom_Tables\V1;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */
class Provider extends Service_Provider {
	const DISABLED = 'TEC_CUSTOM_TABLES_V1_DISABLED';

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {
		if ( ! self::is_active() ) {
			return false;
		}
	}

	/**
	 * Returns whether the Custom Tables implementation should register, thus activate,
	 * or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Custom Tables implementation should register or not.
	 */
	public static function is_active() {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The disable constant is defined and it's truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The disable env var is defined and it's truthy.
			return false;
		}

		/**
		 * Allows filtering whether the whole Custom Tables v1 implementation
		 * should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since TBD
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_custom_tables_v1_enabled', true );
	}
}
