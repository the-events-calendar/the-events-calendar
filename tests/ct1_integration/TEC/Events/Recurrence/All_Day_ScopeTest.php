<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Recurrence\Updates\Single_Occurrence_Update;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class All_Day_ScopeTest extends WPTestCase {
	use With_Recurrence_Engine;

	public function all_day_modes(): array {
		return [ 'timed' => [ false ], 'all day' => [ true ] ];
	}

	/**
	 * @test
	 * @dataProvider all_day_modes
	 */
	public function should_use_the_event_flag_for_all_occurrence_rendering_and_exports( bool $all_day ): void {
		$event = $this->given_a_multi_date_event( [ [ 'start' => '2050-01-12 00:00:00', 'end' => '2050-01-12 23:59:59' ] ] );
		wp_update_post( [ 'ID' => $event->ID, 'meta_input' => [ '_EventAllDay' => $all_day ? 'yes' : 'no' ] ] );
		$row = Occurrence::where( 'post_id', '=', $event->ID )->order_by( 'start_date', 'DESC' )->first();
		tribe( Provisional_Post::class )->hydrate_caches( [ $row->provisional_id ] );
		$this->assertSame( $all_day, tribe_event_is_all_day( $row->provisional_id ) );
		$ical = tribe( \Tribe__Events__iCal::class )->get_ical_output_for_an_event( get_post( $row->provisional_id ), \Tribe__Events__Main::instance() );
		$this->assertSame( $all_day, str_contains( $ical['DTSTART'], 'VALUE=DATE:' ), var_export( [ get_post_meta( $event->ID, '_EventAllDay', true ), get_post_meta( $row->provisional_id, '_EventAllDay', true ), $ical['DTSTART'] ], true ) );
		$this->assertSame( $all_day, str_contains( $ical['DTEND'], 'VALUE=DATE:' ) );
	}

	/** @test */
	public function should_refuse_an_occurrence_only_all_day_change(): void {
		$event = $this->given_a_multi_date_event();
		$row   = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$updates = tribe( Single_Occurrence_Update::class );
		$this->assertTrue( $updates->buffer_update( null, $row->provisional_id, '_EventAllDay', 'yes' ) );
		$this->assertFalse( $updates->apply_pending( $row->provisional_id ) );
		$this->assertFalse( tribe_event_is_all_day( $event->ID ) );
	}
}
