<?php

namespace TEC\Events\Integrations;

use TEC\Common\Integrations\Integration_Abstract as Common_Integration_Abstract;

/**
 * Class Integration_Abstract
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations
 */
abstract class Integration_Abstract extends Common_Integration_Abstract {
	/**
	 * Filters whether the integration should load.
	 *
	 * @since 6.0.4
	 * @since TBD uses the Common integration as the base filter and then Events for Legacy compatibility.
	 *
	 * @param bool $value Whether the integration should load.
	 *
	 * @return bool
	 */
	protected function filter_should_load( bool $value ): bool {
		$value = parent::filter_should_load( $value );

		$slug = static::get_slug();
		$type = static::get_type();

		/**
		 * Filters if integrations should be loaded.
		 *
		 * @since 6.0.4
		 *
		 * @param bool $value   Whether the integration should load.
		 * @param string $type  Type of integration we are loading.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters( 'tec_events_integrations_should_load', $value, $type, $slug );

		/**
		 * Filters if integrations of the current type should be loaded.
		 *
		 * @since 6.0.4
		 *
		 * @param bool $value   Whether the integration should load.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters( "tec_events_integrations_{$type}_should_load", $value, $slug );

		/**
		 * Filters if a specific integration (by type and slug) should be loaded.
		 *
		 * @since 6.0.4
		 *
		 * @param bool $value   Whether the integration should load.
		 */
		return (bool) apply_filters( "tec_events_integrations_{$type}_{$slug}_should_load", $value );
	}
}
