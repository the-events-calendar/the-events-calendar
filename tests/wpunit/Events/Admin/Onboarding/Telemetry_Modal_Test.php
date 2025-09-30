<?php

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Telemetry\Telemetry as Common_Telemetry;

/**
 * Class Telemetry_Modal_Test
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Onboarding
 */
class Telemetry_Modal_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @test
	 */
	public function it_should_not_show_telemetry_modal_when_onboarding_wizard_is_completed() {
		// Set up wizard as completed (finished = true, completed_tabs = [0, 1, 2]).
		update_option( 'tec_onboarding_wizard_data', [
			'finished' => true,
			'completed_tabs' => [ 0, 1, 2 ],
		] );

		// Test the modal status calculation.
		$should_show = Common_Telemetry::calculate_modal_status();

		$this->assertFalse( $should_show, 'Modal should not show when wizard is completed.' );

		// Clean up.
		delete_option( 'tec_onboarding_wizard_data' );
	}

	/**
	 * @test
	 */
	public function it_should_show_telemetry_modal_when_onboarding_wizard_is_skipped() {
		// Set up wizard as skipped (finished = true, but only tab 0 completed).
		update_option( 'tec_onboarding_wizard_data', [
			'finished' => true,
			'completed_tabs' => [ 0 ],
		] );

		// Test the modal status calculation.
		$should_show = Common_Telemetry::calculate_modal_status();

		$this->assertTrue( $should_show, 'Modal should show when wizard is skipped (only tab 0 completed).' );

		// Clean up.
		delete_option( 'tec_onboarding_wizard_data' );
	}

	/**
	 * @test
	 */
	public function it_should_show_telemetry_modal_when_no_onboarding_data_exists() {
		// Ensure no onboarding data exists.
		delete_option( 'tec_onboarding_wizard_data' );

		// Test the modal status calculation.
		$should_show = Common_Telemetry::calculate_modal_status();

		$this->assertTrue( $should_show, 'Modal should show when no onboarding data exists.' );
	}
}
