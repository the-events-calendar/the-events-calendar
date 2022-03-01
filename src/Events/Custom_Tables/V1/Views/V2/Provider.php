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
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Customizer;
use Tribe__Customizer__Section;
use Tribe__Utils__Color;
use Tribe__Events__Main as TEC;

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

		// Setup the correct post slug
		add_filter( 'wp_unique_post_slug', [ $this, 'unique_post_slug_for_occurrence' ], 10, 6 );
	}

	/**
	 * Checks if this occurrence has a unique post slug.
	 *
	 * @since TBD
	 *
	 * @param $slug
	 * @param $post_ID
	 * @param $post_status
	 * @param $post_type
	 * @param $post_parent
	 * @param $original_slug
	 *
	 * @return mixed
	 */
	public function unique_post_slug_for_occurrence( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		global $wpdb;
		if ( TEC::POSTTYPE !== $post_type ) {
			return $slug;
		}
		$provisional_post = tribe( Provisional_Post::class );
		if ( ! $provisional_post->is_provisional_post_id( $post_ID ) ) {
			return $slug;
		}
		$occurrence_id = $provisional_post->normalize_provisional_post_id( $post_ID );
		if ( ! ( $occurrence = Occurrence::find( $occurrence_id ) ) ) {
			return $slug;
		}

		$real_post_id    = $occurrence->post_id;
		$check_sql       = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $original_slug, $post_type, $real_post_id ) );

		// Unique title?
		if ( $post_name_check ) {
			return $slug;
		}

		return $original_slug;
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
