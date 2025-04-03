<?php

namespace TEC\Events\WPML;

use WPML_Integration_Test_Case;

class WPML_Rewrite_Test extends WPML_Integration_Test_Case {

	/**
	 *
	 * @test
	 */
	public function should_handle_canonical_url_with_user_locale_and_wpml_languages() {
		$this->assertTrue( class_exists( \SitePress::class ) );

		$user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$locale = get_locale();
		update_user_meta( $user, 'locale', $locale );

		$rewrite = \Tribe__Events__Rewrite::instance();
		$this->assertEquals( '/events/list/', $rewrite->get_clean_url( '/events/list/?post_type=tribe_events' ) );
	}

}
