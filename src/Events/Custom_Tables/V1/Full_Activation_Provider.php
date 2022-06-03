<?php
/**
 * Responsible for registering providers that are only relevant after an appropriate number of steps have been taken to
 * fully activate the features of Custom Tables V1.
 *
 * Should not be registered if the Custom Tables have not been generated yet.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1
 */

namespace TEC\Events\Custom_Tables\V1;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Full_Activation_Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Full_Activation_Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {

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
			// Register this provider to allow getting hold of it from third-party code.
			$this->container->singleton( self::class, self::class );
			$this->container->register( WP_Query\Provider::class );
			$this->container->register( Updates\Provider::class );
			$this->container->register( Repository\Provider::class );
			$this->container->register( Views\V2\Provider::class );

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
			do_action( 'tec_events_custom_tables_v1_error', $t );
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
			do_action( 'tec_events_custom_tables_v1_error', $e );
		}

		return true;
	}
}
