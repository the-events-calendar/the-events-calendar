<?php
namespace Tribe\Events\Views\V2\Partials\List_View;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;

class EventTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'list/event';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$request_date = Dates::build_date_object( $event->dates->start_display->sub( new \DateInterval( 'P1D' ) ) );
		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'event'        => $event,
					'is_past'      => false,
					'request_date' => $request_date
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
					'event'        => $event,
					'is_past'      => false,
					'request_date' => $request_date
				]
			)
		);
	}
}
