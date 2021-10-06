<?php
/**
 * Handles the registration and set up of the filters required to integrate the plugin custom tables in the normal
 * WP_Query flow.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\WP_Query
 */

namespace TEC\Custom_Tables\V1\WP_Query;

use Serializable;
use TEC\Custom_Tables\V1\Edits\Event\Unstable_Occurrence;
use TEC\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Custom_Tables\V1\Models\Provisional_Post_Cache;
use TEC\Custom_Tables\V1\Service_Providers\Controllable_Service_Provider;
use TEC\Custom_Tables\V1\WP_Query\Monitors\Custom_Tables_Query_Monitor;
use TEC\Custom_Tables\V1\WP_Query\Monitors\WP_Query_Monitor;
use TEC\Custom_Tables\V1\WP_Query\Repository\Custom_Tables_Query_Filters;
use Tribe__Repository as Repository;
use WP_Query;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\WP_Query
 */
class Provider extends \tad_DI52_ServiceProvider implements Controllable_Service_Provider, Serializable {
	/**
	 * Register the filters and bindings required to integrate the plugin custom tables in the normal
	 * WP_Query flow.
	 *
	 * @since TBD
	 */
	public function register() {
		if ( ! $this->container->isBound( static::class ) ) {
			// Avoid re-bindings on Service Provider control.
			$this->container->singleton( __CLASS__, $this );
			$this->container->singleton( Replace_Results::class, Replace_Results::class );
			$this->container->singleton( WP_Query_Monitor::class, WP_Query_Monitor::class );
			$this->container->singleton( Custom_Tables_Query_Monitor::class, Custom_Tables_Query_Monitor::class );
			$this->container->singleton( Provisional_Post::class, function () {
				remove_action( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
				$provisional_post = new Provisional_Post(
					$this->container->make( Provisional_Post_Cache::class ),
					$this,
					$this->container->make( Unstable_Occurrence::class )
				);
				add_action( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
				add_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ], 10, 2 );

				return $provisional_post;
			} );
		}

		if ( ! has_action( 'pre_get_posts', [ $this, 'attach_monitor' ] ) ) {
			add_action( 'pre_get_posts', [ $this, 'attach_monitor' ], 200 );
		}

		if ( ! has_action( 'query', [ $this, 'hydrate_provisional_post' ] ) ) {
			add_action( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
		}

		if ( ! has_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ] ) ) {
			add_filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ], 10, 2 );
		}

		if ( ! has_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ] ) ) {
			add_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ], 10, 4 );
		}

		if ( ! has_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] ) ) {
			add_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] );
		}

		if ( ! has_filter( 'posts_results', $this->container->callback( Replace_Results::class, 'replace' ) ) ) {
			add_filter( 'posts_results', $this->container->callback( Replace_Results::class, 'replace' ), 10, 2 );
		}

		wp_cache_add_non_persistent_groups( [ 'tec_occurrences' ] );
	}

	/**
	 * Attaches a Monitor instance to the running query.
	 *
	 * @since TBD
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
	 * {@inheritdoc}
	 */
	public function unregister() {
		remove_action( 'pre_get_posts', [ $this, 'attach_monitor' ], 200 );
		remove_action( 'query', [ $this, 'hydrate_provisional_post' ], 200 );
		remove_action( 'tribe_repository_events_init', [ $this, 'replace_repository_query_filters' ] );
		remove_filter( 'posts_results', $this->container->callback( Replace_Results::class, 'replace' ) );
		remove_Filter( 'update_post_metadata_cache', [ $this, 'hydrate_provisional_meta_cache' ] );
		remove_filter( 'get_post_metadata', [ $this, 'hydrate_cache_on_occurrence' ] );

		$this->container->make( WP_Query_Monitor::class )->detach();
		$this->register = false;
	}

	/**
	 * Hooks on the `query` filter to hydrate a provisional post instance and accessory data
	 * if required.
	 *
	 * @since TBD
	 *
	 * @param  string  $query  The query to parse.
	 *
	 * @return string The filtered query.
	 */
	public function hydrate_provisional_post( $query ) {
		return $this->container->make( Provisional_Post::class )->hydrate_provisional_post( $query );
	}

	/**
	 * Hydrates the meta cache of an occurrence in case this cache has not been set already.
	 *
	 * @since TBD
	 *
	 * @param  null|bool  $meta  null if we can override it.
	 * @param  array      $ids   An array with the IDs that requested the meta values.
	 *
	 * @return bool|null
	 */
	public function hydrate_provisional_meta_cache( $meta, $ids ) {
		if ( $meta !== null ) {
			return $meta;
		}

		return $this->container->make( Provisional_Post::class )->hydrate_caches( $ids );
	}

	/**
	 * Hydrate the cache when a meta key is requested individually, if data is already on the cache avoid processing.
	 *
	 * @since TBD
	 *
	 * @param  mixed   $value      The value to return, either a single metadata value or an array
	 *                             of values depending on the value of `$single`. Default null.
	 * @param  int     $object_id  ID of the object metadata is for.
	 * @param  string  $meta_key   Metadata key.
	 * @param  bool    $single     Whether to return only the first value of the specified `$meta_key`.
	 *
	 * @return null
	 */
	public function hydrate_cache_on_occurrence( $value, $object_id, $meta_key, $single ) {
		$provisional_post = $this->container->make( Provisional_Post::class );
		// The requested element is not an occurrence move on.
		if ( ! $provisional_post->is_provisional_post_id( $object_id ) ) {
			return null;
		}

		$cache = wp_cache_get( $object_id, 'post_meta' );
		// This is already on the post_meta cache move on.
		if ( false !== $cache ) {
			return $value;
		}

		$provisional_post->hydrate_caches( [ $object_id ] );

		return null;
	}

	/**
	 * Hooks into the Event Repository initialization to replace the default Query Filters
	 * with an implementation that will redirect to the custom tables.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return string An empty string, to not serialize the object.
	 */
	public function serialize() {
		return '';
	}

	/**
	 * Returns void to not spawn the object from serialized data.
	 *
	 * @since TBD
	 *
	 * @param string $data The dat
	 *
	 * @return void Return void to not spawn the object from serialized data.
	 */
	public function unserialize( $data ) {
		return;
	}
}
