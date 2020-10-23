<?php

namespace Tribe\Events\Views\V2\Partials\Widgets\Widget_Events_List\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Date_TagTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'widgets/widget-events-list/event/date-tag';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event            = $this->get_mock_event( 'events/single/1.json' );
		$event_start_date = $event->dates->start_display;
		$request_date     = $event_start_date->sub( new \DateInterval( 'P1D' ) );
		$context          = [
			'event'        => $event,
			'is_past'      => false,
			'request_date' => $request_date
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	public function request_dates() {
		return [
			'one_week_before' => ['-P1W' ,false],
			'on_same_day' => [false,false],
			'one_week_after' => ['P1W',false],
			'on_same_day_past' => [false,true],
			'one_week_after_past' => ['P1W',true],
		];
	}

	/**
	 * @dataProvider request_dates
	 */
	public function test_render_with_req_dates_and_multi_day_event_combination( $raw_interval_spec, $is_past ) {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 20 )->get();
		$event_start_date = $event->dates->start_display;
		$request_date = $event_start_date;
		if ( false !== $raw_interval_spec ) {
			$sub           = strpos( $raw_interval_spec, '-' ) === 0;
			$interval_spec = $sub ? substr( $raw_interval_spec, 1 ) : $raw_interval_spec;
			$date_interval = new \DateInterval( $interval_spec );
			$request_date  = $sub
				? $event_start_date->sub( $date_interval )
				: $event_start_date->add( $date_interval );
		}

		$context = [
			'event'        => $event,
			'is_past'      => $is_past,
			'request_date' => $request_date
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}
}
