<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_REST_Request;

class EditorTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @test */
	public function should_describe_the_selected_date_and_shared_content_in_both_editors(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', $event->ID )->order_by( 'start_date', 'DESC' )->first();
		$editor = tribe( Editor::class );
		$data = $editor->data( $row->provisional_id );
		$this->assertSame( 'Editing occurrence', $data['heading'] );
		$this->assertSame( 'Multiple dates', $data['scheduleLabel'] );
		$this->assertStringContainsString( 'January 12, 2050', $data['start'] );
		$this->assertStringContainsString( 'moves only this occurrence', $data['scope'] );
		ob_start();
		$editor->render( get_post( $row->provisional_id ) );
		$html = ob_get_clean();
		$this->assertStringContainsString( esc_html( $data['start'] ), $html );
		$this->assertStringContainsString( esc_url( $data['parentEditLink'] ), $html );
		$this->assertStringContainsString( 'tec-occurrence-admin__editor', $html );
		$request = new WP_REST_Request( 'GET' );
		$request->set_param( 'context', 'edit' );
		$this->assertSame( $data, $editor->rest_data( [ 'id' => $row->provisional_id ], Editor::FIELD, $request ) );
	}

	/** @test */
	public function should_never_expose_edit_context_in_public_responses_or_to_readers(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post = $this->given_a_multi_date_event();
		$editor = tribe( Editor::class );
		$request = new WP_REST_Request( 'GET' );
		$request->set_param( 'context', 'view' );
		$this->assertSame( [], $editor->rest_data( [ 'id' => $post->ID ], Editor::FIELD, $request ) );
		$this->assertSame( [ 'edit' ], $GLOBALS['wp_rest_additional_fields']['tribe_events'][Editor::FIELD]['schema']['context'] );
		$request->set_param( 'context', 'edit' );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->assertSame( [], $editor->rest_data( [ 'id' => $post->ID ], Editor::FIELD, $request ) );
	}

	/** @test */
	public function should_refresh_classification_after_saves_and_defer_scope_to_pro(): void {
		$post = $this->given_a_multi_date_event();
		$editor = tribe( Editor::class );
		$this->assertSame( 'dates', $editor->data( $post->ID )['schedule'] );
		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=2" ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'context', 'edit' );
		$data = $editor->rest_data( [ 'id' => $post->ID ], Editor::FIELD, $request );
		$this->assertSame( 'rules', $data['schedule'] );
		$this->assertTrue( $data['locked'] );
		add_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		try {
			$this->assertStringContainsString( 'scope controls', $editor->data( $post->ID )['scope'] );
		} finally {
			remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		}
	}
}
