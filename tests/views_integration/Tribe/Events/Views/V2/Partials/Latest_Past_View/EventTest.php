<?php

namespace Tribe\Events\Views\V2\Partials\Latest_Past_View;

use Tribe\Events\Views\V2\Hide_End_Time_Provider;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;
use Tribe__Settings_Manager;

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
	 * Tests our hide end time setting for the past event view.
	 *
	 * @test
	 */
	public function test_render_with_event_and_hide_end_time() {
		// Partial template, the context is 'list' by default and no View is assigned.
		Tribe__Settings_Manager::set_option( 'remove_event_end_time', [ 'list' ] );
		tribe( Hide_End_Time_Provider::class )->hide_event_end_time();

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

		// Remove option so flag doesn't bleed into other tests.
		Tribe__Settings_Manager::set_option( 'remove_event_end_time', [] );
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
