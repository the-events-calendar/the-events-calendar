<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class TitleTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/title';

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

	/**
	 * Test render with event with excerpt
	 */
	public function test_render_with_event_with_excerpt() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$event->post_content = $event->post_excerpt = 'Hello world!';
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with cost
	 */
	public function test_render_with_event_with_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$event->cost = '$10';
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with no featured image no excerpt no cost
	 */
	public function test_render_with_event_with_no_featured_image_no_excerpt_no_cost() {
		$event = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
