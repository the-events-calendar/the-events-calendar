<?php
/**
 * Abstract Manager class for Elementor integrations.
 *
 * @since 6.4.0
 *
 * @package Tribe\Events\Integrations\Plugins\Elementor
 */

namespace TEC\Events\Integrations\Plugins\Elementor;

/**
 * Class Manager_Abstract
 *
 * @since 6.4.0
 *
 * @package Tribe\Events\Integrations\plugins\Elementor
 */
abstract class Manager_Abstract {
	/**
	 * @var string Type of object.
	 */
	protected $type;

	/**
	 * @var array Collection of objects to register.
	 */
	protected $objects;

	/**
	 * Returns an associative array of objects to be registered.
	 *
	 * @since 6.4.0
	 *
	 * @return array An array in the shape `[ <slug> => <class> ]`.
	 */
	public function get_registered_objects() {
		/**
		 * Filters the list of objects available and registered.
		 *
		 * Both classes and built objects can be associated with a slug; if bound in the container the classes
		 * will be built according to the binding rules; objects will be returned as they are.
		 *
		 * @since 6.4.0
		 *
		 * @param array $widgets An associative array of objects in the shape `[ <slug> => <class> ]`.
		 */
		return (array) apply_filters( "tec_events_elementor_registered_{$this->type}", $this->objects );
	}

	/**
	 * Registers the objects with Elementor.
	 *
	 * @since 6.4.0
	 */
	abstract public function register();
}
