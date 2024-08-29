<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Featured_ImageTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/featured-image';

	/**
	 * Test render with normal event without featured image
	 */
	public function test_render_with_normal_event_without_featured_image() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with normal event with featured image
	 */
	public function test_render_with_normal_event_with_featured_image() {
		$event = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event without featured image
	 */
	public function test_render_with_featured_event_without_featured_image() {
		$event = $this->get_mock_event( 'events/featured/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event with featured image
	 */
	public function test_render_with_featured_event_with_featured_image() {
		$event = $this->mock_event( 'events/featured/1.json' )->with_thumbnail()->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render event with featured image with alt text.
	 */
	public function test_render_with_event_with_featured_image_with_alt() {
		$event = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();
		$event->thumbnail->alt = "Featured Image";
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render event with featured image without alt text.
	 */
	public function test_render_with_event_with_featured_image_without_alt() {
		$event = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();
		$event->thumbnail->alt = "";
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
