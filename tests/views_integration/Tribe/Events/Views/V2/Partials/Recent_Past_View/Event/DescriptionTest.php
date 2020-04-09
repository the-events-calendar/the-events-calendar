<?php

namespace Tribe\Events\Views\V2\Partials\Recent_Past_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe\Utils\Lazy_String;

class DescriptionTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'recent-past/event/description';

	/**
	 * Test render with event without description
	 */
	public function test_render_with_event_without_description() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with description
	 */
	public function test_render_with_event_with_description() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$event->excerpt = new Lazy_String(
			static function () {
				return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
			}
		);
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
