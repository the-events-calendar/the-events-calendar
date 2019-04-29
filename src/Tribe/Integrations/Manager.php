<?php


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
}
