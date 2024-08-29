<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Multiday_Events\Multiday_Event\Hidden;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/multiday-events/multiday-event/hidden/date';

	/**
	 * Test render with multiday event
	 */
	public function test_render_with_multiday_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => false,
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with featured multiday event
	 */
	public function test_render_with_featured_multiday_event() {
		$event = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => false,
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

}