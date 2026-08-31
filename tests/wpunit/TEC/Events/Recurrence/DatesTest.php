<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;

class DatesTest extends WPTestCase {
	/**
	 * It should serialize to the Events Calendar Pro derived shape
	 *
	 * @test
	 */
	public function should_serialize_to_the_pro_derived_shape(): void {
		$tz = new DateTimeZone( 'America/Sao_Paulo' );

		$rset = Dates::serialize(
			new DateTimeImmutable( '2026-11-05 09:00:00', $tz ),
			new DateTimeImmutable( '2026-11-05 10:00:00', $tz ),
			[
				[
					'start' => new DateTimeImmutable( '2026-11-12 09:00:00', $tz ),
					'end'   => new DateTimeImmutable( '2026-11-12 10:00:00', $tz ),
				],
				[
					'start' => new DateTimeImmutable( '2026-11-20 14:00:00', $tz ),
					'end'   => new DateTimeImmutable( '2026-11-20 15:30:00', $tz ),
				],
			]
		);

		$expected = "DTSTART;TZID=America/Sao_Paulo:20261105T090000\n"
					. "RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261112T090000/20261112T100000\n"
					. "RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261120T140000/20261120T153000\n"
					. 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261105T090000/20261105T100000';

		$this->assertEquals( $expected, $rset );
	}

