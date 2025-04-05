<?php
/**
 * Registers the Custom Tables based version of the plugin (v1), if possible.
 *
 * The provider will completely register, or not, the Custom Tables based
 * implementation. The registration will happen on `plugins_loaded::1`.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */

namespace TEC\Events\Custom_Tables\V1;

use TEC\Common\Contracts\Container;

use TEC\Common\Contracts\Service_Provider;

use TEC\Events\Custom_Tables\V1\Migration\State;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Provider extends Service_Provider {
	const DISABLED = 'TEC_CUSTOM_TABLES_V1_DISABLED';

	/**
	 * The custom action that will be fired when the provider registers.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public static string $registration_action = 'tec_ct1_provider_registered';

	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * A map of the filters added by the provider, the callbacks
	 * and the priority of each.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string|string,callable,int>
	 */
	private $added_filters = [];

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.0
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

		add_action( 'tec_events_custom_tables_v1_error', [ $this, 'log_errors' ] );

		$this->did_register = true;

		/*
		 * This block should be the only one capturing exceptions thrown in the context of
		 * the feature. This removes the burden of exception and error handling from the
		 * actual business code.
		 */
		try {
			$this->bind_implementations();
			$this->container->singleton( Container::class, $this->container );

			// Register this provider to allow getting hold of it from third-party code.
			$this->container->singleton( self::class, self::class );
			$this->container->singleton( 'tec.custom-tables.v1.provider', self::class );
			$this->container->register( Tables\Provider::class );
			$this->container->register( Migration\Provider::class );
			// *NOTE* - Ensure only adding providers that are always required in here,
			// versus most features that should go in the `Full_Activation_Provider`.

			$state = $this->container->make( State::class );
			// Should we fully activate?
			if ( $state->is_migrated() ) {
				/**
				 * These providers should be the ones that extend the bulk of features for CT1,
				 * with only the bare minimum of providers registered above, to determine important state information.
				 */
				$this->container->register( Full_Activation_Provider::class );
				// Set a flag in the container to indicate there was a full activation of the CT1 component.
				$this->container->setVar( 'ct1_fully_activated', true );
			}

			$this->add_filters();

			/*
			 * Integrations with 3rd party code are registered last to
			 * allow for their registration to happen on the "ready"
			 * state of the container.
			 */
			$this->container->register( Integrations\Provider::class );
		} catch ( \Throwable $t ) {
			// This code will never fire on PHP 5.6, but will do in PHP 7.0+.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 7.0+.
			 *
			 * @since 6.0.0
			 *
			 * @param \Throwable $t The thrown error.
			 */
			do_action( 'tec_events_custom_tables_v1_error', $t );
		} catch ( \Exception $e ) {
			// PHP 5.6 compatible code.

			/**
			 * Fires an action when an error or exception happens in the
			 * context of Custom Tables v1 implementation AND the server
			 * runs PHP 5.6.
			 *
			 * @since 6.0.0
			 *
			 * @param \Exception $e The thrown error.
			 */
			do_action( 'tec_events_custom_tables_v1_error', $e );
		}

		return true;
	}

	/**
	 * Returns whether the Custom Tables implementation should register, thus activate,
	 * or not.
	 *
	 * @since 6.0.0
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

		// Finally read an option value to determine if the feature should be active or not.
		$active = (bool) get_option( 'tec_custom_tables_v1_active', true );

		/**
		 * Allows filtering whether the whole Custom Tables v1 implementation
		 * should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_events_custom_tables_v1_enabled', $active );
	}

	/**
	 * Binds the implementations that will be required to run the Provider-level
	 * code.
	 *
	 * @since 6.0.0
	 */
	private function bind_implementations() {
		// Register this provider to make it easy to get hold of it.
		$this->container->singleton( 'tec.custom-tables.v1.provider', $this );
		$this->container->singleton( self::class );

		$this->container->singleton( Notices::class );
	}

	/**
	 * Hooks to the Filters API some general-purpose, high-level, code.
	 *
	 * Each action listed here can be removed by calling the provider
	 * `unhook` method.
	 *
	 * @since 6.0.0
	 */
	private function add_filters() {
		$on_error = $this->container->callback( Notices::class, 'on_error' );
		add_action( 'tec_custom_tables_v1_error', $on_error );
		$this->added_filters['tec_custom_tables_v1_error'] = [ $on_error, 10 ];

		// If the plugin has been silently activated, then init it now.
		add_action( 'init', [ Activation::class, 'init' ] );
		add_filter( 'tec_system_information', [ Activation::class, 'filter_include_migration_in_system_info' ] );
	}

	/**
	 * Removes all the actions and filters registered by the Provider, or
	 * only the specified one.
	 *
	 * @since 6.0.0
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

	/**
	 * Logs the error.
	 *
	 * @since 6.1.3
	 *
	 * @param \Throwable $error The error to log.
	 */
	public function log_errors( $error ): void {
		if ( ! $error instanceof \Throwable ) {
			return;
		}

		do_action( 'tribe_log', 'error', 'Caught Custom Tables V1 activation error.', [
			'message' => $error->getMessage(),
			'file'    => $error->getFile(),
			'line'    => $error->getLine(),
		] );
	}
}
