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

		$list_view = View::make( Widget_List_View::class, $context );
		$html      = $list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = [];
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
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

		$list_view = View::make( Widget_List_View::class );
		$context = tribe_context()->alter( [
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'events_per_page'    => 2,
		] );

		$list_view->set_context( $context );
		$html = $list_view->get_html();

		// Let's make sure the View is displaying the events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}
}
