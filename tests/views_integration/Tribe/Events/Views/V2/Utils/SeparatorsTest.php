<?php
namespace Tribe\Events\Views\V2\Utils;

use Tribe\Events\Test\Factories\Event;

class SeparatorsTest extends \Codeception\TestCase\WPTestCase {

	public function monthSeparatorProvider() {
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
	 * @dataProvider monthSeparatorProvider
	 */
	public function it_should_have_month_separator_on_items( $events, $event, $expected ) {
		$should_have_month = Separators::should_have_month( $events, $event );
		$this->assertEquals( $should_have_month, $expected );
	}
}
