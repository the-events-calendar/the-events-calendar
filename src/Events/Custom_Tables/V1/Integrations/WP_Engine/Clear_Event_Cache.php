<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   6.0.2
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */

namespace TEC\Events\Custom_Tables\V1\Integrations\WP_Engine;

use WP_Query;
use WP_Post;

/**
 * Class Clear_Event_Cache
 *
 * @since 6.0.2.1
 *
 */
class Clear_Event_Cache {

	/**
	 * The cache group key for the WP.com event caching.
	 *
	 * @since 6.0.2.1
	 *
	 * @var string
	 */
	public static $cache_group_key = 'tec_wp_engine_queries';

	/**
	 * Clears the Single Event Post Cache due to how weirdly broken cache ends up for WP.com single event due to occurrences.
	 *
	 * @since 6.0.2.1
	 *
	 * @param array<WP_Post|int>|null $posts       The filter input value, it could have already be filtered by other
	 * @param WP_Query|null           $wp_query    A reference to the `WP_Query` instance that is currently running.
	 *                                             plugins at this stage.
	 *
	 * @return null|array<WP_Post|int> The filtered value of the posts, injected before the query actually runs.
	 */
	public function filter_posts_pre_query( $posts = null, $wp_query = null ) {
		if ( ! $wp_query instanceof WP_Query ) {
			return $posts;
		}

		$cache_hash = md5( serialize( $wp_query->query ) );

		if ( $wp_query->request !== 'SELECT * FROM wp_posts WHERE ID IN(0)' ) {
			if ( ! empty( $posts ) ) {
				wp_cache_add( $cache_hash, $posts, static::$cache_group_key );
			}
		}

		$posts = wp_cache_get( $cache_hash, static::$cache_group_key );
		if ( empty( $posts ) ) {
			return $posts;
		}

		$post = reset( $posts );
		if ( $post instanceof WP_Post ) {
			clean_post_cache( $post );
		}

		return $posts;
	}
}