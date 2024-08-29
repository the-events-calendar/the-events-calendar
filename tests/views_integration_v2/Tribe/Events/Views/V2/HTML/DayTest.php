<?php
namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class DayTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$this->template->add_template_globals( [
			'view_slug' => 'day',
		] );
		$template = $this->template->template( 'day', [ 'events' => [] ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day' )->count(),
			1,
			'Day HTML needs to contain one ".tribe-events-calendar-day" element'
		);
	}
}
