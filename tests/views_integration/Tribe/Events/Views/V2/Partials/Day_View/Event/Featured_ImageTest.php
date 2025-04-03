<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Featured_ImageTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/event/featured-image';

	/**
	 * Test render with event without featured image
	 */
	public function test_render_with_event_without_featured_image() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with featured image
	 */
	public function test_render_with_event_with_featured_image() {
		$event = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();
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
