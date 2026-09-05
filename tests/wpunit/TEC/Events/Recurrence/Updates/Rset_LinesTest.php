<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;

class Rset_LinesTest extends WPTestCase {
	private const RULE_RSET = "DTSTART;TZID=America/Sao_Paulo:20260901T150000\nDTEND;TZID=America/Sao_Paulo:20260901T160000\nRRULE:FREQ=WEEKLY;COUNT=5;BYDAY=TU";

	private function date( string $value ): DateTimeImmutable {
		return new DateTimeImmutable( $value, new DateTimeZone( 'America/Sao_Paulo' ) );
	}

	/**
	 * It should add an EXDATE line without timezone at the end
	 *
	 * @test
	 */
	public function should_add_an_exdate_line_without_timezone_at_the_end(): void {
		$rset = Rset_Lines::add_exdate( self::RULE_RSET, $this->date( '2026-09-15 15:00:00' ) );

		$this->assertEquals( self::RULE_RSET . "\nEXDATE:20260915T150000", $rset );
		$this->assertTrue( Rset_Lines::has_exdate( $rset, $this->date( '2026-09-15 15:00:00' ) ) );
		$this->assertFalse( Rset_Lines::has_exdate( $rset, $this->date( '2026-09-22 15:00:00' ) ) );
	}

	/**
	 * It should merge further exclusions into the existing EXDATE line
	 *
	 * @test
	 */
	public function should_merge_further_exclusions_into_the_existing_exdate_line(): void {
		$rset = Rset_Lines::add_exdate( self::RULE_RSET, $this->date( '2026-09-15 15:00:00' ) );
		$rset = Rset_Lines::add_exdate( $rset, $this->date( '2026-09-22 15:00:00' ) );
		// Adding the same exclusion twice is a no-op.
		$rset = Rset_Lines::add_exdate( $rset, $this->date( '2026-09-15 15:00:00' ) );

		$this->assertEquals( self::RULE_RSET . "\nEXDATE:20260915T150000,20260922T150000", $rset );
	}

	/**
	 * It should add an RDATE period in the Pro shape before the EXDATE lines
	 *
	 * @test
	 */
	public function should_add_an_rdate_period_before_the_exdate_lines(): void {
		$rset = Rset_Lines::add_exdate( self::RULE_RSET, $this->date( '2026-09-15 15:00:00' ) );
		$rset = Rset_Lines::add_rdate_period( $rset, $this->date( '2026-09-16 15:00:00' ), $this->date( '2026-09-16 16:00:00' ) );

		$this->assertEquals(
			self::RULE_RSET
			. "\nRDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20260916T150000/20260916T160000"
			. "\nEXDATE:20260915T150000",
			$rset
		);

		// Adding the same period twice is a no-op; a second one follows the first.
		$rset = Rset_Lines::add_rdate_period( $rset, $this->date( '2026-09-16 15:00:00' ), $this->date( '2026-09-16 16:00:00' ) );
		$rset = Rset_Lines::add_rdate_period( $rset, $this->date( '2026-10-31 15:00:00' ), $this->date( '2026-10-31 16:00:00' ) );

		$this->assertEquals(
			self::RULE_RSET
			. "\nRDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20260916T150000/20260916T160000"
			. "\nRDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261031T150000/20261031T160000"
			. "\nEXDATE:20260915T150000",
			$rset
		);
	}

	/**
	 * It should keep the existing lines order and tolerate Windows line endings
	 *
	 * @test
	 */
	public function should_keep_the_existing_lines_order_and_tolerate_windows_line_endings(): void {
		$rset = "DTSTART;TZID=UTC:20260901T150000\r\nRRULE:FREQ=DAILY;COUNT=3\r\nRDATE;VALUE=PERIOD:20260910T150000/PT3600S\r\nEXRULE:FREQ=WEEKLY;BYDAY=SU\r\n";

		$result = Rset_Lines::add_exdate( $rset, new DateTimeImmutable( '2026-09-02 15:00:00', new DateTimeZone( 'UTC' ) ) );

		$this->assertEquals(
			"DTSTART;TZID=UTC:20260901T150000\nRRULE:FREQ=DAILY;COUNT=3\nRDATE;VALUE=PERIOD:20260910T150000/PT3600S\nEXRULE:FREQ=WEEKLY;BYDAY=SU\nEXDATE:20260902T150000",
			$result
		);
	}
}
