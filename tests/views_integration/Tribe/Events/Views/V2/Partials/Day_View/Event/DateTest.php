<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Event;

use Tribe\Events\Views\V2\Hide_End_Time_Provider;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Settings_Manager;

class DateTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'day/event/date';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event hiding end time.
	 */
	public function test_render_with_event_hide_endtime() {
		// Partial template, the context is 'list' by default and no View is assigned.
		Tribe__Settings_Manager::set_option( 'remove_event_end_time', [ 'list' ] );
		tribe( Hide_End_Time_Provider::class )->hide_event_end_time();
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );

		// Remove option so flag doesn't bleed into other tests.
		Tribe__Settings_Manager::set_option( 'remove_event_end_time', [] );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event = $this->get_mock_event( 'events/featured/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
