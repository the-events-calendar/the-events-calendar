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

	private function date_rule( array $custom_overrides = [] ): array {
		return [
			'type'   => 'Custom',
			'custom' => array_merge(
				[
					'interval'   => 1,
					'type'       => 'Date',
					'date'       => [ 'date' => '2026-11-12' ],
					'same-time'  => 'no',
					'start-time' => '9:00am',
					'end-time'   => '10:00am',
					'end-day'    => 'same-day',
				],
				$custom_overrides
			),
		];
	}

	/**
	 * It should reject every non date rule shape
	 *
	 * @test
	 */
	public function should_reject_every_non_date_rule_shape(): void {
		// A rule that is not Custom.
		$rule           = $this->date_rule();
		$rule['type']   = 'Weekly';
		$this->assertFalse( Date_Rules::is_dates_only_meta( [ 'rules' => [ $rule ] ] ) );

		// A Custom rule that is not a Date one.
		$this->assertFalse(
			Date_Rules::is_dates_only_meta( [ 'rules' => [ $this->date_rule( [ 'type' => 'Week' ] ) ] ] )
		);

		// A Date rule missing its date.
		$rule = $this->date_rule();
		unset( $rule['custom']['date'] );
		$this->assertFalse( Date_Rules::is_dates_only_meta( [ 'rules' => [ $rule ] ] ) );

		// A mix of a Date rule and a pattern rule.
		$this->assertFalse(
			Date_Rules::is_dates_only_meta(
				[
					'rules' => [
						$this->date_rule(),
						$this->date_rule( [ 'type' => 'Week' ] ),
					],
				]
			)
		);
	}

	/**
	 * It should apply the event times to same time rules
	 *
	 * @test
	 */
	public function should_apply_the_event_times_to_same_time_rules(): void {
		$timezone    = new DateTimeZone( 'UTC' );
		$event_start = new DateTimeImmutable( '2026-11-05 09:30:15', $timezone );
		$event_end   = new DateTimeImmutable( '2026-11-05 11:00:15', $timezone );

		$periods = Date_Rules::to_periods(
			[ 'rules' => [ $this->date_rule( [ 'same-time' => 'yes' ] ) ] ],
			$event_start,
			$event_end,
			$timezone
		);

		$this->assertIsArray( $periods );
		$this->assertCount( 1, $periods );
		$this->assertEquals( '2026-11-12 09:30:15', $periods[0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-12 11:00:15', $periods[0]['end']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should support multi day date rules
	 *
	 * @test
	 */
	public function should_support_multi_day_date_rules(): void {
		$timezone    = new DateTimeZone( 'UTC' );
		$event_start = new DateTimeImmutable( '2026-11-05 09:00:00', $timezone );
		$event_end   = new DateTimeImmutable( '2026-11-05 10:00:00', $timezone );

		$periods = Date_Rules::to_periods(
			[
				'rules' => [
					$this->date_rule(
						[
							'start-time' => '8:00pm',
							'end-time'   => '2:00am',
							'end-day'    => 1,
						]
					),
				],
			],
			$event_start,
			$event_end,
			$timezone
		);

		$this->assertIsArray( $periods );
		$this->assertEquals( '2026-11-12 20:00:00', $periods[0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-13 02:00:00', $periods[0]['end']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should return null for an invalid rule date
	 *
	 * @test
	 */
	public function should_return_null_for_an_invalid_rule_date(): void {
		$timezone    = new DateTimeZone( 'UTC' );
		$event_start = new DateTimeImmutable( '2026-11-05 09:00:00', $timezone );
		$event_end   = new DateTimeImmutable( '2026-11-05 10:00:00', $timezone );

		$this->assertNull(
			Date_Rules::to_periods(
				[ 'rules' => [ $this->date_rule( [ 'date' => [ 'date' => 'not-a-date' ] ] ) ] ],
				$event_start,
				$event_end,
				$timezone
			)
		);
	}
}
