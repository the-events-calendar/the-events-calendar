<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Multiday_Events;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Multiday_Event_SpacerTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body/day/multiday-events/multiday-event-spacer';

	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
