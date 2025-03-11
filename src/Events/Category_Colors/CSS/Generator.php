<?php
/**
 * Handles the generation, minification, and storage of category colors CSS.
 *
 * This class retrieves category color settings, generates CSS rules, minifies the output,
 * and saves the final CSS in the WordPress options table.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\CSS_Generator
 */

namespace TEC\Events\Category_Colors\CSS;

use TEC\Common\StellarWP\DB\DB;
use Tribe__Events__Main;
use Tribe__Utils__Color;

/**
 * Class for generating, minifying, and storing category colors CSS.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\CSS_Generator
 */
class Generator {
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
	 * Meta keys used for retrieving category colors.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected array $meta_keys = [
		'tec-events-cat-colors-primary',
		'tec-events-cat-colors-secondary',
		'tec-events-cat-colors-text',
		'tec-events-cat-colors-priority',
	];

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
		$categories = $this->fetch_category_meta();

		if ( empty( $categories ) ) {
			$this->generated_css = '';

			return '';
		}

		$css_rules = array_map( [ $this, 'generate_css_rule' ], $categories );

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
	 * Generate a CSS rule for a category using CSS variables.
	 *
	 * @since TBD
	 *
	 * @param array $category The category data.
	 *
	 * @return string The generated CSS rule.
	 */
	protected function generate_css_rule( array $category ): string {
		$class = sanitize_html_class( $category['slug'] );

		$primary    = $this->sanitize_color( $category['primary'] ) ?: 'transparent';
		$background = $this->sanitize_color( $category['background'] ) ?: 'transparent';
		$text       = $this->sanitize_color( $category['text'] ) ?: 'inherit';

		return ".tribe-events-calendar-list__category--{$class} {\n"
		       . "    --tec-category-primary: {$primary};\n"
		       . "    --tec-category-secondary: {$background};\n"
		       . "    --tec-category-text: {$text};\n"
		       . "}\n";
	}

	/**
	 * Fetch category meta for the `tribe_events_cat` taxonomy.
	 * Retrieves metadata from the database in batches and organizes it by term ID.
	 *
	 * @since TBD
	 *
	 * @return array Structured category data.
	 */
	protected function fetch_category_meta(): array {
		$db     = tribe( DB::class );
		$offset = 0;

		$batch_size = (int) apply_filters( 'tec_events_category_color_generator_batch_size', 500 );
		$categories = [];

		do {
			$results = $db->table( 'term_taxonomy', 'tt' )
				->select( 'tm.term_id', 'tm.meta_key', 'tm.meta_value', 't.slug' )
				->innerJoin( 'termmeta', 'tt.term_id', 'tm.term_id', 'tm' )
				->innerJoin( 'terms', 'tt.term_id', 't.term_id', 't' )
				->where( 'tt.taxonomy', Tribe__Events__Main::TAXONOMY )
				->whereIn( 'tm.meta_key', $this->meta_keys )
				->limit( $batch_size )
				->offset( $offset )
				->getAll();

			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$term_id    = (int) $row->term_id;
					$slug       = sanitize_title( $row->slug );
					$meta_key   = $row->meta_key;
					$meta_value = $row->meta_value;

					if ( ! isset( $categories[ $term_id ] ) ) {
						$categories[ $term_id ] = [
							'slug'       => $slug,
							'primary'    => '',
							'background' => '',
							'text'       => '',
							'priority'   => -1,
						];
					}

					switch ( $meta_key ) {
						case 'tec-events-cat-colors-primary':
							$categories[ $term_id ]['primary'] = $meta_value;
							break;
						case 'tec-events-cat-colors-secondary':
							$categories[ $term_id ]['background'] = $meta_value;
							break;
						case 'tec-events-cat-colors-text':
							$categories[ $term_id ]['text'] = $meta_value;
							break;
						case 'tec-events-cat-colors-priority':
							$categories[ $term_id ]['priority'] = is_numeric( $meta_value ) ? (int) $meta_value : -1;
							break;
					}
				}
			}

			$offset += $batch_size;
		} while ( ! empty( $results ) );

		// Sort by priority ascending (lower priority first, highest priority last).
		usort( $categories, fn( $a, $b ) => $a['priority'] <=> $b['priority'] );

		return $categories;
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
}
