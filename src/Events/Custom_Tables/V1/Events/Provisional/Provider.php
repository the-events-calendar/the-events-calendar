<?php
/**
 * Manages the provisional events.
 *
 * @since 6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */

namespace TEC\Events\Custom_Tables\V1\Events\Provisional;

use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator as Provisional_ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post_Cache;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main as TEC;

/**
 * Class Provider
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */
class Provider extends Service_Provider {

	/**
	 * Registers the service provider functions.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( Provisional_ID_Generator::class, Provisional_ID_Generator::class );
		$this->container->singleton( Meta::class, Meta::class );

		if ( is_multisite() ) {
			$this->register_multisite_actions();
		}

		add_action( 'wp_insert_post', [ $this, 'flush_cache' ] );
		add_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'flush_event_cache' ] );
		// The callback identity must match the one Meta::update_metadata() removes and re-adds on itself.
		add_filter( 'update_post_metadata', [ $this->container->make( Meta::class ), 'update_metadata' ], 0, 5 );
	}

	/**
	 * Removes the actions and filters set by this provider.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'wp_insert_post', [ $this, 'flush_cache' ] );
		remove_action( 'save_post_' . TEC::POSTTYPE, [ $this, 'flush_event_cache' ] );
		remove_filter( 'update_post_metadata', [ $this->container->make( Meta::class ), 'update_metadata' ], 0 );

		if ( is_multisite() ) {
			remove_action( 'activate_blog', [ $this, 'on_activation' ] );
		}
	}

	/**
	 * In case a new post ID is created make sure the base is updated correctly.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The inserted post ID.
	 */
	public function flush_cache( $post_id ) {
		$id_generator = $this->container->make( Provisional_ID_Generator::class );
		if ( $id_generator->needs_change() ) {
			tribe( Provisional_Post_Cache::class )->flush_all();
			$id_generator->sync_above_max_id();
		}
	}

	/**
	 * Flush the cache of an event ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to flush the Occurrence caches for.
	 */
	public function flush_event_cache( $post_id ) {
		$this->container->make( Provisional_Post_Cache::class )->flush_occurrences( $post_id );
	}

	/**
	 * Hooks specifically for WP MU Installations.
	 *
	 * @since 6.0.0
	 */
	protected function register_multisite_actions() {
		add_action( 'activate_blog', [ $this, 'on_activation' ] );
	}

	/**
	 * Action fired when the plugin was installed.
	 *
	 * @since 6.0.0
	 */
	public function on_activation() {
		$this->container->make( Provisional_ID_Generator::class )->install();
	}

	/**
	 * Action fired once the plugin has been deactivated.
	 *
	 * @since 6.0.0
	 */
	public function on_deactivation() {
		$this->container->make( Provisional_ID_Generator::class )->uninstall();
		$this->container->make( Provisional_Post_Cache::class )->flush_all();
	}
}
