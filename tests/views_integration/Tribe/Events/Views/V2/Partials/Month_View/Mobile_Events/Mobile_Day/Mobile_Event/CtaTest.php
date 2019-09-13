<?php
namespace Tribe\Events\Views\V2\Partials\Month_View\Mobile_Events\Mobile_Day\Mobile_Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class CtaTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/mobile-events/mobile-day/mobile-event/cta';

	/**
	 * Test render with normal event without cost
	 */
	public function test_render_with_normal_event_without_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event without cost
	 */
	public function test_render_with_featured_event_without_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->is_featured()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with normal event with cost
	 */
	public function test_render_with_normal_event_with_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$event->cost = '$10';
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event with cost
	 */
	public function test_render_with_featured_event_with_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->is_featured()->get();
		$event->cost = '$10';
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
