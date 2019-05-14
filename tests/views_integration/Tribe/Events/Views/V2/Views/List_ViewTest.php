<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;

class List_ViewTest extends \Codeception\TestCase\WPTestCase {

	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$list_view = View::make( List_View::class );
		$html      = $list_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test render with upcoming events
	 */
	public function test_render_with_upcoming_events() {
		foreach (
			[
				'2018-01-01 10am',
				'2018-01-02 8am',
				'2018-02-02 11am',
			] as $start_date
		) {
			tribe_events()->set_args( [
				'start_date' => $start_date,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event ' . md5( $start_date ),
				'status'     => 'publish',
			] )->create();
		}
		// Sanity check
		$list_date = '2018-01-01 9am';
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', $list_date )->count() );

		$list_view = View::make( List_View::class );
		$list_view->set_context( tribe_context()->alter( [
			'event_date' => $list_date,
		] ) );
		$html = $list_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}
}
