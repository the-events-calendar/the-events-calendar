<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use Tribe\Tests\Traits\With_Uopz;

class ControllerTest extends WPTestCase {
	use With_Uopz;

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

	/**
	 * It should stay inactive when an incompatible pro is active
	 *
	 * The raw version comparison against `Tribe__Events__Pro__Main::VERSION` cannot run
	 * here: defining the real Events Calendar Pro class name would poison every later
	 * test in the suite process. The gate wiring is what this test pins; the real
	 * class_exists + version_compare combination runs in the Events Calendar Pro CI,
	 * where both plugins are active.
	 *
	 * @test
	 */
	public function should_stay_inactive_when_an_incompatible_pro_is_active(): void {
		tribe()->setVar( 'ct1_fully_activated', true );
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );

		$this->set_class_fn_return( Controller::class, 'is_incompatible_pro_active', true );

		$this->assertFalse( $this->controller()->is_active() );

		$this->controller()->register();

		$this->assertFalse( Controller::is_registered() );
	}

	/**
	 * It should provide occurrences independently of the activation option and filter
	 *
	 * The static capability read is the pre-boot handshake: it must be true whenever the
	 * infrastructure is available, even while the feature is switched off at runtime.
	 *
	 * @test
	 */
	public function should_provide_occurrences_independently_of_the_runtime_state(): void {
		tribe()->setVar( 'ct1_fully_activated', true );
		update_option( Controller::ACTIVE_OPTION, false );

		$this->assertTrue( Controller::provides_occurrences() );
		$this->assertFalse( $this->controller()->is_active() );

		add_filter( 'tec_events_recurrence_enabled', '__return_false' );

		$this->assertTrue( Controller::provides_occurrences() );
	}

	/**
	 * It should not provide occurrences under the environment kill switch
	 *
	 * @test
	 */
	public function should_not_provide_occurrences_under_the_environment_kill_switch(): void {
		tribe()->setVar( 'ct1_fully_activated', true );

		putenv( Controller::DISABLED . '=1' );

		$this->assertFalse( Controller::provides_occurrences() );

		putenv( Controller::DISABLED );

		$this->assertTrue( Controller::provides_occurrences() );
	}

	/**
	 * It should not provide occurrences without full CT1 activation
	 *
	 * @test
	 */
	public function should_not_provide_occurrences_without_full_ct1_activation(): void {
		tribe()->setVar( 'ct1_fully_activated', false );

		$this->assertFalse( Controller::provides_occurrences() );
	}

	/**
	 * It should not provide occurrences when an incompatible Pro is active
	 *
	 * @test
	 */
	public function should_not_provide_occurrences_when_an_incompatible_pro_is_active(): void {
		tribe()->setVar( 'ct1_fully_activated', true );

		$this->set_class_fn_return( Controller::class, 'is_incompatible_pro_active', true );

		$this->assertFalse( Controller::provides_occurrences() );
	}
}
