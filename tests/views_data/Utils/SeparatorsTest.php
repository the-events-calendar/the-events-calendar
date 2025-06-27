<?php

namespace Tribe\Events\Views\V2\Utils;

use Tribe\Events\Test\Factories\Event;
use Tribe__Date_Utils as Dates;

class SeparatorsTest extends \Codeception\TestCase\WPTestCase {

	protected Event $event_factory;

	protected function setUp(): void {
		parent::setUp();
		$this->event_factory = new Event();
	}

	/**
	 * @test
	 */
	public function it_should_have_month_separator_on_items() {
		$events = [];

		$event_dates = [
			'2017-09-01' => true,
			'2017-10-15' => true,
			'2017-10-16' => false,
			'2017-10-23' => false,
			'2017-11-16' => true,
			'2017-11-22' => false,
		];

		foreach ( $event_dates as $date => $expected ) {
			$events[] = $event = $this->event_factory->create( [ 'when' => $date ] );
			$actual   = Separators::should_have_month( $events, $event );

			// For debugging it will display failure message with the request date and the expected and actual results.
			$this->assertEquals(
				$expected,
				$actual,
				"Failed: Event on {$date} expected to return " . ( $expected ? 'true' : 'false' ) . " but got " . ( $actual ? 'true' : 'false' )
			);
		}
	}

	/**
	 * @test
	 */
	public function should_correctly_handle_request_dates_on_separator() {
		$dates  = [ '2019-10-30', '2019-10-31', '2019-11-01', '2019-11-03' ];
		$events = array_map( fn( $date ) => $this->event_factory->create( [ 'when' => $date . ' 09:00:00' ] ), $dates );

		$test_cases = [
			[ '2019-10-23', [ true, false, true, false ] ],
			[ '2019-10-30', [ true, false, true, false ] ],
			[ '2019-11-01', [ true, false, false, false ] ],
			[ '2019-11-02', [ true, false, false, false ] ],
			[ '2019-11-03', [ true, false, false, false ] ],
		];

		foreach ( $test_cases as [$request_date, $expected_results] ) {
			foreach ( $events as $index => $event ) {
				$actual = Separators::should_have_month( $events, $event, Dates::build_date_object( $request_date ) );

				// For debugging it will display failure message with the request date and the expected and actual results.
				$this->assertEquals(
					$expected_results[ $index ],
					$actual,
					"Failed: Request date {$request_date}, event on {$dates[$index]} expected " . ( $expected_results[ $index ] ? 'true' : 'false' ) . " but got " . ( $actual ? 'true' : 'false' )
				);
			}
		}
	}
}
