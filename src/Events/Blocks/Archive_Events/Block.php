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
	 * @since TBD Returns HTML in which no registered shortcode can be run by a later pass.
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
	 * @since TBD
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
		$parts   = preg_split( '#(<script\\b[^>]*>.*?</script>)#is', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

		if ( false === $parts ) {
			return '';
		}

		foreach ( $parts as $i => $part ) {
			// Script text is not HTML: `&#91;` would change the value a script reads, `\u005b` does not.
			$parts[ $i ] = preg_replace( $pattern, $i % 2 ? '\\u005b' : '&#91;', $part ) ?? '';
		}

		return implode( '', $parts );
	}
}
