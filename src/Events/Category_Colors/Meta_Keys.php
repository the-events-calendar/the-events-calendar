<?php

namespace TEC\Events\Category_Colors;

/**
 * Helper class to manage meta keys for Category Colors.
 */
class Meta_Keys {

	/**
	 * Prefix for storing category meta values.
	 *
	 * @since TBD
	 * @var string
	 */
	protected static string $meta_key_prefix = 'tec-events-cat-colors-';

	/**
	 * List of valid meta keys.
	 *
	 * @since TBD
	 * @var array<string, string>
	 */
	protected static array $keys = [
		'primary'   => 'primary',
		'secondary' => 'secondary',
		'text'      => 'text',
		'priority'  => 'priority',
	];

	/**
	 * Returns the full meta key with prefix.
	 *
	 * @since TBD
	 *
	 * @param string $key The key name (e.g., 'primary', 'background').
	 *
	 * @return string|null Full meta key or null if the key does not exist.
	 */
	public static function get_key( string $key ): ?string {
		return self::$keys[ $key ] ?? null ? self::$meta_key_prefix . self::$keys[ $key ] : null;
	}

	/**
	 * Returns all available meta keys.
	 *
	 * @since TBD
	 *
	 * @return array<string, string> List of all meta keys with their full values.
	 */
	public static function get_all_keys(): array {
		$full_keys = [];
		foreach ( self::$keys as $key => $value ) {
			$full_keys[ $key ] = self::$meta_key_prefix . $value;
		}

		return $full_keys;
	}
}
