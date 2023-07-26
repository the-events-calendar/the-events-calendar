<?php
namespace Events\WPML;
use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Custom_Tables\V1\Provider as CT1_Provider;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Views\V2\Hooks;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Venue as Venue;

class WPML_Rewrite_Test extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Uopz;


	/**
	 *
	 * @test
	 */
	public function should_handle_canonical_url_with_user_locale_and_wpml_languages( ) {
		// @todo 
		$this->assertTrue(class_exists(\SitePress::class));

		$user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$locale = get_locale();
		update_user_meta($user,'locale',$locale);


		$this->set_permalink_structure('/%postname%/');

		$rewrite = \Tribe__Events__Rewrite::instance();
		$this->assertEquals('/events/list/', $rewrite->get_clean_url('/events/list/?post_type=tribe_events'));

	}

}
