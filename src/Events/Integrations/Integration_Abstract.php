<?php

namespace TEC\Events\Integrations;

use TEC\Common\Integrations\Integration_Abstract as Common_Integration_Abstract;

/**
 * Class Integration_Abstract
 *
 * @since 6.0.4
 * @since 6.1.1 Extends the Common Integration
 *
 * @link  https://docs.theeventscalendar.com/apis/integrations/including-new-integrations/
 *
 * @package TEC\Events\Integrations
 */
abstract class Integration_Abstract extends Common_Integration_Abstract {

	/**
	 * @inheritDoc
	 */
	public static function get_parent(): string {
		return 'events';
	}

	/**
	 * Filters whether the integration should load.
	 *
	 * @since 6.0.4
	 * @deprecated 6.1.1 uses the Common integration as the base filter and then Events for Legacy compatibility.
	 *
	 * @param bool $value Whether the integration should load.
	 *
	 * @return bool
	 */
	protected function filter_should_load( bool $value ): bool {
		$value = parent::filter_should_load( $value );

		$slug   = static::get_slug();
		$type   = static::get_type();
		$parent = static::get_parent();

		/**
		 * Filters if integrations should be loaded.
		 *
		 * @since 6.0.4
		 * @deprecated 6.1.1
		 *
		 * @param bool $value   Whether the integration should load.
		 * @param string $type  Type of integration we are loading.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters_deprecated( 'tec_events_integrations_should_load', [ $value, $type, $slug ], '6.1.1', "tec_integration:{$parent}/should_load" );

		/**
		 * Filters if integrations of the current type should be loaded.
		 *
		 * @since 6.0.4
		 * @deprecated 6.1.1
		 *
		 * @param bool $value   Whether the integration should load.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters_deprecated( "tec_events_integrations_{$type}_should_load", [ $value, $slug ], '6.1.1', "tec_integration:{$parent}/{$type}/should_load" );

		/**
		 * Filters if a specific integration (by type and slug) should be loaded.
		 *
		 * @since 6.0.4
		 * @deprecated 6.1.1
		 *
		 * @param bool $value   Whether the integration should load.
		 */
		return (bool) apply_filters_deprecated( "tec_events_integrations_{$type}_{$slug}_should_load", [ $value ], '6.1.1', "tec_integration:{$parent}/{$type}/{$slug}/should_load" );
	}
}
