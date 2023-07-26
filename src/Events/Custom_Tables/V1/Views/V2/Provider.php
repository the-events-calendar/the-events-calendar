<?php
/**
 * Provides integration with Views V2.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events\Custom_Tables\V1\Views\V2;

use Exception;
use stdClass;
use Tribe__Customizer as Customizer;
use Tribe__Customizer__Section as Customizer_Section;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Views\V2
 */
class Provider extends Service_Provider {


	/**
	 * Registers the handlers and modifiers required to make the plugin correctly work
	 * with Views v2.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( Customizer_Compatibility::class, Customizer_Compatibility::class );

		add_filter( 'tribe_events_views_v2_by_day_view_day_results', [
			$this,
			'prepare_by_day_view_day_results',
		], 10, 2 );

		// Handle Customizer styles.
		add_filter( 'tribe_customizer_global_elements_css_template', [
			$this,
			'update_global_customizer_styles',
		], 10, 3 );
	}

	/**
	 * Returns the prepared `By_Day_View` day results.
	 *
	 * @since 6.0.0
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
		return $this->container->make( Customizer_Compatibility::class )
		                       ->update_global_customizer_styles( $css_template, $section, $customizer );;
	}
}
