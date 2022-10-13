<?php

namespace TEC\Events\Custom_Tables\V1\Integrations\DotCom;

use tad_DI52_ServiceProvider;
use WP_Query;
use WP_Post;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations\DotCom
 */
class Provider extends tad_DI52_ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		// Ensures that the integration only does anything when in WP.com environment.
		if ( ! defined( 'WPCOMSH_VERSION' ) ) {
			return;
		}

		$this->hook();
	}

	/**
	 * Hooks all the actions and filters.
	 *
	 * @since TBD
	 */
	protected function hook(): void {
		add_filter( 'tec_events_custom_tables_v1_events_only_modifier_filter_posts_pre_query', [ $this, 'filter_posts_pre_query' ], 101, 2 );
	}

	/**
	 * Unhooks all the actions and filters.
	 *
	 * @since TBD
	 */
	protected function unhook(): void {
		remove_filter( 'tec_events_custom_tables_v1_events_only_modifier_filter_posts_pre_query', [ $this, 'filter_posts_pre_query' ], 101 );
	}

	/**
	 * Clears the Single Event Post Cache due to how weirdly broken cache ends up for WP.com single event due to occurrences.
	 *
	 * @since TBD
	 *
	 * @param WP_Query|null           $wp_query    A reference to the `WP_Query` instance that is currently running.
	 * @param array<WP_Post|int>|null $posts       The filter input value, it could have already be filtered by other
	 *                                             plugins at this stage.
	 *
	 * @return null|array<WP_Post|int> The filtered value of the posts, injected before the query actually runs.
	 */
	public function filter_posts_pre_query( $posts = null, $wp_query = null ) {
		return $this->container->make( Clear_Event_Cache::class )->filter_posts_pre_query( $posts, $wp_query );
	}

}