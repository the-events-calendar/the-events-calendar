<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Error;
use WP_REST_Request;

class First_SaveTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @after */
	public function restore_request(): void {
		unset( $_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ], $_POST[ Admin_Provider::FIELD ] );
		remove_filter( 'update_post_metadata', [ self::class, 'fail_recurrence_write' ], -20 );
		remove_filter( 'add_post_metadata', [ self::class, 'fail_recurrence_write' ], -20 );
		tribe_remove_option( 'datepickerFormat' );
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
	}

	private function first_save_post(): int {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		// Avoid orphan CT1 rows belonging to recycled low IDs in the suite database.
		$id = wp_insert_post( [ 'import_id' => 90000000, 'post_type' => 'tribe_events', 'post_status' => 'auto-draft', 'post_title' => 'First publish' ] );
		$this->assertNull( Event::find( $id, 'post_id' ) );
		wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
		foreach ( [ '_EventStartDate' => '2050-01-01 09:00:00', '_EventEndDate' => '2050-01-01 10:00:00', '_EventStartDateUTC' => '2050-01-01 09:00:00', '_EventEndDateUTC' => '2050-01-01 10:00:00', '_EventTimezone' => 'UTC', '_EventDuration' => 3600 ] as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}
		$this->assertNull( Event::find( $id, 'post_id' ), 'The deferred custom-table commit has not run.' );
		return $id;
	}

	/** @test */
	public function should_persist_additional_dates_on_the_first_classic_save(): void {
		$id = $this->first_save_post();
		tribe_update_option( 'datepickerFormat', 0 );
		$_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ] = wp_create_nonce( Admin_Provider::NONCE_ACTION );
		$_POST[ Admin_Provider::FIELD ] = [ [ 'date' => '2050-01-08', 'start' => '09:00', 'end' => '10:00' ] ];
		do_action( 'tribe_events_update_meta', $id, [], get_post( $id ) );
		$this->assertInstanceOf( Event::class, Event::find( $id, 'post_id' ) );
		$this->assertSame( 2, (int) Occurrence::where( 'post_id', '=', $id )->count() );
		$this->assertNotEmpty( get_post_meta( $id, '_EventRecurrence', true ) );
	}

	/** @test */
	public function should_persist_additional_dates_before_the_first_rest_commit(): void {
		$id = $this->first_save_post();
		update_post_meta( $id, Blocks_Provider::META_KEY, wp_json_encode( [ [ 'date' => '2050-01-08', 'start' => '09:00:00', 'end' => '10:00:00' ] ] ) );
		do_action( 'rest_after_insert_tribe_events', get_post( $id ), new WP_REST_Request( 'POST', '/wp/v2/tribe_events/' . $id ), false );
		$this->assertSame( 2, (int) Occurrence::where( 'post_id', '=', $id )->count() );
		$this->assertStringContainsString( '20500108', Event::find( $id, 'post_id' )->rset );
		$this->assertStringContainsString( '2050-01-08', get_post_meta( $id, Blocks_Provider::META_KEY, true ) );
	}

	public static function fail_recurrence_write( $check, $id, $key ) {
		return '_EventRecurrence' === $key ? false : $check;
	}

	/** @test */
	public function should_report_a_failed_canonical_write_and_keep_submitted_block_dates(): void {
		$id = $this->first_save_post();
		$mirror = wp_json_encode( [ [ 'date' => '2050-01-08', 'start' => '09:00:00', 'end' => '10:00:00' ] ] );
		update_post_meta( $id, Blocks_Provider::META_KEY, $mirror );
		add_filter( 'update_post_metadata', [ self::class, 'fail_recurrence_write' ], -20, 3 );
		add_filter( 'add_post_metadata', [ self::class, 'fail_recurrence_write' ], -20, 3 );
		$blocks = tribe( Blocks_Provider::class );
		$blocks->consume_blocks_dates( get_post( $id ) );
		$error = $blocks->report_save_failure( null );
		$this->assertInstanceOf( WP_Error::class, $error );
		$this->assertSame( 'tec_recurrence_dates_save_failed', $error->get_error_code() );
		$this->assertSame( $mirror, get_post_meta( $id, Blocks_Provider::META_KEY, true ) );
		$this->assertNull( $blocks->report_save_failure( null ) );
		$request = $_REQUEST;
		$_REQUEST['post'] = $id;
		try {
			$blocks->add_block_attribute( [] );
			$this->assertSame( $mirror, get_post_meta( $id, Blocks_Provider::META_KEY, true ) );
		} finally {
			$_REQUEST = $request;
		}
	}
}
