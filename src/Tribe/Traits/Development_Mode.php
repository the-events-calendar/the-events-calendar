<?php

declare( strict_types=1 );

namespace TEC\Tribe\Traits;

/**
 * Trait Development_Mode
 *
 * @since x.x.x
 */
trait Development_Mode {

	/**
	 * Check if the site is in development mode.
	 *
	 * A site is considered to be in development mode if:
	 * - The value of wp_is_development_mode( 'plugin' ) is true.
	 * - The value of wp_get_environment_type() is 'local' or 'development'.
	 *
	 * This method also applies a filter so that 3rd party developers can set their
	 * own conditions for development mode.
	 *
	 * @since x.x.x
	 *
	 * @return bool
	 */
	protected function is_site_development_mode() {
		$env_type = wp_get_environment_type();

		$is_development_mode =
			wp_is_development_mode( 'plugin' )
			|| 'local' === $env_type
			|| 'development' === $env_type;

		/**
		 * Filters whether the site is in development mode.
		 *
		 * @param bool $is_development_mode Whether the site is in development mode.
		 */
		return apply_filters( 'tribe_site_is_development_mode', $is_development_mode );
	}
}
