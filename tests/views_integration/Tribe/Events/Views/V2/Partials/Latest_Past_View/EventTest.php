<?php

namespace Tribe\Events\Views\V2\Partials\Latest_Past_View;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;

class EventTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'latest-past/event';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event        = $this->get_mock_event( 'events/single/1.json' );
		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'request_date' => $request_date,
					'event'        => $event,
				]
			)
		);
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event        = $this->get_mock_event( 'events/featured/1.json' );
		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'request_date' => $request_date,
					'event'        => $event,
				]
			)
		);
	}

	/**
	 * should render and event with a featured image
	 *
	 * @test
	 */
	public function should_render_and_event_with_a_featured_image() {
		$event        = $this->mock_event( 'events/single/1.json' )->with_thumbnail()->get();
		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'request_date' => $request_date,
					'event'        => $event,
				]
			)
		);
	}
}
