<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */

namespace TEC\Events\Custom_Tables\V1\Integrations\DotCom;

use WP_Query;
use WP_Post;

/**
 * Class Clear_Event_Cache
 *
 * @since TBD
 *
 */
class Clear_Event_Cache {

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
		if ( $wp_query->request !== 'SELECT * FROM wp_post WHERE ID IN(0)' ) {
			return $posts;
		}

		$args = $wp_query->query;
		$random_post_id = $this->get_random_post_id_nonexistent();
		$args['post__not_in'] = $random_post_id;
		$posts = get_posts( $args );
		$post = reset( $posts );
		clean_post_cache( $post->ID );

		return $posts;
	}

	/**
	 * Gets a non-existent post ID for the purposes of purging cache for the wp_query.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	protected function get_random_post_id_nonexistent(): int {
		// will find an ID that doesn't exist.
		do {
			$post_id = random_int( 500000, 1000000 );
			$post = get_post( $post_id );
		} while( $post instanceof WP_Post );

		return $post_id;
	}

}