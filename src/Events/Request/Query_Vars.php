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

			$normalized = $this->normalize_ical( $value );

			if ( null !== $normalized ) {
				$vars['ical'] = $normalized;
			} else {
				unset( $vars['ical'] );
			}
		}

		// Mirror the same sanitization to superglobals, but only if the key exists in them.
		$this->sanitize_superglobal_key( '_GET', 'ical', [ $this, 'normalize_ical' ] );
		$this->sanitize_superglobal_key( '_POST', 'ical', [ $this, 'normalize_ical' ] );
		$this->sanitize_superglobal_key( '_REQUEST', 'ical', [ $this, 'normalize_ical' ] );

		return $vars;
	}

	/**
	 * Sanitize a specific key in a superglobal-like array reference.
	 *
	 * @since TBD
	 *
	 * @param array  $array The superglobal array passed by reference.
	 * @param string $key   The key to sanitize.
	 *
	 * @return void
	 */
	protected function sanitize_superglobal_key( string $superglobal, string $key, callable $normalizer ): void {
		// Only allow whitelisted superglobals.
		$allowed = [ '_GET', '_POST', '_REQUEST' ];
		if ( ! in_array( $superglobal, $allowed, true ) ) {
			return;
		}

		if ( ! isset( $GLOBALS[ $superglobal ] ) || ! is_array( $GLOBALS[ $superglobal ] ) ) {
			return;
		}

		if ( ! array_key_exists( $key, $GLOBALS[ $superglobal ] ) ) {
			return;
		}

		$value = $GLOBALS[ $superglobal ][ $key ];

		if ( is_array( $value ) ) {
			$value = reset( $value );
		}

		$normalized = $normalizer( $value );

		if ( null === $normalized ) {
			unset( $GLOBALS[ $superglobal ][ $key ] );
			return;
		}

		$GLOBALS[ $superglobal ][ $key ] = $normalized;
	}

	/**
	 * Normalizes the `ical` value to either `1` or `null` (unset).
	 *
	 * @since TBD
	 *
	 * @param mixed $value The raw value to normalize.
	 *
	 * @return int|null `1` when truthy, `null` when not.
	 */
	protected function normalize_ical( $value ) {
		// Support presence-only query var (?ical) as truthy.
		if ( '' === $value || null === $value ) {
			return 1;
		}

		return tribe_is_truthy( $value ) ? 1 : null;
	}
}
