<?php

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Main;
use Tribe__Settings_Manager as Settings;

class RobotsTest extends \Codeception\TestCase\WPTestCase {

	public function get_view_robots_state() {
		return [
			'day' => [
				'view' => 'day',
				'include' => false,
			],
			'list' => [
				'view' => 'list',
				'include' => false,
			],
			'month' => [
				'view' => 'month',
				'include' => true,
			],
		];
	}

	/**
	 * @test
	 * @dataProvider get_view_robots_state
	 */
	public function it_should_contextually_output_robots_meta_tag_from_view( $view, $do_include ) {
		$override_context = static function() use ( $view ) {
			return $view;
		};

		add_filter( 'tribe_context_pre_view', $override_context );

		ob_start();
		tribe( 'events.views.v2.hooks' )->action_add_noindex();
		$results = trim( ob_get_clean() );

		$this->assertEquals( $do_include, !! $results );

		remove_filter( 'tribe_context_pre_view', $override_context );
	}

	/**
	 * @test
	 * @dataProvider get_view_robots_state
	 */
	public function it_should_not_output_robots_meta_tag_if_on_home( $view, $do_include ) {
		global $wp_query;

		$wp_query->is_home = true;

		$override_context = static function() use ( $view ) {
			return $view;
		};

		add_filter( 'tribe_context_pre_view', $override_context );

		ob_start();
		tribe( 'events.views.v2.hooks' )->action_add_noindex();
		$results = trim( ob_get_clean() );

		$this->assertFalse( !! $results );

		remove_filter( 'tribe_context_pre_view', $override_context );
	}

	/**
	 * @test
	 * @dataProvider get_view_robots_state
	 */
	public function it_should_output_robots_meta_tag_if_filtered_true( $view, $do_include ) {
		$override_context = static function() use ( $view ) {
			return $view;
		};

		add_filter( 'tribe_context_pre_view', $override_context );
		add_filter( 'tec_events_views_v2_robots_meta_include', '__return_true' );

		ob_start();
		tribe( 'events.views.v2.hooks' )->action_add_noindex();
		$results = trim( ob_get_clean() );

		$this->assertTrue( !! $results );
		$this->assertContains( 'noindex, nofollow', $results );

		remove_filter( 'tec_events_views_v2_robots_meta_include', '__return_true' );
		remove_filter( 'tribe_context_pre_view', $override_context );
	}

	/**
	 * @test
	 * @dataProvider get_view_robots_state
	 */
	public function it_should_not_output_robots_meta_tag_if_filtered_false( $view, $do_include ) {
		$override_context = static function() use ( $view ) {
			return $view;
		};

		add_filter( 'tribe_context_pre_view', $override_context );
		add_filter( 'tec_events_views_v2_robots_meta_include', '__return_false' );

		ob_start();
		tribe( 'events.views.v2.hooks' )->action_add_noindex();
		$results = trim( ob_get_clean() );

		$this->assertFalse( !! $results );

		remove_filter( 'tec_events_views_v2_robots_meta_include', '__return_false' );
		remove_filter( 'tribe_context_pre_view', $override_context );
	}

	/**
	 * @test
	 * @dataProvider get_view_robots_state
	 */
	public function it_should_output_custom_robots_meta_tag( $view, $do_include ) {
		$override_context = static function() use ( $view ) {
			return $view;
		};

		$override_content = static function() {
			return 'bacon';
		};

		add_filter( 'tribe_context_pre_view', $override_context );
		add_filter( 'tec_events_views_v2_robots_meta_include', '__return_true' );
		add_filter( 'tec_events_views_v2_robots_meta_content', $override_content );

		ob_start();
		tribe( 'events.views.v2.hooks' )->action_add_noindex();
		$results = trim( ob_get_clean() );

		$this->assertContains( 'bacon', $results );
		$this->assertNotContains( 'noindex, nofollow', $results );

		remove_filter( 'tec_events_views_v2_robots_meta_content', $override_content );
		remove_filter( 'tec_events_views_v2_robots_meta_include', '__return_true' );
		remove_filter( 'tribe_context_pre_view', $override_context );
	}
}
