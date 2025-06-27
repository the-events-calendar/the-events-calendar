<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Month_SeparatorTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'list/month-separator';

	/**
	 * Test should render month separator
	 */
	public function test_should_render_month_separator() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_2 = $this->get_mock_event( 'events/single/2.json' );
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_1 ] ) );
	}

	/**
	 * Test should not render month separator
	 */
	public function test_should_not_render_month_separator() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_2 = $this->get_mock_event( 'events/single/2.json' );
		$events = [ $event_1, $event_2 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'events' => $events, 'event' => $event_2 ] ) );
	}
}
