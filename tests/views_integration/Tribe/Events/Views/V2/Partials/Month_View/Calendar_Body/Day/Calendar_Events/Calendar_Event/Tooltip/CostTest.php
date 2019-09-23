<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event\Tooltip;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class CostTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip/cost';

	/**
	 * Test render with cost
	 */
	public function test_render_with_cost() {

		$event = $this->get_mock_event( 'events/single/1.template.json', [ 'id' => 23, 'start_date' => '2018-01-01', 'end_date' => '2018-01-01' ] );
		$event->cost = '$10';

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	public function test_render_without_cost() {

		$event = $this->get_mock_event( 'events/single/1.template.json', [ 'id' => 23, 'start_date' => '2018-01-01', 'end_date' => '2018-01-01' ] );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}

}

