<?php

namespace TEC\Events\Recurrence\Admin;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Recurrence\Pro_History;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use Tribe\Tests\Traits\With_Uopz;

class Pro_StatusTest extends WPTestCase {
	use With_Recurrence_Engine;
	use With_Uopz;

	/** @before */
	public function setup_plugin_functions(): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$this->set_fn_return( 'validate_plugin_requirements', true );
		$this->set_fn_return( 'is_plugin_active', false );
	}

	/** @test */
	public function should_detect_an_inactive_plugin_in_a_renamed_directory_and_offer_activation(): void {
		$this->set_fn_return( 'get_plugins', [ 'renamed-pro/events-calendar-pro.php' => [ 'Version' => '7.9.0' ] ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$data = ( new Pro_Status() )->get();
		$this->assertSame( 'inactive', $data['state'] );
		$this->assertTrue( $data['show'] );
		$this->assertSame( 'Reactivate Pro', $data['label'] );
		parse_str( wp_parse_url( $data['url'], PHP_URL_QUERY ), $args );
		$this->assertSame( 'renamed-pro/events-calendar-pro.php', $args['plugin'] );
		$this->assertNotFalse( wp_verify_nonce( $args['_wpnonce'], 'activate-plugin_' . $args['plugin'] ) );
	}

	/** @test */
	public function should_not_warn_on_clean_free_sites_but_explain_preserved_rules(): void {
		$this->set_fn_return( 'get_plugins', [] );
		delete_option( Pro_History::MEMO_OPTION );
		delete_option( Pro_History::SERIES_SCHEMA_OPTION );
		$this->assertFalse( ( new Pro_Status() )->get()['show'] );
		$this->assertTrue( ( new Pro_Status() )->get( true )['show'] );
	}

	/** @test */
	public function should_distinguish_incompatible_and_unavailable_plugins(): void {
		$this->set_fn_return( 'get_plugins', [ 'events-pro/events-calendar-pro.php' => [ 'Version' => '7.8.0' ] ] );
		$this->assertSame( 'incompatible', ( new Pro_Status() )->get()['state'] );
		$this->set_fn_return( 'get_plugins', [ 'events-pro/events-calendar-pro.php' => [ 'Version' => '7.9.0' ] ] );
		$this->set_fn_return( 'is_plugin_active', true );
		$this->assertSame( 'unavailable', ( new Pro_Status() )->get()['state'] );
		add_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		try {
			$this->assertSame( 'active', ( new Pro_Status() )->get()['state'] );
			$this->assertFalse( ( new Pro_Status() )->get()['show'] );
		} finally {
			remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		}
	}

	/** @test */
	public function should_explain_recovery_without_offering_unauthorized_actions(): void {
		$this->set_fn_return( 'get_plugins', [ 'events-pro/events-calendar-pro.php' => [ 'Version' => '7.9.0' ] ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		$data = ( new Pro_Status() )->get();
		$this->assertSame( '', $data['url'] );
		$this->assertStringContainsString( 'administrator', $data['message'] );
		$this->assertStringContainsString( 'administrator', $data['guidance'] );
	}
}
