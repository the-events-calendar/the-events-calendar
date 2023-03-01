<?php

namespace Tribe\Events\Integrations;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Integrations__WPML__Linked_Posts;
use Tribe__Events__Integrations__WPML__Rewrites;
use Tribe__Log;

// WPML functions


class WPMLTest extends WPTestCase {
	use With_Uopz;

	public function rewrite_rules_data_provider() {
		return [
			// Expected, Faux Rules
			'string'      =>
				[ 'bob', 'bob' ]
			,
			'int'         => [ 1, 1 ],
			'bool'        =>
				[ false, false ],
			'null'        =>
				[ null, null ],
			'empty array' => [ [], [] ]

		];
	}

	/**
	 * @test
	 * @dataProvider rewrite_rules_data_provider
	 * @skip SIGSEGV (139) when running in CI context; investigation will follow.
	 */
	public function should_handle_filter_rewrite_rules_ok( $expected, $faux_rewrite ) {
		$wpml_rewrite = Tribe__Events__Integrations__WPML__Rewrites::instance();

		// Ensure no errors and the data is translated as expected.
		$this->assertEquals( $expected, $wpml_rewrite->filter_rewrite_rules_array( $faux_rewrite ) );
	}

	/**
	 * @test
	 * @skip SIGSEGV (139) when running in CI context; investigation will follow.
	 */
	public function should_handle_missing_lang_code_linked_post_filter() {
		$venue_id = tribe_create_venue( [
			'Venue' => 'Deathstar Loading Dock'
		] );
		$event_id = tribe_create_event( [ 'post_title' => "Frank's Fridders" ] );

		// WPML setup
		// 99 is WPML error code for failure.
		$fail_code = 99;
		// No lang code.
		$data = [];
		$this->set_const_value( 'ICL_LANGUAGE_CODE', null );
		$this->set_const_value( 'WPML_API_ERROR', $fail_code );
		if ( function_exists( 'wpml_get_language_information' ) ) {
			$this->set_fn_return( 'wpml_get_language_information', $data );
			$this->set_fn_return( 'wpml_add_translatable_content', $fail_code );
		} else {
			$this->add_fn( 'wpml_get_language_information',
				function () use ( $data ) {
					return $data; //
				} );
			$this->add_fn( 'wpml_add_translatable_content',
				function () use ( $fail_code ) {
					return $fail_code; // 99 is WPML error code for failure
				} );
		}
		$track_errors         = new \stdClass();
		$track_errors->whoops = 0;
		// This func will exit, so mock it.
		$this->set_class_fn_return(
			Tribe__Log::class,
			'log_error',
			function () use ( $track_errors ) {
				$track_errors->whoops ++;
			},
			true
		);
		// Should not fail/stop running.
		$wpml_linked_posts = tribe( Tribe__Events__Integrations__WPML__Linked_Posts::class );
		$wpml_linked_posts->filter_tribe_events_linked_post_create( $venue_id, [], 'tribe_venue', 'published', $event_id );
		$this->assertEquals( 0, $track_errors->whoops );
	}
}
