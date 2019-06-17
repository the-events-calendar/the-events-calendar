<?php
namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Events\Views\V2\TestHtmlCase;

class ListTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'list', [ 'events' => (object) [] ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list' )->count(),
			1,
			'List HTML needs to contain one ".tribe-events-calendar-list" element'
		);
	}
}
