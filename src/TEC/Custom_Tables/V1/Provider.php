<?php
/**
 * Registers the Custom Tables based version of the plugin (v1), if possible.
 *
 * The provider will completely register, or not, the Custom Tables based
 * implementation. The registration will happen on `plugins_loaded::1`.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */

namespace TEC\Custom_Tables\V1;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1
 */
class Provider extends Service_Provider {
	const DISABLED = 'TEC_CUSTOM_TABLES_V1_DISABLED';

	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * A map of the filters added by the provider, the callbacks
	 * and the priority of each.
	 *
	 * @since TBD
	 *
	 * @var array<string|string,callable,int>
	 */
	private $added_filters = [];

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {
		if ( ! self::is_active() ) {
			return false;
		}

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;

		/*
		 * This block should be the only one capturing exceptions thrown in the context of
		 * the feature. This removes the burden of exception and error handling from the
		 * actual business code.
		 */
		try {
			$this->bind_implementations();
			$this->add_actions();
			$this->add_filters();

			// Any following phase will require the Custom Tables to be correctly set up and up-to-date.
			$this->container->make( Tables::class )->update();

			// Where are we with migration?
			$migration = $this->container->make( Migration::class );

			if ( $migration->is_required() || $migration->is_running() ) {
				$migration->run();
			} else {
				// Normal functions.
				$this->run_normal_operations();
			}
		} catch ( \Throwable $t ) {
			// This code will never fire on PHP 5.6, but will do in PHP 7.0+.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 7.0+.
			 *
			 * @since TBD
			 *
			 * @param \Throwable $t The thrown error.
			 */
			do_action( 'tec_custom_tables_v1_error', $t );
		} catch ( \Exception $e ) {
			// PHP 5.6 compatible code.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 5.6.
			 *
			 * @since TBD
			 *
			 * @param \Exception $e The thrown error.
			 */
			do_action( 'tec_custom_tables_v1_error', $e );
		}

		return true;
	}

	/**
	 * Returns whether the Custom Tables implementation should register, thus activate,
	 * or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Custom Tables implementation should register or not.
	 */
	public static function is_active() {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The disable constant is defined and it's truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The disable env var is defined and it's truthy.
			return false;
		}

		/**
		 * Allows filtering whether the whole Custom Tables v1 implementation
		 * should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since TBD
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_custom_tables_v1_enabled', true );
	}

	/**
	 * Binds the implementations that will be required to run the Provider-level
	 * code.
	 *
	 * @since TBD
	 */
	private function bind_implementations() {
		// Register this provider to make it easy to get hold of it.
		$this->container->singleton( 'tec.custom-tables.v1.provider', $this );
		$this->container->singleton( self::class, self::class );

		$this->container->singleton( Tables::class, Tables::class );
		$this->container->singleton( Migration::class, Migration::class );
		$this->container->singleton( Notices::class, Notices::class );
	}

	/**
	 * Hooks the action handling required by the Custom Tables implementation
	 * to work.
	 *
	 * Each action listed here can be removed by calling the provider
	 * `unhook` method.
	 *
	 * @since TBD
	 */
	private function add_actions() {
		$on_error = $this->container->callback( Notices::class, 'on_error' );
		add_action( 'tec_custom_tables_v1_error', $on_error );
		$this->added_filters['tec_custom_tables_v1_error'] = [ $on_error, 10 ];
	}

	/**
	 * Hooks the filters required by the Custom Tables implementation to work.
	 *
	 * Each filter listed here can be removed by calling the provider
	 * `unhook` method.
	 *
	 * @since TBD
	 */
	private function add_filters() {
	}

	/**
	 * Removes all the actions and filters registered by the Provider, or
	 * only the specified one.
	 *
	 * @since TBD
	 *
	 * @param string|null $filter Removes a filter or action hooked by
	 *                            the provider, if any.
	 *
	 * @return int The number of removed filters.
	 */
	public function unhook( $filter = null ) {
		if ( null !== $filter ) {
			if ( isset( $this->added_filters[ $filter ] ) ) {
				remove_filter( $filter, ...$this->added_filters[ $filter ] );
			}

			return 1;
		}

		$removed = 0;
		foreach ( $this->added_filters as $tag => list( $callback, $priority ) ) {
			remove_filter( $tag, $callback, $priority );
			$removed ++;
		}

		return $removed;
	}

	private function run_normal_operations() {
	}
}
