<?php

namespace TEC\Events\Blocks\Archive_Events;

use Tribe\Events\Views\V2\Template_Bootstrap;

/**
 * Class Block
 *
 * @since 6.3.3
 *
 * @package TEC\Events\Blocks\Archive_Events
 */
class Block extends \Tribe__Editor__Blocks__Abstract {
	/**
	 * @since 6.3.3
	 *
	 * @var string The namespace of this template.
	 */
	protected $namespace = 'tec';

	/**
	 * Returns the name/slug of this block.
	 *
	 * @since 6.3.3
	 *
	 * @return string The name/slug of this block.
	 */
	public function slug(): string {
		return 'archive-events';
	}

	/**
	 * Set the default attributes of this block.
	 *
	 * @since 6.3.3
	 *
	 * @return array<string,mixed> The array of default attributes.
	 */
	public function default_attributes(): array {
		return [];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 6.3.3
	 * @since 6.17.5 Prevents full event views from rendering inside event descriptions.
	 * @since 6.18.3.1 Returns HTML in which no registered shortcode can be run by a later pass.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return string The block HTML.
	 */
	public function render( $attributes = [] ): string {
		// On a single event request, the view already contains this event's content.
		// Rendering the block again would recurse.
		if ( doing_filter( 'the_content' ) && tribe( Template_Bootstrap::class )->should_display_single() ) {
			return '';
		}

		$args['attributes'] = $this->attributes( $attributes );

		// Add the rendering attributes into global context.
		tribe( 'events.editor.template' )->add_template_globals( $args );

		$html = tribe( 'events.editor.template' )->template( [ 'blocks', $this->slug() ], $args, false );

		return $this->neutralize_shortcodes( $html );
	}

	/**
	 * Encodes the bracket that opens each registered shortcode in the view HTML.
	 *
	 * Inside post content the block renders before core runs `do_shortcode()`, and the view
	 * prints request values, so a shortcode typed into a search would run. In HTML `&#91;`
	 * displays as `[`; in script text (the view's JSON) `\u005b` parses as `[`.
	 *
	 * @since 6.18.3.1
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
	 *
	 * @return string An empty string.
	 */
	private function fail_shortcode_check(): string {
		do_action(
			'tribe_log',
			'error',
			'Archive Events block HTML could not be checked for shortcodes.',
			[ 'preg_error' => preg_last_error() ]
		);

		return '';
	}
}
