<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Updates\Events;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Date_InstantsTest extends WPTestCase {
	use With_Recurrence_Engine;

	private function event_id(): int {
		return tribe_events()->set_args( [ 'title' => 'Date instants', 'status' => 'publish', 'start_date' => '2030-01-05 09:00:00', 'end_date' => '2030-01-05 10:00:00', 'timezone' => 'America/New_York' ] )->create()->ID;
	}

	/** @test */
	public function should_preserve_equivalent_instants_seconds_and_identity_after_rederivation(): void {
		$id = $this->event_id();
		$service = tribe( Dates_Service::class );
		$periods = [ [ 'start' => new DateTimeImmutable( '2030-01-08T09:00:30Z' ), 'end' => new DateTimeImmutable( '2030-01-08T10:00:45Z' ) ] ];
		$this->assertTrue( $service->set_dates( $id, $periods ) );
		$before = $service->get_dates( $id );
		$this->assertSame( '2030-01-08 04:00:30', $before[1]['start'] );
		$this->assertSame( '2030-01-08 05:00:45', $before[1]['end'] );
		$rset = Event::find( $id, 'post_id' )->rset;
		$this->assertTrue( $service->set_dates( $id, [ [ 'start' => '2030-01-08T06:00:30-03:00', 'end' => '2030-01-08T05:00:45-05:00' ] ] ) );
		$this->assertSame( $before, $service->get_dates( $id ) );
		$this->assertTrue( tribe( Events::class )->update( $id ) );
		$this->assertSame( $rset, Event::find( $id, 'post_id' )->rset );
		$this->assertSame( $before, $service->get_dates( $id ) );
		$base = new DateTimeImmutable( '2030-01-05 09:00:00', new DateTimeZone( 'America/New_York' ) );
		$this->assertStringContainsString( '20300108T040030/20300108T050045', Dates::serialize( $base, $base->modify( '+1 hour' ), $periods ) );
	}

	/** @test */
	public function should_preserve_a_period_crossing_the_spring_dst_boundary(): void {
		$id = $this->event_id();
		$service = tribe( Dates_Service::class );
		$this->assertTrue( $service->set_dates( $id, [ [ 'start' => '2030-03-10T06:30:30Z', 'end' => '2030-03-10T07:30:45Z' ] ] ) );
		$before = $service->get_dates( $id );
		$this->assertSame( '2030-03-10 01:30:30', $before[1]['start'] );
		$this->assertSame( '2030-03-10 03:30:45', $before[1]['end'] );
		$this->assertTrue( tribe( Events::class )->update( $id ) );
		$this->assertSame( $before, $service->get_dates( $id ) );
	}

	/** @test */
	public function should_reject_unrepresentable_periods_before_changing_the_schedule(): void {
		$id = $this->event_id();
		$service = tribe( Dates_Service::class );
		$this->assertTrue( $service->set_dates( $id, [ [ 'start' => '2030-01-08 09:00:30', 'end' => '2030-01-08 10:00:30' ] ] ) );
		$before = $service->get_dates( $id );
		$meta = get_post_meta( $id, '_EventRecurrence', true );
		foreach ( [
			[ 'start' => '2030-01-09 09:00:00', 'end' => '2030-01-09 09:00:00' ],
			[ 'start' => '2030-01-09 10:00:00', 'end' => '2030-01-09 09:00:00' ],
			[ 'start' => '2030-01-09 09:00:00.123', 'end' => '2030-01-09 10:00:00' ],
			// The second repeated hour cannot be represented by the canonical date rule.
			[ 'start' => '2030-11-03T01:30:00-05:00', 'end' => '2030-11-03T02:30:00-05:00' ],
		] as $period ) {
			$this->assertFalse( $service->set_dates( $id, [ $period ] ), wp_json_encode( $period ) );
			$this->assertSame( $meta, get_post_meta( $id, '_EventRecurrence', true ) );
			$this->assertSame( $before, $service->get_dates( $id ) );
		}
	}
}
