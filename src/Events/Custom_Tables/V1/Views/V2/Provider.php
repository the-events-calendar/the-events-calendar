<?php
/**
 * Provides integration with Views V2.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use Exception;
use stdClass;
use tad_DI52_ServiceProvider;
use Tribe__Customizer;
use Tribe__Customizer__Section;
use Tribe__Template;
use Tribe__Utils__Color;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the handlers and modifiers required to make the plugin correctly work
	 * with Views v2.
	 *
	 * @since TBD
	 */
	public function register() {
		add_filter( 'tribe_events_views_v2_by_day_view_day_results', [
			$this,
			'prepare_by_day_view_day_results',
		], 10, 2 );

		// Customizer
		add_filter( 'tribe_customizer_global_elements_css_template', [
			$this,
			'update_global_customizer_styles',
		], 10, 3 );
	}

	/**
	 * Filters the template origin namespace to add templates provided by the Custom Tables v1 implementation.
	 *
	 * @since TBD
	 * @param array<string,mixed> $namespace_map A map from template path providers to paths.
	 * @param string              $path          The absolute path
	 * @param Tribe__Template     $template      A reference to the template handler filtering the values.
	 *
	 * @return array<string,string> A map from template path providers to paths.
	 *
	 * @todo is this still required?
	 */
	public function filter_add_template_origin_namespace( $namespace_map, $path, Tribe__Template $template ) {
		$namespace_map['the-events-calendar-custom-tables-v1'] = trailingslashit( TEC_CUSTOM_TABLES_V1_ROOT );

		return $namespace_map;
	}

	/**
	 * Returns the prepared `By_Day_View` day results.
	 *
	 * @since TBD
	 *
	 * @param array<int,stdClass>|null $day_results  Either the prepared day results, or `null`
	 *                                               if the day results have not been prepared yet.
	 * @param array<int>               $event_ids    A list of the Event post IDs that should be prepared.
	 *
	 * @return array<int,stdClass> The prepared day results.
	 */
	public function prepare_by_day_view_day_results( array $day_results = null, array $event_ids = [] ) {
		return $this->container->make( By_Day_View_Compatibility::class )
		                       ->prepare_day_results( $event_ids );
	}

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @throws Exception If the Color util is built incorrectly.
	 *
	 * @param Tribe__Customizer__Section $section      The Global Elements section.
	 * @param Tribe__Customizer          $customizer   The current Customizer instance.
	 *
	 * @param string                     $css_template The CSS template, as produced by the Global Elements.
	 *
	 * @return string The filtered CSS template.
	 *
	 */
	public function update_global_customizer_styles( $css_template, $section, $customizer ) {
		if ( ! ( is_string( $css_template ) && $section instanceof Tribe__Customizer__Section && $customizer instanceof Tribe__Customizer ) ) {
			return $css_template;
		}

		if ( $customizer->has_option( $section->ID, 'accent_color' ) ) {
			$settings = $customizer->get_option( [ $section->ID ] );

			$accent_color     = new Tribe__Utils__Color( $settings['accent_color'] );
			$accent_color_rgb = $accent_color::hexToRgb( $settings['accent_color'] );
			$accent_css_rgb   = $accent_color_rgb['R'] . ',' . $accent_color_rgb['G'] . ',' . $accent_color_rgb['B'];

			$accent_color_hover  = 'rgba(' . $accent_css_rgb . ',0.8)';
			$accent_color_active = 'rgba(' . $accent_css_rgb . ',0.9)';

			// Organizer/Venue Links Overrides.
			$css_template .= '
				.tribe-common a.tribe-events-calendar-series-archive__link,
				.tribe-common a:visited.tribe-events-calendar-series-archive__link {
					color: <%= global_elements.accent_color %>;
				}

				.tribe-common a:hover.tribe-events-calendar-series-archive__link,
				.tribe-common a:focus.tribe-events-calendar-series-archive__link {
					color: ' . $accent_color_hover . ';
				}

				.tribe-common a:active.tribe-events-calendar-series-archive__link {
					color: ' . $accent_color_active . ';
				}
			';
		}

		return $css_template;
	}
}
