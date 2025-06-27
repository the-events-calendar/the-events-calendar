<?php
namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class ListTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$this->template->add_template_globals( [
			'view_slug' => 'list',
		] );
		$this->template->set_values(
			[
				'show_now'                => true,
				'now_label'               => 'Now',
				'now_label_mobile'        => 'Now',
				'show_end'                => false,
				'selected_start_datetime' => '2019-01-01',
				'datepicker_date'         => '2019-01-01',
			],
			false
		);
		$template = $this->template->template( 'list', [ 'events' => [] ] );
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list' )->count(),
			1,
			'List HTML needs to contain one ".tribe-events-calendar-list" element'
		);
	}
}
