<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/event/date';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event = $this->mock_event( 'events/featured/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
