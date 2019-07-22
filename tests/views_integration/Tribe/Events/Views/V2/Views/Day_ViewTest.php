<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Day_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$day_view = View::make( Day_View::class );
		$html     = $day_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}

	public function test_render_multiple_events_on_day() {
		$events = [];

		// Create the events.
		foreach (
			[
				'09:00',
				'12:00',
				'12:30',
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date . ' today',
				'timezone'   => 'America/Sao_Paulo',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_date,
				'status'     => 'publish',
			] )->create();
		}

		// Sanity check
		$this->assertEquals( 3, tribe_events()->by( 'date_overlaps', tribe_beginning_of_day( 'today' ), tribe_end_of_day( 'today' ) )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$day_view = View::make( Day_View::class );
		$day_view->set_context( tribe_context()->alter( [
			'posts_per_page' => 2,
		] ) );
		$html = $day_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );

		$this->assertEquals(
			$expected_post_ids,
			$day_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}
}
