<?php

namespace Tribe\Events\Views\V2\SEO\Headers;

use TEC\Events\SEO\Headers\Controller;

/**
 * Tests that filter_headers() returns a 404 for any TEC view slug that is
 * currently disabled in the site's TEC settings, regardless of URL form.
 *
 * This covers the Phase 3 guard added to filter_headers() that fires before
 * the per-view (check_day_view / check_month_view / check_list_view) dispatching,
 * ensuring that optional views like Photo, Week, Map, and Summary also 404
 * when they have been turned off.
 */
class Controller_Disabled_View_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * A view slug that is absent from tribeEnableViews should always 404,
	 * even when eventDate is present (pretty-URL form).
	 *
	 * Uses 'photo' as the example since it has no dedicated check_*_view()
	 * method, which means the Phase 3 guard is the only thing that catches it.
	 *
	 * @test
	 */
	public function test_disabled_view_slug_with_event_date_shows_404(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month', 'day' ], 10, 3 );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'photo',
			'eventDate'    => '2023-06',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'Accessing a disabled view slug should return a 404.' );
	}

	/**
	 * The disabled-view guard must also fire when the request has no eventDate
	 * (e.g. the view was reached via a query-param URL rather than a pretty URL).
	 *
	 * @test
	 */
	public function test_disabled_view_slug_without_event_date_shows_404(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'week',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'A disabled view slug with no eventDate should still return a 404.' );
	}

	/**
	 * When a view is present in tribeEnableViews the disabled-view guard must
	 * not set a 404. The list view without a tribe-bar-date param is used so
	 * that no per-view date checks can also trigger a 404, keeping this test
	 * focused on the guard alone.
	 *
	 * @test
	 */
	public function test_enabled_view_slug_does_not_trigger_guard(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );

		// No tribe-bar-date → check_list_view() bails immediately (nothing to check).
		unset( $_REQUEST['tribe-bar-date'] );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'An enabled view slug should not trigger the disabled-view 404 guard.' );
	}

	/**
	 * ?eventDisplay=past is NOT a view slug — it is a display-mode modifier that
	 * renders the List view in "past events" mode. It must never trigger the
	 * disabled-view 404 guard, even though 'past' is absent from tribeEnableViews.
	 *
	 * @test
	 */
	public function test_past_display_mode_does_not_trigger_guard_when_list_enabled(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );

		unset( $_REQUEST['tribe-bar-date'] );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'past',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse(
			$wp_query->is_404,
			'?eventDisplay=past is a List view modifier, not a view slug — it must not 404 when list is enabled.'
		);
	}

	/**
	 * ?eventDisplay=past should 404 when the List view itself is disabled,
	 * because past-events browsing depends on the List view being available.
	 *
	 * @test
	 */
	public function test_past_display_mode_shows_404_when_list_disabled(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'month' ], 10, 3 ); // list not enabled.

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'past',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue(
			$wp_query->is_404,
			'?eventDisplay=past should 404 when the List view is disabled, since it relies on the list view layout.'
		);
	}

	/**
	 * Non-tribe_events post types must be ignored entirely; the guard must not
	 * fire for regular WordPress post types that happen to carry an eventDisplay
	 * query var.
	 *
	 * @test
	 */
	public function test_wrong_post_type_skips_guard(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'post',   // Not tribe_events.
			'eventDisplay' => 'photo',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'The guard should not fire for non-tribe_events post types.' );
	}

	/**
	 * When eventDisplay is absent there is nothing to validate; the method
	 * returns early before reaching the guard.
	 *
	 * @test
	 */
	public function test_missing_event_display_skips_guard(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );

		global $wp_query;
		$wp_query->query = [
			'post_type' => 'tribe_events',
			// eventDisplay intentionally absent.
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'When eventDisplay is absent the guard should not fire.' );
	}
}
