<?php

namespace TEC\Events\Integrations;

/**
 * Class Integration_Abstract
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations
 */
abstract class Integration_Abstract extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.4
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
	 * @since 6.0.4
	 *
	 * @return string
	 */
	abstract public static function get_slug(): string;

	/**
	 * Determines whether this integration should load.
	 *
	 * @since 6.0.4
	 *
	 * @return bool
	 */
	public function should_load(): bool {
		return $this->filter_should_load( $this->load_conditionals() );
	}

	/**
	 * Filters whether the integration should load.
	 *
	 * @since 6.0.4
	 *
	 * @param bool $value Whether the integration should load.
	 *
	 * @return bool
	 */
	protected function filter_should_load( bool $value ): bool {
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

	/**
	 * Determines if the integration in question should be loaded.
	 *
	 * @since 6.0.4
	 *
	 * @return bool
	 */
	abstract public function load_conditionals(): bool;

	/**
	 * Loads the integration itself.
	 *
	 * @since 6.0.4
	 *
	 * @return void
	 */
	abstract protected function load(): void;

	/**
	 * Determines the integration type.
	 *
	 * @since 6.0.4
	 *
	 * @return string
	 */
	abstract public static function get_type(): string;
}
