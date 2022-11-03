<?php

namespace TEC\Events\Integrations;

/**
 * Class Integration_Abstract
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations
 */
abstract class Integration_Abstract extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		// Registers this provider as a singleton for ease of use.
		$this->container->singleton( self::class, self::class );

		// Prevents any loading in case we shouldn't load.
		if ( ! $this->should_load() ) {
			return;
		}

		$this->load();
	}

	/**
	 * Gets the slug for this integration.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	abstract public static function get_slug(): string;

	/**
	 * Gets the value for if this integration should load.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function should_load(): bool {
		return $this->filter_should_load( $this->load_conditionals() );
	}

	/**
	 * Filters the value and applies all the required layers of filtering.
	 *
	 * @since TBD
	 *
	 * @param bool $value
	 *
	 * @return bool
	 */
	protected function filter_should_load( bool $value ): bool {
		$slug = static::get_slug();
		$type = static::get_type();

		/**
		 * Filters if integrations should be loaded.
		 *
		 * @since TBD
		 *
		 * @param bool $value   Should load value.
		 * @param string $type  Type of integration we are loading.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters( 'tec_events_integrations_should_load', $value, $type, $slug );

		/**
		 * Filters if integrations of a particular type should be loaded.
		 *
		 * @since TBD
		 *
		 * @param bool $value   Should load value.
		 * @param string $slug  Slug of the integration we are loading.
		 */
		$value = apply_filters( "tec_events_integrations_{$type}_should_load", $value, $slug );

		/**
		 * Filters if a particular integration should be loaded.
		 *
		 * @since TBD
		 *
		 * @param bool $value   Should load value.
		 */
		return (bool) apply_filters( "tec_events_integrations_{$type}_{$slug}_should_load", $value );
	}

	/**
	 * Determines if the integration in question should be loaded.
	 *
	 * @since TBD
	 *
	 *
	 * @return bool
	 */
	abstract public function load_conditionals(): bool;

	/**
	 * Loads the integration itself.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	abstract protected function load(): void;

	/**
	 * Determines the integration type.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	abstract public static function get_type(): string;
}