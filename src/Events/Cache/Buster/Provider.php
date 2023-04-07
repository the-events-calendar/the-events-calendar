<?php

namespace TEC\Events\Cache\Buster;

use Tribe\Events\Editor\Blocks\Archive_Events;
use \Tribe__Events__Main as Events_Main;

/**
 * Cache Buster Service Provider
 *
 * @since TBD
 *
 * @package TEC\Events\Cache\Buster
 */
class Provider extends \tad_DI52_ServiceProvider {

	protected $is_registered = false;

	public function register() {
		if($this->is_registered) {
			return false;
		}

        $this->is_registered = true;
        add_action('trashed_post', [$this, 'handle_on_trashed_post'],9);

		return true;
	}

	public function handle_on_trashed_post( $post_id ) {
		return $this->container->make(Post_Listener::class)->handle_on_trashed_post((int)$post_id);
	}
}