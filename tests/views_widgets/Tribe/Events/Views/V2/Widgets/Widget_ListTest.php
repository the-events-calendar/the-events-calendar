<?php
namespace Tribe\Events\Views\V2\Views\Widgets;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Widget_ListTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();
		\Tribe__Rewrite::instance()->setup();
		add_filter( 'tribe_events_views', static function ( array $views ) {
			$views['widget-events-list'] = Widget_List_View::class;

			return $views;
		} );

		remove_filter( 'post_class', 'twenty_twenty_one_post_classes', 10 );
		remove_filter( 'post_class', 'twentynineteen_post_classes', 10 );
		add_filter( 'tribe_events_views_v2_theme_compatibility_registered', '__return_empty_array' );
	}

	/**
	 * @test
	 */
	public function test_render_empty() {
		// Assert no events for clarity.
		$this->assertEmpty( tribe_events()->found() );

		$context = tribe_context()->alter(
			[
				'today'              => $this->mock_date_value,
				'now'                => $this->mock_date_value,
				'event_date'         => $this->mock_date_value,
			]
		);

		$widget_list_view = View::make( Widget_List_View::class, $context );
		$html             = $widget_list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = [];
		$this->assertEquals(
			$expected_post_ids,
			$widget_list_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_render_with_upcoming_events() {
		$events = [];

		// Create the events.
		foreach (
			[
				'tomorrow 9am',
				'+1 week',
				'+9 days',
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'America/New_York',
				'duration'   => 2 * HOUR_IN_SECONDS,
				'title'      => 'Widget Event - ' . $start_date,
				'status'     => 'publish',
			] )->create();
		}
		// Sanity check.
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$widget_list_view = View::make( Widget_List_View::class );
		$context = tribe_context()->alter( [
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'events_per_page'    => 2,
			'jsonld_enable'      => true,
		] );

		$widget_list_view->set_context( $context );
		$html = $widget_list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$widget_list_view->found_post_ids()
		);

		// @todo separate this out into a non today-based test.
		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_render_with_featured_upcoming_events() {
		$events = [];

		// Create the events.
		foreach (
			[
				'tomorrow 9am' => true,
				'+1 week' => false,
				'+9 days' => false,
			] as $start_date => $featured
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'America/New_York',
				'duration'   => 2 * HOUR_IN_SECONDS,
				'title'      => 'Widget Event - ' . $start_date,
				'status'     => 'publish',
				'featured'   => $featured,
			] )->create();
		}
		// Sanity check.
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$context = tribe_context()->alter( [
			'today'           => $this->mock_date_value,
			'now'             => $this->mock_date_value,
			'event_date'      => $this->mock_date_value,
			'events_per_page' => 5,
			'featured'        => true,
			'jsonld_enable'   => true,
		] );
		$widget_list_view = View::make( Widget_List_View::class, $context );
		$html             = $widget_list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 1 ), 'ID' );

		$this->assertEquals(
			$expected_post_ids,
			$widget_list_view->found_post_ids()
		);

		// @todo separate this out into a non today-based test.
		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function test_render_json_with_upcoming_events() {
		$events = [];

		// Create the events.
		foreach (
			[
				'tomorrow 9am',
				'+1 week',
				'+9 days',
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'America/New_York',
				'duration'   => 2 * HOUR_IN_SECONDS,
				'title'      => 'Widget Event - ' . $start_date,
				'status'     => 'publish',
			] )->create();
		}
		// Sanity check.
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$widget_list_view = View::make( Widget_List_View::class );
		$context = tribe_context()->alter( [
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'events_per_page'    => 2,
		] );

		add_filter(
			'tribe_events_views_v2_view_widget-events-list_template_vars',
			function( $template_vars ) {
				$template_vars['jsonld_enable'] = 1;
				return $template_vars;
			},
			19
		);

		$widget_list_view->set_context( $context );
		$html = $widget_list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$widget_list_view->found_post_ids()
		);

		$this->assertNotFalse( stripos( $html, 'ld+json' ) );

		// @todo separate this out into a non today-based test.
		$this->assertMatchesSnapshot( $html );
	}

	public function remove_json_ld( $template_vars ) {
		$template_vars['json_ld_data'] = false;
		return $template_vars;
	}

	/**
	 * @test
	 */
	public function test_render_no_json_with_upcoming_events() {
		add_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'remove_json_ld' ] );

		$events = [];

		// Create the events.
		foreach (
			[
				'tomorrow 9am',
				'+1 week',
				'+9 days',
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'America/New_York',
				'duration'   => 2 * HOUR_IN_SECONDS,
				'title'      => 'Widget Event - ' . $start_date,
				'status'     => 'publish',
			] )->create();
		}
		// Sanity check.
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$widget_list_view = View::make( Widget_List_View::class );
		$context = tribe_context()->alter( [
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'events_per_page'    => 2,
		] );

		$widget_list_view->set_context( $context );

		$html = $widget_list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$widget_list_view->found_post_ids()
		);
		// There is no way the snapshot will start with this string, so assertFalse seems safe enough here.
		$this->assertFalse( stripos( $html, 'ld+json' ) );

		// @todo separate this out into a non today-based test.
		$this->assertMatchesSnapshot( $html );

		remove_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'remove_json_ld' ] );
	}
}
