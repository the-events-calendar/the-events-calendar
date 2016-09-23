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
	 * Loads WPML integration classes and event listeners.
	 *
	 * @return bool
	 */
	private function load_wpml_integration() {
		if ( ! ( class_exists( 'SitePress' ) && defined( 'ICL_PLUGIN_PATH' ) ) ) {
			return false;
		}

		Tribe__Events__Integrations__WPML__WPML::instance()->hook();

		return true;
	}

	/**
	 * Conditionally loads the classes needed to integrate with third-party plugins.
	 *
	 * Third-party plugin integration classes and methods will be loaded only if
	 * supported plugins are activated.
	 */
	public function load_integrations() {
		$this->load_wpml_integration();
	}
}