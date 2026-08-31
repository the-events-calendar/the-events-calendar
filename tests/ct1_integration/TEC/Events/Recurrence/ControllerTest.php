<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;

class ControllerTest extends WPTestCase {
	/**
	 * @var mixed The `ct1_fully_activated` container value before the test.
	 */
	private $ct1_activated_backup;

	/**
	 * @before
	 */
	public function backup_container_state(): void {
		$this->ct1_activated_backup = tribe()->getVar( 'ct1_fully_activated', false );
	}

	/**
	 * @after
	 */
	public function restore_container_state(): void {
		tribe()->setVar( 'ct1_fully_activated', $this->ct1_activated_backup );
		tribe()->setVar( Controller::class . '_registered', false );
		tribe()->setVar( 'tec_events_recurrence_fully_activated', false );
		putenv( Controller::DISABLED );
		remove_all_filters( 'tec_events_recurrence_enabled' );
	}

	private function controller(): Controller {
		return tribe( Controller::class );
	}

	/**
	 * It should be inactive by default
	 *
	 * @test
	 */
	public function should_be_inactive_by_default(): void {
		tribe()->setVar( 'ct1_fully_activated', true );

		$this->assertFalse( $this->controller()->is_active() );
	}

	/**
	 * It should activate through the option and the filter
	 *
	 * @test
	 */
	public function should_activate_through_the_option_and_the_filter(): void {
		tribe()->setVar( 'ct1_fully_activated', true );

		update_option( Controller::ACTIVE_OPTION, true );
		$this->assertTrue( $this->controller()->is_active() );

		update_option( Controller::ACTIVE_OPTION, false );
		$this->assertFalse( $this->controller()->is_active() );

		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		$this->assertTrue( $this->controller()->is_active() );
	}

	/**
	 * It should stay inactive without full CT1 activation
	 *
	 * @test
	 */
	public function should_stay_inactive_without_full_ct1_activation(): void {
		tribe()->setVar( 'ct1_fully_activated', false );
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );

		$this->assertFalse( $this->controller()->is_active() );
	}

	/**
	 * It should honor the environment kill switch over the filter
	 *
	 * @test
	 */
	public function should_honor_the_environment_kill_switch_over_the_filter(): void {
		tribe()->setVar( 'ct1_fully_activated', true );
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );

		putenv( Controller::DISABLED . '=1' );

		$this->assertFalse( $this->controller()->is_active() );

		putenv( Controller::DISABLED );

		$this->assertTrue( $this->controller()->is_active() );
	}

	/**
	 * It should report no incompatible Pro when Pro is not loaded
	 *
	 * @test
	 */
	public function should_report_no_incompatible_pro_when_pro_is_not_loaded(): void {
		$this->assertFalse( class_exists( 'Tribe__Events__Pro__Main', false ) );
		$this->assertFalse( Controller::is_incompatible_pro_active() );
	}

	/**
	 * It should fire the handshake on registration
	 *
	 * @test
	 */
	public function should_fire_the_handshake_on_registration(): void {
		tribe()->setVar( 'ct1_fully_activated', true );
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );

		$fired_before = did_action( 'tec_events_recurrence_registered' );

		tribe()->register( Controller::class );

		$this->assertTrue( Controller::is_registered() );
		$this->assertTrue( (bool) tribe()->getVar( 'tec_events_recurrence_fully_activated', false ) );
		$this->assertGreaterThan( $fired_before, did_action( 'tec_events_recurrence_registered' ) );

		$this->controller()->unregister();

		$this->assertFalse( (bool) tribe()->getVar( 'tec_events_recurrence_fully_activated', false ) );
	}

	/**
	 * It should not register when inactive
	 *
	 * @test
	 */
	public function should_not_register_when_inactive(): void {
		tribe()->setVar( 'ct1_fully_activated', true );
		// No option, no filter: the gate is closed.

		$this->controller()->register();

		$this->assertFalse( Controller::is_registered() );
		$this->assertFalse( (bool) tribe()->getVar( 'tec_events_recurrence_fully_activated', false ) );
	}
}
