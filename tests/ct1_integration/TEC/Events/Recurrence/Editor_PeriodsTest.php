<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Error;

class Editor_PeriodsTest extends WPTestCase {
	use With_Recurrence_Engine;

	/** @after */
	public function restore_request(): void {
		unset( $_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ], $_POST[ Admin_Provider::FIELD ] );
		tribe_remove_option( 'datepickerFormat' );
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
	}

	public function editors(): array {
		return [ 'Classic' => [ false ], 'Blocks' => [ true ] ];
	}

	/**
	 * @test
	 * @dataProvider editors
	 */
	public function should_preserve_overnight_and_multiday_endpoints_and_ids( bool $blocks ): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		tribe_update_option( 'datepickerFormat', 0 );
		$event = $this->given_a_multi_date_event( [ [ 'start' => '2050-01-10 22:00:00', 'end' => '2050-01-11 02:00:00' ], [ 'start' => '2050-01-17 09:00:00', 'end' => '2050-01-20 17:00:00' ], [ 'start' => '2050-01-25 09:00:30', 'end' => '2050-01-25 10:00:45' ] ] );
		$service = tribe( Dates_Service::class );
		$before  = $service->get_dates( $event->ID );
		wp_update_post( [ 'ID' => $event->ID, 'post_title' => 'Unrelated title edit' ] );
		if ( $blocks ) {
			$mirror = get_post_meta( $event->ID, Blocks_Provider::META_KEY, true );
			$this->assertStringContainsString( '2050-01-11', $mirror );
			$this->assertStringContainsString( '2050-01-20', $mirror );
			tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $event->ID ) );
		} else {
			ob_start();
			tribe( Admin_Provider::class )->render_section( $event->ID );
			$html = ob_get_clean();
			$this->assertStringContainsString( '9:00:30am', $html );
			$this->assertStringContainsString( 'data-format="g:i:sa"', $html );
			$_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ] = wp_create_nonce( Admin_Provider::NONCE_ACTION );
			$_POST[ Admin_Provider::FIELD ] = [ [ 'date' => '2050-01-10', 'end_date' => '2050-01-11', 'start' => '22:00', 'end' => '02:00' ], [ 'date' => '2050-01-17', 'end_date' => '2050-01-20', 'start' => '09:00', 'end' => '17:00' ], [ 'date' => '2050-01-25', 'start' => '09:00:30', 'end' => '10:00:45' ] ];
			tribe( Admin_Provider::class )->save_dates( $event->ID );
		}
		$this->assertSame( $before, $service->get_dates( $event->ID ) );
	}

	/**
	 * @test
	 * @dataProvider editors
	 */
	public function should_reject_invalid_rows_without_removing_existing_dates( bool $blocks ): void {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		tribe_update_option( 'datepickerFormat', 0 );
		$event   = $this->given_a_multi_date_event();
		$service = tribe( Dates_Service::class );
		$before  = $service->get_dates( $event->ID );
		$rows    = [ [ 'date' => '2050-01-10', 'start' => '22:00', 'end' => '02:00' ] ];
		if ( $blocks ) {
			update_post_meta( $event->ID, Blocks_Provider::META_KEY, wp_json_encode( $rows ) );
			tribe( Blocks_Provider::class )->consume_blocks_dates( get_post( $event->ID ) );
			$this->assertInstanceOf( WP_Error::class, tribe( Blocks_Provider::class )->report_save_failure( null ) );
		} else {
			$_POST[ Admin_Provider::NONCE_ACTION . '_nonce' ] = wp_create_nonce( Admin_Provider::NONCE_ACTION );
			$_POST[ Admin_Provider::FIELD ] = $rows;
			try {
				tribe( Admin_Provider::class )->save_dates( $event->ID );
				$this->fail( 'Invalid dates must stop the save.' );
			} catch ( \WPDieException $exception ) {
				$this->assertStringContainsString( 'additional dates were not changed', $exception->getMessage() );
			}
		}
		$this->assertSame( $before, $service->get_dates( $event->ID ) );
	}
}
