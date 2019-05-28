<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthEventMultidayTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/event-multiday', [ 'event' => (object) [ 'ID' => 0 ] ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__event-multiday' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__event-multiday" element'
		);


		$this->assertTrue(
			$html->find( '.tribe-events-calendar-month__event-multiday' )->children()->is( '.tribe-events-calendar-month__event-multiday-inner' ),
			'Multiday HTML needs to contain ".tribe-events-calendar-month__event-multiday-inner" element'
		);


		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__event-multiday-title' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__event-multiday-title" element'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$template = $this->template->template( 'month/event-multiday', [ 'event' => (object) [ 'ID' => 0 ] ] );
		$html = $this->document->html( $template );
		$html = $html->find( '.tribe-events-calendar-month__event-multiday' );

		/*
			@todo: If the event is featured we should check the a11y classes
		*/
	}
}