<?php
namespace Tribe\Events\Views\V2\Views\Widgets;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Widgets\List_Widget;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class List_WidgetTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();
		\Tribe__Rewrite::instance()->setup();
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$context = tribe_context()->alter(
			[
				'event_display'      => 'list',
				'event_display_mode' => 'list',
				'today'              => $this->mock_date_value,
				'now'                => $this->mock_date_value,
				'event_date'         => $this->mock_date_value,
			]
		);

		$list_view = View::make( List_Widget::class, $context );
		$html      = $list_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = [];
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}
}
