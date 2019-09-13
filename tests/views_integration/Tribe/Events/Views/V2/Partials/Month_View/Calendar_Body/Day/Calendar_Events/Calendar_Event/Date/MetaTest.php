<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event\Date;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class MetaTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/date/meta';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
