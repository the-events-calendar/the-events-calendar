<?php

use Tribe\Events\Integrations\Beaver_Builder;
use Tribe\Events\Integrations\Fusion\Service_Provider as Fusion_Integration;
use Tribe\Events\Integrations\Hello_Elementor\Service_Provider as Hello_Elementor_Integration;
use Tribe\Events\Integrations\WP_Rocket;

/**
 * Class Tribe__Events__Integrations__Manager
 *
 * Loads and manages the third-party plugins integration implementations.
 */
class Tribe__Events__Integrations__Manager {

	/**
	 * @var Tribe__Events__Integrations__Manager
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Integrations__Manager
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Conditionally loads the classes needed to integrate with third-party plugins.
	 *
	 * Third-party plugin integration classes and methods will be loaded only if
	 * supported plugins are activated.
	 */
	public function load_integrations() {
		$this->load_freemius();
		$this->load_acf_integration();
		$this->load_twenty_seventeen_integration();
		$this->load_wpml_integration();
		$this->load_X_theme_integration();
		$this->load_wp_rocket_integration();
		$this->load_beaver_builder_integration();
		$this->load_fusion_integration();
		$this->load_hello_elementor_integration();
	}

	/**
	 * Loads our Events Freemius integration
	 *
	 * @since 4.9
	 *
	 * @return bool
	 */
	private function load_freemius() {
		/**
		 * Allows third-party disabling of The Events Calendar integration
		 *
		 * @since  4.9
		 *
		 * @param  bool  $should_load
		 */
		$should_load = apply_filters( 'tribe_events_integrations_should_load_freemius', true );

		if ( ! $should_load ) {
			return false;
		}

		tribe_singleton( 'events.integrations.freemius', new Tribe__Events__Integrations__Freemius );

		return true;
	}


	/**
	 * Loads our ACF integrations if that theme is active.
	 *
	 * @return bool
	 */
	private function load_acf_integration() {
		if ( ! class_exists( 'acf' ) ) {
			return false;
		}

		Tribe__Events__Integrations__ACF__ACF::instance()->hook();

		return true;
	}

	/**
	 * Loads our Twenty Seventeen integrations if that theme is active.
	 *
	 * @since 4.5.10
	 *
	 * @return bool
	 */
	protected function load_twenty_seventeen_integration() {
		$theme = get_stylesheet();

		if ( 'twentyseventeen' === $theme ) {
			tribe( 'tec.integrations.twenty-seventeen' );
			return true;
		}

		return false;
	}

	/**
	 * Loads WPML integration classes and event listeners.
	 *
	 * @return bool
	 */
	private function load_wpml_integration() {
		if ( ! tribe_is_wpml_active() ) {
			return false;
		}

		Tribe__Events__Integrations__WPML__WPML::instance()->hook();

		return true;
	}

	/**
	 * Loads our X Theme integrations if that theme is active.
	 *
	 * @return bool
	 */
	private function load_X_theme_integration() {
		$theme = wp_get_theme();

		if ( 'x' !== $theme->get_template() ) {
			return false;
		}

		Tribe__Events__Integrations__X_Theme__X_Theme::instance()->hook();

		return true;
	}

	/**
	 * Loads our WP Rocket plugin integration.
	 *
	 * @since 5.0.0.2
	 *
	 * @return bool Whether we loaded WP Rocket compatibility or not.
	 */
	private function load_wp_rocket_integration() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return false;
		}

		tribe( WP_Rocket::class )->hook();

		return true;
	}

	/**
	 * Loads our beaver builder plugin integration.
	 *
	 * @since  5.0.2
	 *
	 * @return bool Whether we loaded Beaver Builder compatibility or not.
	 */
	private function load_beaver_builder_integration() {
		if ( ! class_exists( 'FLThemeBuilderLoader' ) || ! class_exists( 'FLBuilderLoader' ) ) {
			return false;
		}

		tribe( Beaver_Builder::class )->hook();

		return true;
	}

	/**
	 * Loads the Fusion integration if Fusion Core is currently active.
	 *
	 * @since 5.5.0
	 */
	public function load_fusion_integration() {
		tribe_register_provider( Fusion_Integration::class );
	}

	/**
	 * Loads the Hello Elementor theme integration.
	 *
	 * @since 5.7.0
	 */
	private function load_hello_elementor_integration() {
		tribe_register_provider( Hello_Elementor_Integration::class );
	}
}
