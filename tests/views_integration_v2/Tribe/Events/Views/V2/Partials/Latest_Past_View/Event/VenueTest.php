<?php

namespace Tribe\Events\Views\V2\Partials\Latest_Past_View\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class VenueTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'latest-past/event/venue';

	/**
	 * Test render with event without venue
	 */
	public function test_render_with_event_without_venue() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event with venue
	 */
	public function test_render_with_event_with_venue() {
		$event = $this->mock_event( 'events/single/1.json' )->with_venue( 'venues/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
