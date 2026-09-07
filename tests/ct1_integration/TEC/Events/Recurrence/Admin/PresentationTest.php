<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class PresentationTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @test */
	public function should_distinguish_schedule_from_row_identity(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', $post->ID )->order_by( 'start_date', 'ASC' )->first();
		$presentation = new Presentation();
		$data = $presentation->get( $row->provisional_id );
		$this->assertSame( 'dates', $data['schedule'] );
		$this->assertTrue( $data['isOccurrence'] );
		$this->assertFalse( $data['locked'] );
		$this->assertSame( 2, $data['count'] );
		$this->assertSame( $post->ID, $data['eventId'] );
		$this->assertStringContainsString( 'post=' . $post->ID . '&action=edit', $data['parentEditLink'] );
		$this->assertStringContainsString( '2050', $data['start'] );
		$this->assertStringContainsString( 'UTC', $data['start'] );
		$this->assertFalse( $presentation->get( $post->ID )['isOccurrence'] );
	}

	/** @test */
	public function should_identify_rules_even_when_only_one_date_remains(): void {
		$post = $this->given_a_multi_date_event();
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=2" ] );
		$rows = iterator_to_array( Occurrence::where( 'post_id', $post->ID )->all(), false );
		$rows[1]->delete();
		$data = ( new Presentation() )->get( $rows[0]->provisional_id );
		$this->assertSame( 'rules', $data['schedule'] );
		$this->assertSame( 1, $data['count'] );
		$this->assertTrue( $data['locked'] );
	}

	/** @test */
	public function should_identify_single_events_and_unscheduled_drafts(): void {
		$post = tribe_events()->set_args( [ 'title' => 'Recurring in title only', 'status' => 'publish', 'start_date' => '2050-02-01 09:00:00', 'end_date' => '2050-02-01 10:00:00' ] )->create();
		$this->assertSame( 'single', ( new Presentation() )->get( $post->ID )['schedule'] );
		$draft = static::factory()->post->create( [ 'post_type' => 'tribe_events', 'post_status' => 'draft' ] );
		$data = ( new Presentation() )->get( $draft );
		$this->assertFalse( $data['isOccurrence'] );
		$this->assertSame( 'Not scheduled', $data['start'] );
	}
}
