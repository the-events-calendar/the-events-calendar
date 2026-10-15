<?php
/**
 * Keeps widget tag settings to the HTML tags the widget controls offer.
 *
 * @since 6.18.2
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
 * @since 6.18.2
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Widgets
 */
final class Tag_Sanitizer {
	/**
	 * The HTML tags a widget tag setting may print: the ones the controls offer, plus the
	 * non-interactive wrappers Elementor itself accepts for its own widgets and the common inline emphasis tags.
	 *
	 * @since 6.18.2
	 *
	 * @var string[]
	 */
	private const ALLOWED_TAGS = [
		'a',
		'article',
		'aside',
		'b',
		'button',
		'div',
		'em',
		'footer',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'header',
		'i',
		'main',
		'nav',
		'p',
		'section',
		'span',
		'strong',
	];

	/**
	 * Replaces any `*_tag` template arg that is not an allowed HTML tag with a `div`.
	 *
	 * @since 6.18.2
	 *
	 * @param array<string,mixed> $args The template args.
	 *
	 * @return array<string,mixed> The template args with safe tags.
	 */
	public static function sanitize( array $args ): array {
		$allowed = null;

		foreach ( $args as $key => $value ) {
			if ( ! is_string( $key ) || ! str_ends_with( $key, '_tag' ) || ! is_string( $value ) ) {
				continue;
			}

			$allowed ??= self::get_allowed_tags();

			$args[ $key ] = in_array( strtolower( $value ), $allowed, true ) ? $value : 'div';
		}

		return $args;
	}

	/**
	 * Gets the tags a widget tag setting may print.
	 *
	 * @since 6.18.2
	 *
	 * @return string[] The allowed tag names, lower case.
	 */
	private static function get_allowed_tags(): array {
		/**
		 * Filters the HTML tags an Elementor widget tag setting may print as an element name.
		 *
		 * Adding a tag that runs or loads content, such as `script`, is the site's own decision and
		 * is not blocked here.
		 *
		 * @since 6.18.2
		 *
		 * @param string[] $allowed The allowed tag names.
		 */
		$filtered = apply_filters( 'tec_events_elementor_widget_allowed_tags', self::ALLOWED_TAGS );

		if ( ! is_array( $filtered ) ) {
			return self::ALLOWED_TAGS;
		}

		return array_values( array_map( 'strtolower', array_filter( $filtered, 'is_string' ) ) );
	}
}
