<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event\Tooltip;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Featured_ImageTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip/featured-image';

	/**
	 * Test render with featured image
	 */
	public function test_render_with_featured_image() {
		$event = $this->mock_event( 'events/single/1.template.json', [
			'start_date' => '2018-01-01',
			'end_date'   => '2018-01-01'
		] )->with_thumbnail()->get();

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with no featured image
	 */
	public function test_render_with_no_featured_image() {
		$event = $this->get_mock_event( 'events/single/1.template.json', [
			'start_date' => '2018-01-01',
			'end_date'   => '2018-01-01'
		] );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
