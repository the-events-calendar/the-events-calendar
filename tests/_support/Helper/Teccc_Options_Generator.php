<?php
/**
 * Helper class to generate teccc_options data structure for Category Colors Migration tests
 *
 * @since TBD
 *
 * @package TEC\Events\Tests\Helper
 */

 namespace Helper;

use Tribe__Events__Main;

/**
 * Class Teccc_Options_Generator
 *
 * @since TBD
 *
 * @package TEC\Events\Tests\Helper
 */
class Teccc_Options_Generator {
	/**
	 * Default options structure for teccc_options
	 *
	 * @since TBD
	 * @var array<string, mixed>
	 */
	protected static array $default_options = [
		'terms' => [],
		'ignored_terms' => [],
		'font_weight' => 'bold',
		'featured-event' => '#0ea0d7',
		'add_legend' => [ 'month', 'list', 'day', 'week', 'photo', 'map', 'summary' ],
		'add_legend_list_view' => true,
		'add_legend_day_view' => true,
		'add_legend_week_view' => true,
		'add_legend_photo_view' => true,
		'add_legend_map_view' => true,
		'add_legend_summary_view' => true,
		'legend_superpowers' => false,
		'show_ignored_cats_legend' => false,
	];

	/**
	 * Generate teccc_options data structure with specified number of categories
	 *
	 * @since TBD
	 *
	 * @param int                  $number_of_categories Number of categories to generate (default: 7).
	 * @param array<string, mixed> $args {
	 *     Optional. Array of arguments to override defaults.
	 *
	 *     @type array  $terms          Custom terms array to use instead of generating.
	 *     @type array  $ignored_terms  Custom ignored terms array.
	 *     @type string $font_weight    Font weight for categories.
	 *     @type string $featured_event Featured event color.
	 *     @type array  $add_legend     Views to show legend in.
	 *     @type bool   $legend_superpowers Whether to enable legend superpowers.
	 *     @type bool   $show_ignored_cats_legend Whether to show ignored categories in legend.
	 * }
	 * @return array<string, mixed> The generated teccc_options data structure
	 */
	public static function generate_teccc_options( int $number_of_categories = 7, array $args = [] ): array {
		$defaults = [
			'terms' => [],
			'all_terms' => [],
			'ignored_terms' => [],
		];

		$args = wp_parse_args( $args, $defaults );

		// Start with default options.
		$teccc_options = self::$default_options;

		// If no custom terms provided, get them from the database.
		if ( empty( $args['terms'] ) ) {
			$teccc_options['terms'] = self::get_terms_from_db( $number_of_categories );
		} else {
			$teccc_options['terms'] = $args['terms'];
		}
		$teccc_options['all_terms'] = $teccc_options['terms'];

		// Set ignored terms if provided.
		if ( ! empty( $args['ignored_terms'] ) ) {
			$teccc_options['ignored_terms'] = $args['ignored_terms'];
		}

		return $teccc_options;
	}

	/**
	 * Get terms from the database in the correct format
	 *
	 * @since TBD
	 *
	 * @param int $number_of_categories Number of categories to retrieve.
	 * @return array<int, array<string, mixed>> Array of term data.
	 */
	protected static function get_terms_from_db( int $number_of_categories ): array {
		$terms = [];
		$event_categories = get_terms( [
			'taxonomy' => Tribe__Events__Main::TAXONOMY,
			'hide_empty' => false,
			'number' => $number_of_categories,
		] );

		if ( is_wp_error( $event_categories ) ) {
			return [];
		}

		foreach ( $event_categories as $category ) {
			$terms[ $category->term_id ] = [
				$category->slug,
				str_replace( ' ', '&nbsp;', $category->name ),
			];
		}

		return $terms;
	}
}
