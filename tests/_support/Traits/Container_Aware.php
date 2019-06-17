<?php

namespace Tribe\Events\Test\Traits;


trait Container_Aware {

	/**
	 * When working with containers (e.g. Docker) there's a "lag" between the writing of a file on the host
	 * and the real update of the file on the guest (the container).
	 */
	public function wait_for_container_to_sync_files() {
		if ( ! $this->is_using_containers() ) {
			return;
		}
		sleep( 2 );
	}

	/**
	 * Whether we're using containers for the tests or not.
	 *
	 * @return bool
	 */
	public function is_using_containers() {
		return (bool) getenv( 'USING_CONTAINERS' );
	}
}
