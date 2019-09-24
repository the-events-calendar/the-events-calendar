<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DescriptionTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/event/description';

	/**
	 * Test render with description.
	 */
	public function test_render_with_description() {
		$content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
		$event = $this->get_mock_event( 'events/single/1.template.json', [ 'post_content' => $content ] );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render without description.
	 */
	public function test_render_without_description() {
		$event = $this->get_mock_event( 'events/single/1.json', [] );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

}
