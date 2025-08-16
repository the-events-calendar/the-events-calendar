<?php
/**
 * Request query vars filter and sanitization.
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */

declare(strict_types=1);

namespace TEC\Events\Request;

/**
 * Class Query_Vars
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */
class Query_Vars {

	/**
	 * Register hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'request', [ $this, 'sanitize_query_vars' ], 0 );
	}

	/**
	 * Unregister hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'request', [ $this, 'sanitize_query_vars' ], 0 );
	}

	/**
	 * Sanitize relevant query vars as early as possible.
	 *
	 * Ensures `ical` is normalized using tribe_is_truthy semantics, removing it when falsey.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $vars Parsed query variables.
	 *
	 * @return array<string,mixed> The sanitized query vars.
	 */
	public function sanitize_query_vars( array $vars ): array {
		if ( array_key_exists( 'ical', $vars ) ) {
			$value = $vars['ical'];

			if ( is_array( $value ) ) {
				$value = reset( $value );
			}

			if ( tribe_is_truthy( $value ) ) {
				$vars['ical'] = 1;
			} else {
				unset( $vars['ical'] );
			}
		}

		return $vars;
	}
}


