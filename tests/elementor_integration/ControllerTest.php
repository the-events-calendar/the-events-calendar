<?php

namespace TEC\Events\Integrations\Plugins\Elementor;
use Tribe__Main;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should reset template setting when Elementor Pro is activated
	 * and the settings was previouslyset to Default Page Template.
	 *
	 * @test
	 */
	public function should_reset_template_setting_when_method_called_with_default_template(): void {
		// Verify that Elementor Pro is not active.
		$this->assertFalse( defined( 'ELEMENTOR_PRO_VERSION' ) );

		// Clear the options cache to ensure fresh values.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Set the template to 'default' (Default Page Template).
		tribe_update_option( 'tribeEventsTemplate', 'default' );

		// Clear cache again after setting to ensure the new value is used.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Verify the setting is set to 'default' by checking the raw option value.
		$options = get_option( Tribe__Main::OPTIONNAME, [] );
		$this->assertEquals( 'default', $options['tribeEventsTemplate'] ?? '' );

		// Simulate Elementor Pro initialization.
		do_action( 'elementor_pro/init' );

		// Verify the setting has been reset to empty string (Default Events Template).
		$this->assertEquals( '', tribe_get_option( 'tribeEventsTemplate', 'default' ) );
	}
}
