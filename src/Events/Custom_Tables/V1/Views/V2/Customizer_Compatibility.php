<?php
/**
 * Handles the custom tables v1 implementation compatibility with the Customizer
 * controls and settings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2;
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use Tribe__Customizer as Customizer;
use Tribe__Customizer__Section as Customizer_Section;
use Tribe__Utils__Color as Color;

/**
 * Class Customizer_Compatibility.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2;
 */
class Customizer_Compatibility {

	/**
	 * Filters the Global Elements section CSS template to add Views v2 related style templates to it.
	 *
	 * @since 6.0.0
	 *
	 * @param Customizer_Section $section      The Global Elements section.
	 * @param Customizer         $customizer   The current Customizer instance.
	 * @param string             $css_template The CSS template, as produced by the Global Elements.
	 *
	 * @return string The filtered CSS template.
	 *
	 * @throws Exception If the Color util is built incorrectly.
	 *
	 */
	public function update_global_customizer_styles( $css_template, $section, $customizer ) {
		if ( ! ( is_string( $css_template ) && $section instanceof Customizer_Section && $customizer instanceof Customizer ) ) {
			return $css_template;
		}

		if ( $customizer->has_option( $section->ID, 'accent_color' ) ) {
			$settings = $customizer->get_option( [ $section->ID ] );

			$accent_color     = new Color( $settings['accent_color'] );
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