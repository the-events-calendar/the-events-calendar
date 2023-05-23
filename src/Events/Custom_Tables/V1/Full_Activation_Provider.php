<?php
/**
 * Responsible for registering providers that are only relevant after an appropriate number of steps have been taken to
 * fully activate the features of Custom Tables V1.
 *
 * Should not be registered if the Custom Tables have not been generated yet.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */

namespace TEC\Events\Custom_Tables\V1;

use TEC\Common\Contracts\Service_Provider;

use TEC\Events\Custom_Tables\V1\Events\Occurrences\Max_Recurrence_Provider;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use WP_CLI;

/**
 * Class Full_Activation_Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Full_Activation_Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;
		tribe()->setVar( 'ct1_fully_activated', true );

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
			$this->container->register( Events\Event_Cleaner\Provider::class );

			// This default variable is defined in TEC, so we register it here, even though it relates to ECP.
			$this->container->register( Max_Recurrence_Provider::class );

			$this->register_schema_hooks();

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

		/**
		 * Fires an action when the Custom Tables v1 implementation is fully activated.
		 *
		 * @since 6.0.13
		 */
		do_action( 'tec_events_custom_tables_v1_fully_activated' );

		return true;
	}

	/**
	 * Registers the actions and filters required to have the custom table names available
	 * as properties on the `$wpdb` instance and to hook into some WP_CLI operations to
	 * empty the tables correctly.
	 *
	 * @since 6.0.0
	 */
	private function register_schema_hooks() {
		$schema_builder = $this->container->make( Schema_Builder::class );
		$schema_builder->register_custom_tables_names();

		if ( is_multisite() ) {
			add_action( 'activate_blog', [ $this, 'update_blog_tables' ] );
			add_action( 'activate_blog', [ $schema_builder, 'register_custom_tables_names' ] );
			add_action( 'switch_blog', [ $this, 'update_blog_tables' ] );
			add_action( 'switch_blog', [ $schema_builder, 'register_custom_tables_names' ] );
			add_filter( 'wpmu_drop_tables', [ $schema_builder, 'filter_tables_list' ] );
		}

		if ( defined( 'WP_CLI' ) && method_exists( '\\WP_CLI', 'add_hook' ) ) {
			WP_CLI::add_hook( 'after_invoke:site empty', [ $schema_builder, 'empty_custom_tables' ] );
		}
	}

	/**
	 * Updates the custom tables for a blog,
	 *
	 * @since 6.0.2
	 *
	 * @param int $blog_id The blog ID to udpate the tables for.
	 *
	 * @return void        Custom tables are updated, if required.
	 */
	public function update_blog_tables( $blog_id ): void {
		$blog_id = (int) $blog_id;

		if ( empty( $blog_id ) ) {
			// Not a valid blog ID.
			return;
		}

		if ( get_transient( Activation::ACTIVATION_TRANSIENT ) ) {
			// Already activated on this site.
			return;
		}

		// Do not run again on this site for a day.
		set_transient( Activation::ACTIVATION_TRANSIENT, time(), DAY_IN_SECONDS );

		$schema_builder = $this->container->make( Schema_Builder::class );
		$schema_builder->update_blog_tables( $blog_id );
	}
}
