<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class RecurringTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'day/event/recurring';

	/**
	 * Test render with recurring event
	 */
	public function test_render_with_recurring_event() {
		$event                = $this->get_mock_event( 'events/single/1.json' );
		$event->recurring     = true;
		$event->permalink_all = 'http://test.tri.be/event/mock-event/all/';

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render is empty for a non recurring event
	 */
	public function test_render_is_empty_for_a_non_recurring_event() {
		$event            = $this->get_mock_event( 'events/single/1.json' );
		$event->recurring = false;

		$this->assertEquals( '', $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
