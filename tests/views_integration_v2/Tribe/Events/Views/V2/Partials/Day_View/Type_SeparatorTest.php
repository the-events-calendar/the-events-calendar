<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Type_SeparatorTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/type-separator';

	/**
	 * Test should render type separator
	 */
	public function test_should_render_type_separator() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_1->timeslot = 'multiday';
		$event_2 = $this->get_mock_event( 'events/single/2.json' );
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_1 ] ) );
	}

	/**
	 * Test should not render type separator
	 */
	public function test_should_not_render_type_separator() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_2 = $this->get_mock_event( 'events/single/2.json' );
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_2 ] ) );
	}
}
