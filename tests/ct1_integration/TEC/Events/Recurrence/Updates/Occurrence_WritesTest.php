<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Error;
use WP_REST_Request;

class Occurrence_WritesTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @after */
	public function restore_ownership(): void {
		remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
	}

	/** @test */
	public function should_refuse_a_classic_occurrence_submission_without_replaying_it_on_the_parent(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event   = $this->given_a_multi_date_event();
		$row     = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$request = $_REQUEST;
		$method  = $_SERVER['REQUEST_METHOD'] ?? null;
		$_REQUEST['post_ID']       = $row->provisional_id;
		$_REQUEST['action']        = 'editpost';
		$_SERVER['REQUEST_METHOD'] = 'POST';
		try {
			$this->expectException( \WPDieException::class );
			tribe( Occurrence_Writes::class )->classic_request();
		} finally {
			$_REQUEST = $request;
			if ( null === $method ) {
				unset( $_SERVER['REQUEST_METHOD'] );
			} else {
				$_SERVER['REQUEST_METHOD'] = $method;
			}
		}
	}

	/** @test */
	public function should_edit_shared_fields_on_the_parent_and_preserve_selected_date_moves(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row   = Occurrence::where( 'post_id', '=', $event->ID )->order_by( 'start_date', 'DESC' )->first();
		$id    = $row->provisional_id;
		$this->assertSame( get_edit_post_link( $event->ID ), get_edit_post_link( $id ) );
		$before = get_post( $event->ID )->post_title;
		$this->assertInstanceOf( WP_Error::class, wp_update_post( [ 'ID' => $id, 'post_title' => 'Wrong scope' ], true ) );
		$this->assertFalse( wp_trash_post( $id ) );
		$this->assertFalse( wp_delete_post( $id, true ) );
		$this->assertSame( $before, get_post( $event->ID )->post_title );
		$this->assertNotNull( Occurrence::find( $row->occurrence_id ) );

		$this->assertSame( $event->ID, wp_update_post( [ 'ID' => $event->ID, 'post_title' => 'Shared content', 'post_content' => 'Shared description', 'post_status' => 'draft' ], true ) );
		clean_post_cache( $event->ID );
		$this->assertSame( 'Shared content', get_post( $event->ID )->post_title );
		$this->assertSame( 'Shared description', get_post( $event->ID )->post_content );
		$this->assertSame( 'draft', get_post_status( $event->ID ) );
		$tz = new DateTimeZone( get_post_meta( $event->ID, '_EventTimezone', true ) );
		$this->assertTrue( tribe( Single_Occurrence_Update::class )->apply( $id, new DateTimeImmutable( '2050-02-01 09:00:00', $tz ), new DateTimeImmutable( '2050-02-01 10:00:00', $tz ) ) );
		$this->assertSame( '2050-02-01 09:00:00', Occurrence::find( $row->occurrence_id )->start_date );
	}

	/** @test */
	public function should_reject_rest_occurrence_mutations_and_leave_parent_and_pro_requests_alone(): void {
		$event = $this->given_a_multi_date_event();
		$row   = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$guard = tribe( Occurrence_Writes::class );
		foreach ( [ 'POST', 'PUT', 'PATCH', 'DELETE' ] as $method ) {
			$request = new WP_REST_Request( $method, '/wp/v2/tribe_events/' . $row->provisional_id );
			$error   = $guard->rest_request( null, [], $request );
			$this->assertInstanceOf( WP_Error::class, $error );
			$this->assertSame( 'tec_occurrence_edit_scope', $error->get_error_code() );
		}
		$this->assertNull( $guard->rest_request( null, [], new WP_REST_Request( 'GET', '/wp/v2/tribe_events/' . $row->provisional_id ) ) );
		$this->assertNull( $guard->rest_request( null, [], new WP_REST_Request( 'POST', '/wp/v2/tribe_events/' . $event->ID ) ) );
		add_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		$this->assertNull( $guard->rest_request( null, [], $request ) );
		$this->assertFalse( $guard->reject_post_write( false, [ 'ID' => $row->provisional_id ] ) );
		$this->assertNull( $guard->reject_delete( null, get_post( $row->provisional_id ) ) );
	}

	/** @test */
	public function should_remove_unsupported_row_actions_and_label_the_event_scope(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row   = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$actions = tribe( Occurrence_Writes::class )->row_actions( [ 'edit' => 'Edit', 'trash' => 'Trash', 'inline hide-if-no-js' => 'Quick Edit', 'view' => 'View' ], get_post( $row->provisional_id ) );
		$this->assertArrayNotHasKey( 'trash', $actions );
		$this->assertArrayNotHasKey( 'inline hide-if-no-js', $actions );
		$this->assertStringContainsString( 'Edit event and dates', $actions['edit'] );
		$this->assertSame( 'View', $actions['view'] );
	}
}
