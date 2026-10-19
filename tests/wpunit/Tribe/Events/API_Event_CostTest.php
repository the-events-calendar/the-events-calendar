<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__API as API;

class API_Event_CostTest extends WPTestCase {

	/**
	 * @test
	 */
	public function should_let_the_tickets_provide_the_cost_when_the_cost_field_is_blank(): void {
		$ticket_price = 52;
		add_filter(
			'tribe_events_event_costs',
			static function ( $costs ) use ( $ticket_price ) {
				return $costs ?: [ $ticket_price ];
			}
		);
		$event_id = tribe_events()->set_args( $this->event_args() )->create()->ID;

		$this->save_event_with_cost( $event_id, '' );

		$this->assertSame( [ (string) $ticket_price ], get_post_meta( $event_id, '_EventCost' ) );
	}

	/**
	 * @test
	 */
	public function should_keep_a_blank_cost_when_there_are_no_ticket_prices(): void {
		$event_id = tribe_events()->set_args( $this->event_args() )->create()->ID;

		$this->save_event_with_cost( $event_id, '' );

		$this->assertSame( [ '' ], get_post_meta( $event_id, '_EventCost' ) );
	}

	/**
	 * @test
	 */
	public function should_keep_the_cost_typed_in_when_there_are_no_ticket_prices(): void {
		$event_id = tribe_events()->set_args( $this->event_args() )->create()->ID;

		$this->save_event_with_cost( $event_id, '12' );

		$this->assertSame( [ '12' ], get_post_meta( $event_id, '_EventCost' ) );
	}

	private function event_args(): array {
		return [
			'title'      => 'Cost test event',
			'start_date' => 'tomorrow 9am',
			'timezone'   => 'America/New_York',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		];
	}

	private function save_event_with_cost( int $event_id, string $cost ): void {
		$start = tribe_get_start_date( $event_id, false, 'Y-m-d' );

		API::saveEventMeta(
			$event_id,
			[
				'EventStartDate'   => $start,
				'EventEndDate'     => $start,
				'EventStartHour'   => '09',
				'EventStartMinute' => '00',
				'EventEndHour'     => '11',
				'EventEndMinute'   => '00',
				'EventAllDay'      => '',
				'EventCost'        => $cost,
			],
			get_post( $event_id )
		);
	}
}
