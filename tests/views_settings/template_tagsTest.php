<?php

use Codeception\TestCase\WPTestCase;
use Tribe\Test\Products\Traits\With_View_Context;

class template_tagsTest extends WPTestCase {
	use With_View_Context;

	public function event_properties_alterations() {
		yield 'set_one' => [
			[
				'event_date' => '2020-01',
				'options'    => [
					'timezone_string' => 'America/Los_Angeles',
					'start_of_week'   => '0',
				],
				'events'     => [
					'one' => [
						'title'      => 'First event',
						'start_date' => '2020-01-01 09:00:00',
						'duration'   => 4 * HOUR_IN_SECONDS,
					],
				],
			],
		];
	}

	/**
	 * It should correctly populate the event properties
	 *
	 * @test
	 * @dataProvider event_properties_alterations
	 */
	public function should_correctly_populate_the_event_properties( array $alterations = [] ) {
		$this->setup_context( $alterations );

		$event = tribe_get_event( $this->events['one'] );

		$expected = [
			'ID' => $this->events['one']->ID,
		];
		foreach ( $expected as $prop => $expected_value ) {
			$this->assertEquals( $expected_value, $event->{$prop} );
		}
	}
}
