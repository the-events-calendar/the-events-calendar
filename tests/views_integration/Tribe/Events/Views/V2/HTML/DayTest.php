<?php
namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class DayTest extends HtmlTestCase {

	/**
	 * Returns a "safe" View to use in HTML partial testing.
	 *
	 * @return View_Interface A View instance safe to use in partial HTML testing.
	 */
	protected function make_view_instance() {
		return View::make( 'day' );
	}

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'day', [ 'events' => [] ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day' )->count(),
			1,
			'Day HTML needs to contain one ".tribe-events-calendar-day" element'
		);
	}
}
