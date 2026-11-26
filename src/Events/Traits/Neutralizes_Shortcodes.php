<?php
/**
 * Neutralizes the shortcodes in the HTML a block prints into post content.
 *
 * @since 6.18.4.1
 *
 * @package TEC\Events\Traits
 */

namespace TEC\Events\Traits;

/**
 * Inside post content a dynamic block renders before core runs `do_shortcode()`. A block that
 * prints a view prints request values, such as the search keyword, so a shortcode typed into a
 * search would run. Blocks that print view HTML pass it through `neutralize_shortcodes()`.
 *
 * @since 6.18.4.1
 */
trait Neutralizes_Shortcodes {
	/**
	 * Encodes the bracket that opens each registered shortcode in the view HTML.
	 *
	 * In HTML `&#91;` displays as `[`; in script text (the view's JSON) `\u005b` parses as `[`.
	 *
	 * @since 6.18.3.1
	 * @since 6.18.4.1 Moved from the Archive Events block to a trait shared by the event blocks.
	 *
	 * @param string $html The rendered block HTML.
	 *
	 * @return string The HTML with no runnable shortcode.
	 */
	private function neutralize_shortcodes( string $html ): string {
		global $shortcode_tags;

		if ( empty( $shortcode_tags ) || false === strpos( $html, '[' ) ) {
			return $html;
		}

		$tags    = implode( '|', array_map( static fn( $tag ) => preg_quote( $tag, '/' ), array_keys( $shortcode_tags ) ) );
		$pattern = '/\\[(?=\\/?(?:' . $tags . ')(?![\\w-]))/';
		$length  = strlen( $html );
		$offset  = 0;
		$result  = '';

		/*
		 * Scripts are found with a string scan, not a regex: matching a whole view JSON hits
		 * PCRE's backtrack limit at about 1 MB. Script text is not HTML, so it gets `\u005b`,
		 * which a script reads as `[`; `&#91;` would change the value.
		 */
		while ( $offset < $length ) {
			$start = stripos( $html, '<script', $offset );
			$end   = false === $start ? false : stripos( $html, '</script>', $start );

			if ( false === $end ) {
				$start = $length;
				$end   = $length;
			} else {
				$end += strlen( '</script>' );
			}

			$text   = preg_replace( $pattern, '&#91;', substr( $html, $offset, $start - $offset ) );
			$script = preg_replace( $pattern, '\\u005b', substr( $html, $start, $end - $start ) );

			if ( null === $text || null === $script ) {
				return $this->fail_shortcode_check();
			}

			$result .= $text . $script;
			$offset  = $end;
		}

		return $result;
	}

	/**
	 * Logs a failed shortcode check and returns the empty block HTML.
	 *
	 * Printing part of the view would ship broken markup, so the block renders nothing.
	 *
	 * @since 6.18.3.1
	 * @since 6.18.4.1 Moved from the Archive Events block; the log names the block.
	 *
	 * @return string An empty string.
	 */
	private function fail_shortcode_check(): string {
		do_action(
			'tribe_log',
			'error',
			'Block HTML could not be checked for shortcodes.',
			[
				'block'      => $this->name(),
				'preg_error' => preg_last_error(),
			]
		);

		return '';
	}
}
