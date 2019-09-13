<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Time_SeparatorTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/time-separator';

	/**
	 * Test should render time separator
	 */
	public function test_should_render_time_separator() {
		$event_1 = $this->mock_event( 'events/single/1.json' )->get();
		$event_2 = $this->mock_event( 'events/single/2.json' )->get();
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_1 ] ) );
	}

	/**
	 * Test should not render time separator
	 */
	public function test_should_not_render_time_separator() {
		$event_1 = $this->mock_event( 'events/single/1.json' )->get();
		$event_2 = $this->mock_event( 'events/single/2.json' )->get();
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_2 ] ) );
	}
}
