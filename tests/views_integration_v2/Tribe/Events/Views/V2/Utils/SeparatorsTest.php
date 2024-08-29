<?php
namespace Tribe\Events\Views\V2\Utils;

use Tribe\Events\Test\Factories\Event;
use Tribe__Date_Utils as Dates;

class SeparatorsTest extends \Codeception\TestCase\WPTestCase {

	public function month_separator_provider() {
		$event_factory = new Event();
		$events[] = $should_have_month_one = $event_factory->create( [ 'when' => '2017-09-01' ] );
		$events[] = $should_have_month_two = $event_factory->create( [ 'when' => '2017-10-15' ] );
		$events[] = $should_not_have_month_one = $event_factory->create( [ 'when' => '2017-10-16' ] );
		$events[] = $should_not_have_month_two = $event_factory->create( [ 'when' => '2017-10-16' ] );
		$events[] = $should_have_month_three = $event_factory->create( [ 'when' => '2017-11-16' ] );
		$events[] = $should_not_have_month_three = $event_factory->create( [ 'when' => '2017-11-22' ] );

		return [
			'2017-09-01-should-have' => [
				$events,
				$should_have_month_two,
				true,
			],
			'2017-10-15-should-have' => [
				$events,
				$should_have_month_one,
				true,
			],
			'2017-10-16-should-not-have' => [
				$events,
				$should_not_have_month_one,
				false,
			],
			'2017-10-16(2)-should-not-have' => [
				$events,
				$should_not_have_month_two,
				false,
			],
			'2017-11-16-should-have' => [
				$events,
				$should_have_month_three,
				true,
			],
			'2017-11-22-should-not-have' => [
				$events,
				$should_not_have_month_three,
				false,
			],
		];
	}

	/**
	 * @test
	 * @dataProvider month_separator_provider
	 */
	public function it_should_have_month_separator_on_items( $events, $event, $expected ) {
		$should_have_month = Separators::should_have_month( $events, $event );
		$this->assertEquals( $expected, $should_have_month );
	}

	public function request_date_separator_data_provider() {
		$event_factory = new Event();

		$dates  = [
			'2019-10-30',
			'2019-10-31',
			'2019-11-01',
			'2019-11-03',
		];
		$events = array_map(static function($date)use($event_factory){
			return $event_factory->create(['when'=>$date.  ' 09:00:00']);
		},$dates);

		yield 'req_date_before_10_30_for_10_30_event' => [
			$events,
			$events[0],
			Dates::build_date_object( '2019-10-23' ),
			true,
		];

		yield 'req_date_before_10_30_for_10_31_event' => [
			$events,
			$events[1],
			Dates::build_date_object( '2019-10-23' ),
			false,
		];

		yield 'req_date_before_10_30_for_11_01_event' => [
			$events,
			$events[2],
			Dates::build_date_object( '2019-10-23' ),
			true,
		];

		yield 'req_date_before_10_30_for_11_03_event' => [
			$events,
			$events[3],
			Dates::build_date_object( '2019-10-23' ),
			false,
		];

		yield 'req_date_on_10_30_for_10_30_event' => [
			$events,
			$events[0],
			Dates::build_date_object( '2019-10-30' ),
			true,
		];

		yield 'req_date_on_10_30_for_10_31_event' => [
			$events,
			$events[1],
			Dates::build_date_object( '2019-10-30' ),
			false,
		];

		yield 'req_date_on_10_30_for_11_01_event' => [
			$events,
			$events[2],
			Dates::build_date_object( '2019-10-30' ),
			true,
		];

		yield 'req_date_on_10_30_for_11_03_event' => [
			$events,
			$events[3],
			Dates::build_date_object( '2019-10-30' ),
			false,
		];

		yield 'req_date_on_11_01_for_10_30_event' => [
			$events,
			$events[0],
			Dates::build_date_object( '2019-11-01' ),
			true,
		];

		yield 'req_date_on_11_01_for_10_31_event' => [
			$events,
			$events[1],
			Dates::build_date_object( '2019-11-01' ),
			false,
		];

		yield 'req_date_on_11_01_for_11_01_event' => [
			$events,
			$events[2],
			Dates::build_date_object( '2019-11-01' ),
			false,
		];

		yield 'req_date_on_11_01_for_11_03_event' => [
			$events,
			$events[3],
			Dates::build_date_object( '2019-11-01' ),
			false,
		];

		yield 'req_date_on_11_02_for_10_30_event' => [
			$events,
			$events[0],
			Dates::build_date_object( '2019-11-02' ),
			true,
		];

		yield 'req_date_on_11_02_for_10_31_event' => [
			$events,
			$events[1],
			Dates::build_date_object( '2019-11-02' ),
			false,
		];

		yield 'req_date_on_11_02_for_11_01_event' => [
			$events,
			$events[2],
			Dates::build_date_object( '2019-11-02' ),
			false,
		];

		yield 'req_date_on_11_02_for_11_03_event' => [
			$events,
			$events[3],
			Dates::build_date_object( '2019-11-02' ),
			false,
		];

		yield 'req_date_on_11_03_for_10_30_event' => [
			$events,
			$events[0],
			Dates::build_date_object( '2019-11-03' ),
			true,
		];

		yield 'req_date_on_11_03_for_10_31_event' => [
			$events,
			$events[1],
			Dates::build_date_object( '2019-11-03' ),
			false,
		];

		yield 'req_date_on_11_03_for_11_01_event' => [
			$events,
			$events[2],
			Dates::build_date_object( '2019-11-03' ),
			false,
		];

		yield 'req_date_on_11_03_for_11_03_event' => [
			$events,
			$events[3],
			Dates::build_date_object( '2019-11-03' ),
			false,
		];
	}

	/**
	 * It should correctly handle request dates on separator.
	 *
	 * Here we make sure the separator logic will correctly apply to event sets when we request a specific date.
	 *
	 * @test
	 * @dataProvider request_date_separator_data_provider
	 */
	public function should_correctly_handle_request_dates_on_separator($events, $event, $request_date, $expected) {
		$should_have_month = Separators::should_have_month( $events, $event, $request_date);
		$this->assertEquals( $should_have_month, $expected );
	}
}
