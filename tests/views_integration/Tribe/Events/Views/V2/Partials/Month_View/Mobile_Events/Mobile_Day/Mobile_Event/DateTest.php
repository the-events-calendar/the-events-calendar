<?php
namespace Tribe\Events\Views\V2\Partials\Month_View\Mobile_Events\Mobile_Day\Mobile_Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/mobile-events/mobile-day/mobile-event/date';

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
		$event = $this->mock_event( 'events/single/1.json' )->is_featured()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with all day event
	 */
	public function test_render_with_all_day_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_all_day()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured all day event
	 */
	public function test_render_with_featured_all_day_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_featured()->is_all_day()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
