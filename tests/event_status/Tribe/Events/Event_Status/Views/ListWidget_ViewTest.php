<?php

namespace Tribe\Events\Event_Status\Views;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class ListWidget_ViewTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'widgets/widget-events-list/event/title';

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
	 * @skip The meta tested is not being hydrated correctly by get_mock_event
	 */
	public function it_should_render_canceled_name_and_html() {
		$event = $this->get_mock_event( 'events/single/canceled_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
		] ) );
	}

	/**
	 * @test
	 * @skip The meta tested is not being hydrated correctly by get_mock_event
	 */
	public function it_should_render_postponed_name_and_html() {
		$event = $this->get_mock_event( 'events/single/postponed_status.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event' => $event,
		] ) );
	}
}
