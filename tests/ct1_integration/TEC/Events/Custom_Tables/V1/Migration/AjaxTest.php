<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use Spatie\Snapshots\MatchesSnapshots;

class AjaxTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;

	/**
	 * @before
	 */
	public function filter_doing_ajax(): void {
		add_filter( 'wp_doing_ajax', '__return_true' );
	}

	/**
	 * @after
	 */
	public function empty_request_superglobal(): void {
		$_REQUEST = [];
	}

	/**
	 * It should send correct report when migration not required
	 *
	 * @test
	 */
	public function should_send_correct_report_when_migration_not_required(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();
		// Create a user that would be able to see the report.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		// Create the nonce for the request.
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$ajax = tribe( Ajax::class );

		$this->assertMatchesJsonSnapshot( $ajax->send_report( false ) );
	}

	/**
	 * It should check nonce for send_report.
	 *
	 * @test
	 */
	public function should_check_nonce_for_send_report(): void {
		$die_called = false;
		$handler    = function () use ( &$die_called ) {
			$die_called = true;

			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		$ajax = tribe( Ajax::class );
		$ajax->send_report( false );

		$this->assertTrue( $die_called, 'wp_die should be called when nonce is not set' );
	}

	/**
	 * It should check capability for send_report.
	 *
	 * @test
	 */
	public function should_check_capability_for_send_report(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$handler = function () {
			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		ob_start();
		$ajax = tribe( Ajax::class );
		$ajax->send_report( true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'permission',
			$output,
			'Should return permission error for non-admin users' );
	}

	/**
	 * It should check nonce for start_migration.
	 *
	 * @test
	 */
	public function should_check_nonce_for_start_migration(): void {
		$die_called = false;
		$handler    = function () use ( &$die_called ) {
			$die_called = true;

			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		$ajax = tribe( Ajax::class );
		$ajax->start_migration( false );

		$this->assertTrue( $die_called, 'wp_die should be called when nonce is not set' );
	}

	/**
	 * It should check capability for start_migration.
	 *
	 * @test
	 */
	public function should_check_capability_for_start_migration(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$handler = function () {
			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		ob_start();
		$ajax = tribe( Ajax::class );
		$ajax->start_migration( true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'permission',
			$output,
			'Should return permission error for non-admin users' );
	}

	/**
	 * It should start migration successfully.
	 *
	 * @test
	 */
	public function should_start_migration_successfully(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$ajax   = tribe( Ajax::class );
		$result = $ajax->start_migration( false );

		$this->assertIsString( $result, 'Should return a JSON string' );
	}

	/**
	 * It should check nonce for cancel_migration.
	 *
	 * @test
	 */
	public function should_check_nonce_for_cancel_migration(): void {
		$die_called = false;
		$handler    = function () use ( &$die_called ) {
			$die_called = true;

			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		$ajax = tribe( Ajax::class );
		$ajax->cancel_migration( false );

		$this->assertTrue( $die_called, 'wp_die should be called when nonce is not set' );
	}

	/**
	 * It should check capability for cancel_migration.
	 *
	 * @test
	 */
	public function should_check_capability_for_cancel_migration(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$handler = function () {
			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		ob_start();
		$ajax = tribe( Ajax::class );
		$ajax->cancel_migration( true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'permission',
			$output,
			'Should return permission error for non-admin users' );
	}

	/**
	 * It should cancel migration successfully.
	 *
	 * @test
	 */
	public function should_cancel_migration_successfully(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$ajax   = tribe( Ajax::class );
		$result = $ajax->cancel_migration( false );

		$this->assertIsString( $result, 'Should return a JSON string' );
	}

	/**
	 * It should check nonce for revert_migration.
	 *
	 * @test
	 */
	public function should_check_nonce_for_revert_migration(): void {
		$die_called = false;
		$handler    = function () use ( &$die_called ) {
			$die_called = true;

			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		$ajax = tribe( Ajax::class );
		$ajax->revert_migration( false );

		$this->assertTrue( $die_called, 'wp_die should be called when nonce is not set' );
	}

	/**
	 * It should check capability for revert_migration.
	 *
	 * @test
	 */
	public function should_check_capability_for_revert_migration(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$handler = function () {
			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		ob_start();
		$ajax = tribe( Ajax::class );
		$ajax->revert_migration( true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'permission',
			$output,
			'Should return permission error for non-admin users' );
	}

	/**
	 * It should revert migration successfully.
	 *
	 * @test
	 */
	public function should_revert_migration_successfully(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$ajax   = tribe( Ajax::class );
		$result = $ajax->revert_migration( false );

		$this->assertIsString( $result, 'Should return a JSON string' );
	}

	/**
	 * It should check nonce for paginate_events.
	 *
	 * @test
	 */
	public function should_check_nonce_for_paginate_events(): void {
		$die_called = false;
		$handler    = function () use ( &$die_called ) {
			$die_called = true;

			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		$ajax = tribe( Ajax::class );
		$ajax->paginate_events( false );

		$this->assertTrue( $die_called, 'wp_die should be called when nonce is not set' );
	}

	/**
	 * It should check capability for paginate_events.
	 *
	 * @test
	 */
	public function should_check_capability_for_paginate_events(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );

		$handler = function () {
			return function () {
			};
		};
		add_filter( 'wp_die_ajax_handler', $handler );

		ob_start();
		$ajax = tribe( Ajax::class );
		$ajax->paginate_events( true );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'permission',
			$output,
			'Should return permission error for non-admin users' );
	}

	/**
	 * It should paginate events successfully.
	 *
	 * @test
	 */
	public function should_paginate_events_successfully(): void {
		$state = tribe( State::class );
		$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
		$state->save();

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( Ajax::NONCE_ACTION );
		$_GET['page']            = 1;
		$_GET['upcoming']        = true;
		$_GET['report_category'] = '';

		$ajax   = tribe( Ajax::class );
		$result = $ajax->paginate_events( false );

		$this->assertIsString( $result, 'Should return a JSON string' );
	}
}
