<?php
/**
 * Handles the integration with Fusion Core.
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Pro\Integrations\Fusion
 */

namespace Tribe\Events\Integrations\Fusion;

use TEC\Common\Contracts\Service_Provider as Provider_Contract;


/**
 * Class Service_Provider
 *
 * @since   5.5.0
 *
 * @package Tribe\Events\Integrations\Fusion
 */
class Service_Provider extends Provider_Contract {


	/**
	 * Registers the bindings and hooks the filters required for the Fusion Core integration to work.
	 *
	 * @since   5.5.0
	 */
	public function register() {
		// Bail in case Fusion core is not loaded.
		if ( ! defined( 'FUSION_CORE_VERSION' ) || empty( FUSION_CORE_VERSION ) ) {
			return;
		}

		// Fusion compatibility only for V2 users.
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$this->container->singleton( Widget_Shortcode::class, Widget_Shortcode::class );

		// Register the hooks related to this integration.
		$this->register_hooks();
	}

	/**
	 * Register the hooks for Fusion integration.
	 *
	 * @since   5.5.0
	 */
	public function register_hooks() {
		add_action( 'shortcode_atts_fusion_widget', [ $this, 'filter_shortcode_widget_atts' ], 25, 4 );
	}

	/**
	 * Builds and hooks the class that will handle shortcode support in the context of Fusion Core.
	 *
	 * @since 5.5.0
	 *
	 * @param array  $out       The output array of shortcode attributes.
	 * @param array  $pairs     The supported attributes and their defaults.
	 * @param array  $atts      The user defined shortcode attributes.
	 * @param string $shortcode The shortcode name.
	 *
	 * @return array Change the attributes to fix the class name after WordPress borks the Namespaced method.
	 */
	public function filter_shortcode_widget_atts( $out, $pairs, $atts, $shortcode ) {
		return $this->container->make( Widget_Shortcode::class )->fix_type_for_namespaced_widgets( $out, $pairs, $atts, $shortcode );
	}
}
