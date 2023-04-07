<?php

namespace TEC\Events\Cache\Buster;

use WP_Post;
use Tribe__Events__Main;

class Post_Listener {

	public function handle_on_clean_post_cache() {
		//do_action( 'clean_post_cache', $post->ID, $post );
	}

	public function handle_on_trashed_post( int $post_id ) {
		//do_action( 'wp_trash_post', $post_id );
		$post = get_post($post_id);
		if(!$post) {
			return;
		}

		// @todo Does the occurrence entry cache get cleared on this hook?

		$this->handle_cache_bust_for_event($post, 'trashed_post');
	}

	protected function handle_cache_bust_for_event ( WP_Post $post, string $event ) {
		if($post->post_type !== Tribe__Events__Main::POSTTYPE) {
			return false;
		}

		$post = apply_filters('tecp', $post,$event);

		return $this->delete($post);
	}

	/**
	 * @todo Move to own class? Cache_Handler ?
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function delete( WP_Post  $post):bool {
		if($post->post_type !== Tribe__Events__Main::POSTTYPE) {
			return false;
		}
		// Clear all cache related to this post that would have expired.
		// @todo Trigger filter for CT1 to do occurrence deletion?
		$cache = tribe('cache');
		$key = tribe_get_event_cache_key($cache,$post);
		$cache->delete($key);

		return true;
	}
}