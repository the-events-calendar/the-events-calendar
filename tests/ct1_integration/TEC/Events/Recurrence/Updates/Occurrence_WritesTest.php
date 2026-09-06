<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_REST_Request;

class Occurrence_WritesTest extends WPTestCase {
	use With_Recurrence_Engine;

	public static $redirect;

	/** @after */
	public function restore_ownership(): void {
		remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
	}

	public static function capture_redirect( $location ) {
		self::$redirect = $location;
		return false;
	}

	/** @test */
	public function should_keep_each_occurrence_url_on_get_and_after_save(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$previous_screen = $GLOBALS['current_screen'] ?? null;
		$request = $_REQUEST;
		add_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		try {
			set_current_screen( 'edit-tribe_events' );
			foreach ( Occurrence::where( 'post_id', '=', $event->ID )->all() as $row ) {
				$id = (int) $row->provisional_id;
				$expected = admin_url( 'post.php?post=' . $id . '&action=edit' );
				$this->assertSame( $expected, get_edit_post_link( $id, 'raw' ) );
				$_REQUEST = [ 'post' => $id, 'action' => 'edit' ];
				self::$redirect = null;
				do_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post' );
				$this->assertNull( self::$redirect );
				$_REQUEST = [ 'post_ID' => $id, 'action' => 'editpost' ];
				do_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post' );
				$this->assertSame( $id, wp_update_post( [ 'ID' => $id, 'post_title' => 'Shared title' ], true ) );
				$url = apply_filters( 'redirect_post_location', $expected . '&message=1', $id );
				parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $args );
				$this->assertSame( (string) $id, $args['post'] );
			}
		} finally {
			$GLOBALS['current_screen'] = $previous_screen;
			$_REQUEST = $request;
			remove_filter( 'wp_redirect', [ self::class, 'capture_redirect' ], 0 );
		}
	}

	/** @test */
	public function should_save_shared_content_and_only_move_the_selected_date(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$rows = iterator_to_array( Occurrence::where( 'post_id', '=', $event->ID )->order_by( 'start_date', 'ASC' )->all(), false );
		$row = end( $rows );
		$id = (int) $row->provisional_id;
		$slug = get_post( $event->ID )->post_name;
		get_post( $rows[0]->provisional_id ); // Prime a sibling before the shared update.
		$this->assertSame( $id, wp_update_post( [ 'ID' => $id, 'post_title' => 'Shared content', 'post_content' => 'Shared description', 'post_status' => 'draft' ], true ) );
		$this->assertSame( 'Shared content', get_post( $event->ID )->post_title );
		$this->assertSame( 'Shared content', get_post( $rows[0]->provisional_id )->post_title );
		$this->assertSame( 'Shared description', get_post( $id )->post_content );
		$this->assertSame( 'draft', get_post_status( $id ) );
		$this->assertSame( $slug, get_post( $event->ID )->post_name );
		update_post_meta( $id, '_EventStartDate', '2050-02-01 09:00:00' );
		update_post_meta( $id, '_EventEndDate', '2050-02-01 10:00:00' );
		do_action( 'tribe_events_update_meta', $id );
		$this->assertSame( '2050-02-01 09:00:00', Occurrence::find( $row->occurrence_id )->start_date );
		$this->assertSame( $rows[0]->start_date, Occurrence::find( $rows[0]->occurrence_id )->start_date );
		$this->assertSame( $id, (int) Occurrence::find( $row->occurrence_id )->provisional_id );
		$this->assertFalse( wp_trash_post( $id ) );
		$this->assertFalse( wp_delete_post( $id, true ) );
	}

	/** @test */
	public function should_save_rest_content_with_the_occurrence_response_identity(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', '=', $event->ID )->order_by( 'start_date', 'DESC' )->first();
		$id = (int) $row->provisional_id;
		$request = new WP_REST_Request( 'POST', '/wp/v2/tribe_events/' . $id );
		$request->set_body_params( [ 'title' => 'REST shared title', 'content' => 'REST description', 'status' => 'draft' ] );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
		$this->assertSame( $id, $response->get_data()['id'] );
		$this->assertSame( 'REST shared title', get_post( $event->ID )->post_title );
		$this->assertSame( $row->start_date, Occurrence::find( $row->occurrence_id )->start_date );
		$read = rest_get_server()->dispatch( new WP_REST_Request( 'GET', '/wp/v2/tribe_events/' . $id ) );
		$this->assertSame( $id, $read->get_data()['id'] );
		$this->assertSame( 'REST shared title', $read->get_data()['title']['rendered'] );
		$error = rest_get_server()->dispatch( new WP_REST_Request( 'DELETE', '/wp/v2/tribe_events/' . $id ) );
		$this->assertSame( 409, $error->get_status() );
		wp_set_current_user( 0 );
		$request->set_param( 'title', 'Unauthorized update' );
		$this->assertSame( 401, rest_get_server()->dispatch( $request )->get_status() );
		$this->assertSame( 'REST shared title', get_post( $event->ID )->post_title );
	}

	/** @test */
	public function should_store_categories_revisions_and_attachments_on_the_event(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$id = (int) $row->provisional_id;
		$first = static::factory()->term->create( [ 'taxonomy' => 'tribe_events_cat' ] );
		$second = static::factory()->term->create( [ 'taxonomy' => 'tribe_events_cat' ] );
		wp_set_object_terms( $event->ID, [ $first ], 'tribe_events_cat' );
		wp_set_object_terms( $id, [ $second ], 'tribe_events_cat' );
		$this->assertSame( [ $second ], wp_get_object_terms( $event->ID, 'tribe_events_cat', [ 'fields' => 'ids' ] ) );
		$this->assertSame( [ $second ], wp_get_object_terms( $id, 'tribe_events_cat', [ 'fields' => 'ids' ] ) );
		clean_object_term_cache( $id, 'tribe_events' );
		update_object_term_cache( [ $id ], 'tribe_events' );
		$this->assertSame( [ $second ], wp_list_pluck( get_the_terms( $id, 'tribe_events_cat' ), 'term_id' ) );
		wp_remove_object_terms( $id, [ $second ], 'tribe_events_cat' );
		$this->assertSame( [], wp_get_object_terms( $event->ID, 'tribe_events_cat', [ 'fields' => 'ids' ] ) );
		global $wpdb;
		$this->assertSame( '0', $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE object_id = %d", $id ) ) );
		wp_update_post( [ 'ID' => $id, 'post_title' => 'Revision content' ] );
		$revision = _wp_put_post_revision( $id );
		$this->assertIsInt( $revision );
		$this->assertSame( $event->ID, wp_get_post_parent_id( $revision ) );
		$attachment = wp_insert_attachment( [ 'post_title' => 'Occurrence attachment', 'post_parent' => $id ] );
		$this->assertSame( $event->ID, wp_get_post_parent_id( $attachment ) );
	}

	/** @test */
	public function should_keep_row_edit_links_and_defer_to_pro(): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event = $this->given_a_multi_date_event();
		$row = Occurrence::where( 'post_id', '=', $event->ID )->first();
		$guard = tribe( Occurrence_Writes::class );
		$actions = [ 'edit' => 'Edit', 'trash' => 'Trash', 'inline hide-if-no-js' => 'Quick Edit', 'view' => 'View' ];
		$filtered = $guard->row_actions( $actions, get_post( $row->provisional_id ) );
		$this->assertArrayNotHasKey( 'trash', $filtered );
		$this->assertArrayNotHasKey( 'inline hide-if-no-js', $filtered );
		$this->assertStringContainsString( 'post=' . $row->provisional_id, $filtered['edit'] );
		global $wpdb;
		$sql = "UPDATE `{$wpdb->posts}` SET `post_title` = 'ID = {$row->provisional_id}' WHERE `ID` = {$row->provisional_id}";
		$this->assertStringContainsString( "'ID = {$row->provisional_id}' WHERE `ID` = {$event->ID}", $guard->route_shared_write( $sql ) );
		add_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		$this->assertSame( $sql, $guard->route_shared_write( $sql ) );
		$this->assertSame( $actions, $guard->row_actions( $actions, get_post( $row->provisional_id ) ) );
		$this->assertNull( $guard->rest_request( null, [], new WP_REST_Request( 'DELETE', '/wp/v2/tribe_events/' . $row->provisional_id ) ) );
		$this->assertNull( $guard->reject_delete( null, get_post( $row->provisional_id ) ) );
	}
}
