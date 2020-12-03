<?php

namespace Tribe\Events\Views\V2\Partials\Widgets\Widget_Events_List\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'widgets/widget-events-list/event/date';

	/**
	 * Test render with event.
	 */
	public function test_render_with_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with all day event.
	 */
	public function test_render_with_all_day_event() {
		$event = $this->get_mock_event( 'events/all-day/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with multiday event.
	 */
	public function test_render_with_multiday_event() {
		$event = $this->get_mock_event( 'events/multiday/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with all day multiday event.
	 */
	public function test_render_with_all_day_multiday_event() {
		$event = $this->get_mock_event(
			'events/all-day/1.template.json',
			[
				'ID'         => 1234,
				'start_date' => '2019-06-20',
				'end_date'   => '2019-06-21',
			]
		);
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event = $this->get_mock_event( 'events/featured/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
