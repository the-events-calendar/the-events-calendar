<?php
/**
 * Meta Keys Helper for Category Colors.
 *
 * This class manages the meta keys used for storing category color data.
 * It provides utility methods for retrieving prefixed meta keys and a list of all available keys.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors
 */

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
		'primary'          => 'primary',
		'secondary'        => 'secondary',
		'text'             => 'text',
		'priority'         => 'priority',
		'hide_from_legend' => 'hidden',
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
		return array_map(
			function ( $value ) {
				return self::$meta_key_prefix . $value;
			},
			self::$keys
		);
	}
}
