<?php
/**
 * Meta Keys Trait for Category Colors.
 *
 * This trait provides access to the meta keys used for storing category color data.
 * It provides utility methods for retrieving meta keys and a list of all available keys.
 *
 * @since 6.14.0
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use InvalidArgumentException;

/**
 * Trait to provide access to category color meta keys.
 */
trait Meta_Keys_Trait {

	/**
	 * List of valid meta keys with their full prefixed values.
	 *
	 * @since 6.14.0
	 * @var array<string, string>
	 */
	protected array $keys = [
		'primary'          => 'tec-events-cat-colors-primary',
		'secondary'        => 'tec-events-cat-colors-secondary',
		'text'             => 'tec-events-cat-colors-text',
		'priority'         => 'tec-events-cat-colors-priority',
		'hide_from_legend' => 'tec-events-cat-colors-hidden',
	];

	/**
	 * Returns the full meta key.
	 *
	 * @since 6.14.0
	 *
	 * @throws InvalidArgumentException If the key is not recognized.
	 *
	 * @param string $key The key name (e.g., 'primary', 'background').
	 *
	 * @return string The full meta key.
	 */
	protected function get_key( string $key ): string {
		if ( ! isset( $this->keys[ $key ] ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid meta key "%s". Valid keys are: %s',
					$key,
					implode( ', ', array_keys( $this->keys ) )
				)
			);
		}

		return $this->keys[ $key ];
	}

	/**
	 * Returns all available meta keys.
	 *
	 * @since 6.14.0
	 *
	 * @return array<string, string> List of all meta keys with their full values.
	 */
	protected function get_all_keys(): array {
		return $this->keys;
	}
}
