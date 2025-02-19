<?php
/**
 * Handles the generation, minification, and storage of category colors CSS.
 *
 * This class retrieves category color settings, generates CSS rules, minifies the output,
 * and saves the final CSS in the WordPress options table.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\CSS_Generator
 */

namespace TEC\Events\Category_Colors\CSS_Generator;

use TEC\Events\Category_Colors\Traits\Category_Trait;
use Tribe__Utils__Color;

/**
 * Class for generating, minifying, and storing category colors CSS.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\CSS_Generator
 */
class Generator {
	use Category_Trait;

	/**
	 * Option key for storing generated CSS in wp_options.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $option_key = 'tec_events_category_color_css';

	/**
	 * Stores the generated CSS before saving.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $generated_css = '';

	/**
	 * Generate, minify, and save category colors CSS.
	 *
	 * @since TBD
	 *
	 * @return string The generated CSS.
	 */
	public function generate_and_save_css(): string {
		$this->generate_css();
		$this->minify_css();
		$this->save_css();

		return $this->generated_css;
	}

	/**
	 * Generate category colors CSS and store it in the class property.
	 *
	 * @since TBD
	 *
	 * @return string The generated CSS.
	 */
	public function generate_css(): string {
		$this->fetch_category_meta();
		$categories = $this->build_category_structure();

		$css_rules = array_map(
			[ $this, 'generate_css_rule' ],
			array_filter( $categories, [ $this, 'is_valid_category' ] )
		);

		/**
		 * Filter the final generated CSS before saving.
		 *
		 * @since TBD
		 *
		 * @param string $css The generated CSS string.
		 *
		 * @return string The filtered CSS string.
		 */
		$this->generated_css = apply_filters( 'tec_events_category_color_generator_final_css', implode( "\n", $css_rules ) );

		return $this->generated_css;
	}

	/**
	 * Save the generated CSS to wp_options.
	 *
	 * @since TBD
	 */
	public function save_css(): void {
		update_option( $this->option_key, trim( $this->generated_css ), true );
	}

	/**
	 * Retrieve the stored CSS from wp_options.
	 *
	 * @since TBD
	 *
	 * @return string The stored CSS.
	 */
	public function get_saved_css(): string {
		return get_option( $this->option_key, '' );
	}

	/**
	 * Generate a CSS rule for a category.
	 *
	 * @since TBD
	 *
	 * @param array $category The category data.
	 *
	 * @return string The generated CSS rule.
	 */
	protected function generate_css_rule( array $category ): string {
		$class = sanitize_html_class( $category['class'] );

		$background = $this->sanitize_color( $category['background'], true );
		$border     = $this->sanitize_color( $category['primary'], true );
		$text       = $this->sanitize_color( $category['text'], true );

		$styles = array_filter(
			[
				$background ? "background-color: {$background};" : '',
				$border ? "border-color: {$border};" : '',
				$text ? "color: {$text};" : '',
			]
		);

		return ".{$class} { " . implode( ' ', $styles ) . ' }';
	}

	/**
	 * Validate and convert color formats (HEX, RGB, HSL) into HEX.
	 *
	 * @since TBD
	 *
	 * @param string|null $color    The color input.
	 * @param bool        $validate Whether to validate the color before converting.
	 *
	 * @return string|null The sanitized HEX color or null if invalid.
	 */
	protected function sanitize_color( ?string $color, bool $validate = false ): ?string {
		if ( is_null( $color ) ) {
			return null;
		}

		try {
			$color_obj = new Tribe__Utils__Color( $color );
			$hex       = $color_obj->get_hex_with_hash(); // Ensure we always return HEX format.

			return ( $validate && ! $this->is_valid_hex( $hex ) ) ? null : $hex;
		} catch ( \Exception $e ) {
			return null; // Invalid color formats (bad RGB, HSL, etc.).
		}
	}

	/**
	 * Validate a category array.
	 *
	 * @since TBD
	 *
	 * @param array $category The category data.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	protected function is_valid_category( array $category ): bool {
		return ! empty( $category['class'] )
			&& $this->is_valid_class( $category['class'] )
			&& is_numeric( $category['priority'] )
			&& (int) $category['priority'] >= -1
			&& (
				$this->sanitize_color( $category['background'], true )
				|| $this->sanitize_color( $category['border'], true )
				|| $this->sanitize_color( $category['text'], true )
			);
	}

	/**
	 * Validate if a string is a proper CSS class.
	 *
	 * @since TBD
	 *
	 * @param string $css_class The class name.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	protected function is_valid_class( string $css_class ): bool {
		return preg_match( '/^[a-zA-Z0-9\-_]+$/', $css_class );
	}

	/**
	 * Validate if a string is a proper hex color.
	 *
	 * @since TBD
	 *
	 * @param string $color The color string.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	protected function is_valid_hex( ?string $color ): bool {
		return ! empty( $color ) && preg_match( '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color );
	}

	/**
	 * Minify the generated CSS by removing spaces, newlines, and comments.
	 *
	 * @since TBD
	 */
	public function minify_css(): void {
		$comments = <<<'EOS'
(?sx)
    # Don't change anything inside of quotes
    ( "(?:[^"\\]++|\\.)*+" | '(?:[^'\\]++|\\.)*+' )
|
    # Remove CSS comments
    /\* (?> .*? \*/ )
EOS;

		$everything_else = <<<'EOS'
(?six)
    # Don't change anything inside of quotes
    ( "(?:[^"\\]++|\\.)*+" | '(?:[^'\\]++|\\.)*+' )
|
    # Remove spaces before and after ; and }
    \s*+ ; \s*+ ( } ) \s*+
|
    # Remove all spaces around meta chars/operators (excluding + and -)
    \s*+ ( [*$~^|]?+= | [{};,>~] | !important\b ) \s*+
|
    # Remove all spaces around + and - (in selectors only!)
    \s*([+-])\s*(?=[^}]*{)
|
    # Remove spaces right of ( [ :
    ( [[(:] ) \s++
|
    # Remove spaces left of ) ]
    \s++ ( [])] )
|
    # Remove spaces left (and right) of : (but not in selectors!)
    \s+(:)(?![^\}]*\{)
|
    # Remove spaces at beginning/end of string
    ^ \s++ | \s++ \z
|
    # Convert double spaces to single
    (\s)\s+
EOS;

		$search_patterns  = [ "%{$comments}%", "%{$everything_else}%" ];
		$replace_patterns = [ '$1', '$1$2$3$4$5$6$7$8' ];

		$this->generated_css = preg_replace( $search_patterns, $replace_patterns, $this->generated_css );
	}
}
