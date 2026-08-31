<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;

class Date_RulesTest extends WPTestCase {
	private function tz(): DateTimeZone {
		return new DateTimeZone( 'America/Sao_Paulo' );
	}

	/**
	 * It should write the legacy date rule shape Events Calendar Pro reads
	 *
	 * @test
	 */
	public function should_write_the_legacy_date_rule_shape_pro_reads(): void {
		$meta = Date_Rules::to_meta(
			[
				[
					'start' => new DateTimeImmutable( '2026-11-12 09:00:00', $this->tz() ),
					'end'   => new DateTimeImmutable( '2026-11-12 10:00:00', $this->tz() ),
				],
				[
					'start' => new DateTimeImmutable( '2026-11-20 14:00:00', $this->tz() ),
					'end'   => new DateTimeImmutable( '2026-11-21 15:30:00', $this->tz() ),
				],
			],
			new DateTimeImmutable( '2026-11-05 09:00:00', $this->tz() ),
			new DateTimeImmutable( '2026-11-05 10:00:00', $this->tz() )
		);

		$this->assertCount( 2, $meta['rules'] );
		$this->assertEquals( [], $meta['exclusions'] );

		$first = $meta['rules'][0];
		$this->assertEquals( 'Custom', $first['type'] );
		$this->assertEquals( 'Date', $first['custom']['type'] );
		$this->assertEquals( '2026-11-12', $first['custom']['date']['date'] );
		$this->assertEquals( 'no', $first['custom']['same-time'] );
		$this->assertEquals( '9:00am', $first['custom']['start-time'] );
		$this->assertEquals( '10:00am', $first['custom']['end-time'] );
		$this->assertEquals( 'same-day', $first['custom']['end-day'] );
		$this->assertEquals( '2026-11-05 09:00:00', $first['EventStartDate'] );
		$this->assertEquals( '2026-11-05 10:00:00', $first['EventEndDate'] );

		// The second date crosses into the next day.
		$second = $meta['rules'][1];
		$this->assertEquals( 1, $second['custom']['end-day'] );
		$this->assertEquals( '2:00pm', $second['custom']['start-time'] );
		$this->assertEquals( '3:30pm', $second['custom']['end-time'] );
	}

	/**
	 * It should round trip through the legacy meta shape
	 *
	 * @test
	 */
	public function should_round_trip_through_the_legacy_meta_shape(): void {
		$event_start = new DateTimeImmutable( '2026-11-05 09:00:00', $this->tz() );
		$event_end   = new DateTimeImmutable( '2026-11-05 10:00:00', $this->tz() );

		$periods = [
			[
				'start' => new DateTimeImmutable( '2026-11-12 09:00:00', $this->tz() ),
				'end'   => new DateTimeImmutable( '2026-11-12 10:00:00', $this->tz() ),
			],
			[
				'start' => new DateTimeImmutable( '2026-11-20 14:00:00', $this->tz() ),
				'end'   => new DateTimeImmutable( '2026-11-21 15:30:00', $this->tz() ),
			],
		];

		$meta = Date_Rules::to_meta( $periods, $event_start, $event_end );

		$this->assertTrue( Date_Rules::is_dates_only_meta( $meta ) );

		$round_tripped = Date_Rules::to_periods( $meta, $event_start, $event_end, $this->tz() );

		$this->assertIsArray( $round_tripped );
		$this->assertCount( 2, $round_tripped );

		foreach ( $periods as $i => $period ) {
			$this->assertEquals( $period['start']->format( 'Y-m-d H:i' ), $round_tripped[ $i ]['start']->format( 'Y-m-d H:i' ) );
			$this->assertEquals( $period['end']->format( 'Y-m-d H:i' ), $round_tripped[ $i ]['end']->format( 'Y-m-d H:i' ) );
		}
	}

	/**
	 * It should parse same time date rules with the event times
	 *
	 * @test
	 */
	public function should_parse_same_time_date_rules_with_the_event_times(): void {
		$event_start = new DateTimeImmutable( '2026-11-05 09:00:00', $this->tz() );
		$event_end   = new DateTimeImmutable( '2026-11-05 10:30:00', $this->tz() );

		$meta = [
			'rules' => [
				[
					'type'   => 'Custom',
					'custom' => [
						'interval'  => 1,
						'type'      => 'Date',
						'date'      => [ 'date' => '2026-11-12' ],
						'same-time' => 'yes',
					],
				],
			],
		];

		$periods = Date_Rules::to_periods( $meta, $event_start, $event_end, $this->tz() );

		$this->assertIsArray( $periods );
		$this->assertEquals( '2026-11-12 09:00:00', $periods[0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-12 10:30:00', $periods[0]['end']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should reject rule based and excluded meta
	 *
	 * @test
	 */
	public function should_reject_rule_based_and_excluded_meta(): void {
		$weekly = [
			'rules' => [
				[
					'type' => 'Weekly',
				],
			],
		];

		$this->assertFalse( Date_Rules::is_dates_only_meta( $weekly ) );
		$this->assertNull( Date_Rules::to_periods( $weekly, new DateTimeImmutable(), new DateTimeImmutable(), $this->tz() ) );

		$with_exclusions = [
			'rules'      => [
				[
					'type'   => 'Custom',
					'custom' => [
						'type' => 'Date',
						'date' => [ 'date' => '2026-11-12' ],
					],
				],
			],
			'exclusions' => [
				[ 'type' => 'Weekly' ],
			],
		];

		$this->assertFalse( Date_Rules::is_dates_only_meta( $with_exclusions ) );

		$this->assertFalse( Date_Rules::is_dates_only_meta( '' ) );
		$this->assertFalse( Date_Rules::is_dates_only_meta( [ 'rules' => [] ] ) );
	}
}
