<?php

namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthEventMultidayTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$thumbnail_id            = static::factory()->attachment->create_upload_object(
			codecept_data_dir( 'images/featured-image.jpg' )
		);
		$event_id                = static::factory()->event->create(
			[
				'meta_input' => [
					'_thumbnail_id' => $thumbnail_id,
				]
			]
		);
		$event                   = tribe_get_event( $event_id );
		$event->featured         = true;
		$event->multiday         = 2;
		$event->starts_this_week = true;
		$event->ends_this_week   = true;

		$template = $this->template->template(
			'month/calendar-body/day/multiday-events/multiday-event',
			[ 'event' => $event, 'day_date' => '2019-01-01', 'is_start_of_week' => true ]
		);
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__multiday-event' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__multiday-event" element'
		);


		$this->assertTrue(
			$html->find( '.tribe-events-calendar-month__multiday-event' )->children()->is(
				'.tribe-events-calendar-month__multiday-event-inner'
			),
			'Multiday HTML needs to contain ".tribe-events-calendar-month__multiday-event-inner" element'
		);


		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__multiday-event-title' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__multiday-event-title" element'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$thumbnail_id            = static::factory()->attachment->create_upload_object(
			codecept_data_dir( 'images/featured-image.jpg' )
		);
		$event_id                = static::factory()->event->create(
			[
				'meta_input' => [
					'_thumbnail_id' => $thumbnail_id,
				]
			]
		);
		$event                   = tribe_get_event( $event_id );
		$event->featured         = true;
		$event->multiday         = 2;
		$event->starts_this_week = true;
		$event->ends_this_week   = true;

		$template = $this->template->template(
			'month/calendar-body/day/multiday-events/multiday-event',
			[ 'event' => $event, 'day_date' => '2019-01-01', 'is_start_of_week' => true ]
		);
		$html     = $this->document->html( $template );
		$html     = $html->find( '.tribe-events-calendar-month__multiday-event' );
		$icon     = $html->find( '.tribe-events-calendar-month__multiday-event-featured-icon' );


		$this->markTestSkipped( 'The month multi-day event does not receive data yet' );

		$this->assertTrue(
			$icon->is( '[aria-label="Featured"]' ),
			'Month multiday featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$icon->is( '[title="Featured"]' ),
			'Month multiday featured icon needs to be title="Featured"'
		);
	}
}
