<?php

namespace TEC\Events\Tests\Helper;

use Tribe\Test\Products\WPBrowser\Views\V2\TestCase;

class Views_Integration_Test extends TestCase {

	public function post_type_cache_cases() {
		return [
			'organizer before' => [ 'tribe_organizer', 'tribe_is_organizer', 'Tribe__Events__Main::isOrganizer', '_before' ],
			'venue before'     => [ 'tribe_venue', 'tribe_is_venue', 'Tribe__Events__Main::isVenue', '_before' ],
			'organizer after'  => [ 'tribe_organizer', 'tribe_is_organizer', 'Tribe__Events__Main::isOrganizer', '_after' ],
			'venue after'      => [ 'tribe_venue', 'tribe_is_venue', 'Tribe__Events__Main::isVenue', '_after' ],
		];
	}

	/**
	 * @dataProvider post_type_cache_cases
	 */
	public function test_shared_setup_clears_classifications_for_reused_post_ids( $post_type, $check, $cache_key, $hook ) {
		$backup  = tribe_get_var( $cache_key, [] );
		$post_id = static::factory()->post->create( [ 'post_type' => $post_type ] );
		try {
			$this->assertTrue( $check( $post_id ) );

			// A later fixture can reuse an ID for a different post type, while TEC's request cache survives.
			wp_update_post( [ 'ID' => $post_id, 'post_type' => 'tribe_events' ] );
			$this->getModule( '\\Helper\\Views_integration' )->$hook( $this );

			$this->assertFalse( $check( $post_id ) );
		} finally {
			tribe_set_var( $cache_key, $backup );
		}
	}
}
