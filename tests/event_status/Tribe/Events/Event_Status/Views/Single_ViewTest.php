<?php

namespace Tribe\Events\Event_Status\Views;

use Tribe\Events\Event_Status\Status_Labels;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Single_ViewTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'event-status/single/event-statuses';

	/**
	 * @test
	 */
	public function it_should_render_single_notice_with_no_name_or_reason() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
			'status_labels' => new Status_Labels(),
		] ) );
	}

	/**
	 * @test
	 */
	public function it_should_render_single_canceled_name_reason_and_html() {
		$event = $this->get_mock_event( 'events/single/canceled_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
			'status_labels' => new Status_Labels(),
		] ) );
	}

	/**
	 * @test
	 */
	public function it_should_render_single_postponed_name_reason_and_html() {
		$event = $this->get_mock_event( 'events/single/postponed_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
			'status_labels' => new Status_Labels(),
		] ) );
	}
}
