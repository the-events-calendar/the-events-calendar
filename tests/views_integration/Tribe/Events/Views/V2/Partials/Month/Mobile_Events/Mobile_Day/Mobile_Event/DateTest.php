<?php
namespace Tribe\Events\Views\V2\Partials\Month\Mobile_Events\Mobile_Day\Mobile_Event;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/mobile-events/mobile-day/mobile-event/date';


	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => tribe_events()->first() ] ) );
	}
}
