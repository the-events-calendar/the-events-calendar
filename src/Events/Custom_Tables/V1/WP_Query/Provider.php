<?php
/**
 * Handles the registration and set up of the filters required to integrate the plugin custom tables in the normal
 * WP_Query flow.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query;

use Serializable;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\Custom_Tables_Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Monitors\WP_Query_Monitor;
use TEC\Events\Custom_Tables\V1\WP_Query\Repository\Custom_Tables_Query_Filters;
use Tribe__Repository as Repository;
use TEC\Common\Contracts\Service_Provider;

use WP_Query;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
class Provider extends Service_Provider implements Serializable, Provider_Contract {
	/**
	 * Register the filters and bindings required to integrate the plugin custom tables in the normal
	 * WP_Query flow.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		if ( ! $this->container->isBound( static::class ) ) {
			// Avoid re-bindings on Service Provider control.
			$this->container->singleton( __CLASS__, $this );
			$this->container->singleton( WP_Query_Monitor::class, WP_Query_Monitor::class );
			$this->container->singleton( Custom_Tables_Query_Monitor::class, Custom_Tables_Query_Monitor::class );
		}

		if ( ! has_action( 'pre_get_posts', [ $this, 'attach_monitor' ] ) ) {
			add_action( 'pre_get_posts', [ $this, 'attach_monitor' ], 200 );
		}

		if ( ! has_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] ) ) {
			add_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] );
		}

		wp_cache_add_non_persistent_groups( [ 'tec_occurrences' ] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function unregister() {
		remove_action( 'pre_get_posts', [ $this, 'attach_monitor' ], 200 );
		remove_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] );

		$this->container->make( WP_Query_Monitor::class )->detach();
		$this->register = false;
	}

	/**
	 * Attaches a Monitor instance to the running query.
	 *
	 * @since 6.0.0
	 *
	 * @param  WP_Query  $query  A reference to the currently running query.
	 */
	public function attach_monitor( $query ) {
		if ( ! $query instanceof WP_Query ) {
			return;
		}

		$this->container->make( WP_Query_Monitor::class )->attach( $query );
	}

	/**
	 * Hooks into the Event Repository initialization to replace the default Query Filters
	 * with an implementation that will redirect to the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param  Repository  $repository  A reference to the instance of the repository that is initializing.
	 */
	public function replace_repository_query_filters( Repository $repository ) {
		$custom_tables_query_filters = $this->container->make( Custom_Tables_Query_Filters::class );
		add_filter( 'posts_groupby', [ $custom_tables_query_filters, 'group_by_occurrence_id' ], 200, 2 );
		$repository->filter_query = $custom_tables_query_filters;
	}

	/**
	 * Implements the method that is going to be invoked to serialize
	 * the class to make sure the Container instance, that uses non-serializable
	 * Closures, will not be part of the serialized data.
	 *
	 * @since 6.0.0
	 *
	 * @return string An empty string, to not serialize the object.
	 */
	public function serialize() {
		return '';
	}

	/**
	 * Returns void to not spawn the object from serialized data.
	 *
	 * @since 6.0.0
	 *
	 * @param string $data The data to unserialize.
	 *
	 * @return void Return void to not spawn the object from serialized data.
	 */
	public function unserialize( $data ) {
		return;
	}

	/**
	 * Implements the method that is going to be invoked to serialize
	 * the class to make sure the Container instance, that uses non-serializable
	 * Closures, will not be part of the serialized data.
	 *
	 * @since 6.0.6
	 *
	 * @return array An empty array, the object is not serializable.
	 */
	public function __serialize():array {
		return [];
	}

	/**
	 * Returns void to not spawn the object from serialized data.
	 *
	 * @since 6.0.6
	 *
	 * @param array $data The data to unserialize.
	 *
	 * @return void Return void to not spawn the object from serialized data.
	 */
	public function __unserialize( array $data ): void {
		return;
	}

}
