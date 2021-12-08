<?php

namespace Tribe\Events\Event_Status\Views\Month;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Tooltip_ViewTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip';

	/**
	 * @test
	 */
	public function it_should_render_standard_title() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
		] ) );
	}

	/**
	 * @test
	 */
	public function it_should_render_canceled_name_and_html() {
		$event = $this->get_mock_event( 'events/single/canceled_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
		] ) );
	}

	/**
	 * @test
	 */
	public function it_should_render_postponed_name_and_html() {
		$event = $this->get_mock_event( 'events/single/postponed_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
		] ) );
	}
}