	/**
	 * It should round trip a serialized RSET
	 *
	 * @test
	 */
	public function should_round_trip_a_serialized_rset(): void {
		$tz = new DateTimeZone( 'America/Sao_Paulo' );

		$rset = Dates::serialize(
			new DateTimeImmutable( '2026-11-05 09:00:00', $tz ),
			new DateTimeImmutable( '2026-11-05 10:00:00', $tz ),
			[
				[
					'start' => new DateTimeImmutable( '2026-11-12 09:00:00', $tz ),
					'end'   => new DateTimeImmutable( '2026-11-12 10:00:00', $tz ),
				],
			]
		);

		$parsed = Dates::parse( $rset );

		$this->assertIsArray( $parsed );
		$this->assertEquals( 'America/Sao_Paulo', $parsed['timezone']->getName() );
		$this->assertCount( 2, $parsed['periods'] );
		$this->assertEquals( '2026-11-05 09:00:00', $parsed['periods'][0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-05 10:00:00', $parsed['periods'][0]['end']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-12 09:00:00', $parsed['periods'][1]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-12 10:00:00', $parsed['periods'][1]['end']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should not treat rule based RSETs as dates only
	 *
	 * @test
	 */
	public function should_not_treat_rule_based_rsets_as_dates_only(): void {
		$rules_rset = "DTSTART;TZID=UTC:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=5";

		$this->assertFalse( Dates::is_dates_only( $rules_rset ) );
		$this->assertNull( Dates::parse( $rules_rset ) );

		$exdate_rset = "DTSTART;TZID=UTC:20261105T090000\n"
					. "RDATE;TZID=UTC;VALUE=PERIOD:20261112T090000/20261112T100000\n"
					. 'EXDATE;TZID=UTC:20261112T090000';

		$this->assertFalse( Dates::is_dates_only( $exdate_rset ) );
		$this->assertNull( Dates::parse( $exdate_rset ) );

		$this->assertFalse( Dates::is_dates_only( '' ) );
		$this->assertNull( Dates::parse( '' ) );
	}

	/**
	 * It should parse period durations date times and dates
	 *
	 * @test
	 */
	public function should_parse_period_durations_date_times_and_dates(): void {
		$rset = "DTSTART;TZID=UTC:20261105T090000\n"
				. "RDATE;TZID=UTC;VALUE=PERIOD:20261112T090000/PT2H\n"
				. "RDATE;TZID=UTC;VALUE=DATE-TIME:20261113T090000\n"
				. 'RDATE;TZID=UTC;VALUE=DATE:20261114';

		$parsed = Dates::parse( $rset, 3600 );

		$this->assertIsArray( $parsed );
		// The DTSTART models the first Occurrence plus the three RDATEs.
		$this->assertCount( 4, $parsed['periods'] );

		// DTSTART period gets the default duration.
		$this->assertEquals( '2026-11-05 09:00:00', $parsed['periods'][0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-05 10:00:00', $parsed['periods'][0]['end']->format( 'Y-m-d H:i:s' ) );

		// PERIOD with a duration part.
		$this->assertEquals( '2026-11-12 11:00:00', $parsed['periods'][1]['end']->format( 'Y-m-d H:i:s' ) );

		// DATE-TIME with the default duration.
		$this->assertEquals( '2026-11-13 10:00:00', $parsed['periods'][2]['end']->format( 'Y-m-d H:i:s' ) );

		// DATE with the DTSTART time and default duration.
		$this->assertEquals( '2026-11-14 09:00:00', $parsed['periods'][3]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-14 10:00:00', $parsed['periods'][3]['end']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should parse comma separated and UTC values
	 *
	 * @test
	 */
	public function should_parse_comma_separated_and_utc_values(): void {
		$rset = "DTSTART;TZID=UTC:20261105T090000\n"
				. 'RDATE;TZID=UTC;VALUE=PERIOD:20261112T090000/20261112T100000,20261119T090000/20261119T100000';

		$parsed = Dates::parse( $rset );

		$this->assertIsArray( $parsed );
		$this->assertCount( 3, $parsed['periods'] );

		// A trailing `Z` marks a UTC value, converted into the RSET timezone.
		$utc_rset = "DTSTART;TZID=America/Sao_Paulo:20261105T090000\n"
					. 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261112T120000Z/20261112T130000Z';

		$parsed = Dates::parse( $utc_rset );

		$this->assertIsArray( $parsed );
		// America/Sao_Paulo is UTC-3: 12:00 UTC is 09:00 local.
		$this->assertEquals( '2026-11-12 09:00:00', $parsed['periods'][1]['start']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should dedupe periods by start and sort them ascending
	 *
	 * @test
	 */
	public function should_dedupe_periods_by_start_and_sort_them_ascending(): void {
		$rset = "DTSTART;TZID=UTC:20261120T090000\n"
				. "RDATE;TZID=UTC;VALUE=PERIOD:20261105T090000/20261105T100000\n"
				. "RDATE;TZID=UTC;VALUE=PERIOD:20261120T090000/20261120T100000\n"
				. 'RDATE;TZID=UTC;VALUE=PERIOD:20261105T090000/20261105T100000';

		$parsed = Dates::parse( $rset );

		$this->assertIsArray( $parsed );
		$this->assertCount( 2, $parsed['periods'] );
		$this->assertEquals( '2026-11-05 09:00:00', $parsed['periods'][0]['start']->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2026-11-20 09:00:00', $parsed['periods'][1]['start']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should keep distinct instants sharing a local wall time in the repeated DST hour
	 *
	 * @test
	 */
	public function should_keep_distinct_instants_sharing_a_local_wall_time_in_the_repeated_dst_hour(): void {
		/*
		 * America/New_York falls back on 2026-11-01: 05:30Z is 01:30 EDT and 06:30Z is
		 * 01:30 EST. Both are distinct instants sharing the same local wall time.
		 */
		$rset = "DTSTART;TZID=America/New_York:20261101T003000\n"
				. "RDATE;VALUE=DATE-TIME:20261101T053000Z\n"
				. 'RDATE;VALUE=DATE-TIME:20261101T063000Z';

		$parsed = Dates::parse( $rset );

		$this->assertIsArray( $parsed );
		$this->assertCount( 3, $parsed['periods'] );

		$utc_starts = array_map(
			static function ( array $period ): string {
				return $period['start']->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
			},
			$parsed['periods']
		);

		$this->assertEquals(
			[ '2026-11-01 04:30:00', '2026-11-01 05:30:00', '2026-11-01 06:30:00' ],
			$utc_starts
		);
	}
}
