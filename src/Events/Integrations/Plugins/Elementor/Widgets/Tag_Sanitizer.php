<?php
/**
 * Keeps widget tag settings to the HTML tags the widget controls offer.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

/**
 * Class Tag_Sanitizer.
 *
 * The tags come from stored widget settings. The editor limits them to a SELECT, but nothing
 * enforces that on the server, and the templates print them as element names.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
final class Tag_Sanitizer {
	/**
	 * The HTML tags the widget tag controls offer.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	private const ALLOWED_TAGS = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ];

	/**
	 * Replaces any `*_tag` template arg that is not an allowed HTML tag with a `div`.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $args The template args.
	 *
	 * @return array<string,mixed> The template args with safe tags.
	 */
	public static function sanitize( array $args ): array {
		foreach ( $args as $key => $value ) {
			if ( ! is_string( $key ) || ! str_ends_with( $key, '_tag' ) || ! is_string( $value ) ) {
				continue;
			}

			$args[ $key ] = in_array( strtolower( $value ), self::ALLOWED_TAGS, true ) ? $value : 'div';
		}

		return $args;
	}
}
