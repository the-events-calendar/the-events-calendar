<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event\Tooltip;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DescriptionTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip/description';

	/**
	 * Test render with event without description
	 */
	public function test_render_with_event_without_description() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with description
	 */
	public function test_render_with_event_with_description() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$event->post_content = $event->post_excerpt = 'Hello world!';
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
