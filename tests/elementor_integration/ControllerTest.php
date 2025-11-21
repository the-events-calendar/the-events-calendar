<?php

namespace TEC\Events\Integrations\Plugins\Elementor;
use Tribe__Main;
use TEC\Events\Integrations\Plugins\Elementor\Template\Controller as Elementor_Controller;

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
		$this->assertEquals( 'default', $options['tribeEventsTemplate'] );

		// Simulate Elementor Pro initialization.
		do_action( 'elementor_pro/init' );

		// Verify the setting has been reset to empty string (Default Events Template).
		$this->assertEquals( '', tribe_get_option( 'tribeEventsTemplate' ) );
	}

	/**
	 * It should not affect template setting when using a custom page template.
	 *
	 * @test
	 */
	public function should_not_affect_template_setting_when_using_custom_template(): void {
		// Clear the options cache to ensure fresh values.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Set the template to a custom page template.
		tribe_update_option( 'tribeEventsTemplate', 'custom-template.php' );

		// Clear cache again after setting to ensure the new value is used.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Verify the setting is set to the custom template by checking the raw option value.
		$options = get_option( Tribe__Main::OPTIONNAME, [] );
		$this->assertEquals( 'custom-template.php', $options['tribeEventsTemplate'] );

		// Simulate Elementor Pro initialization.
		do_action( 'elementor_pro/init' );

		// Verify the setting remains unchanged.
		$updated_options = get_option( Tribe__Main::OPTIONNAME, [] );
		$this->assertEquals( 'custom-template.php', $updated_options['tribeEventsTemplate'] );
	}

	/**
	 * It should not affect template setting when already using Default Events Template.
	 *
	 * @test
	 */
	public function should_not_affect_template_setting_when_already_default_events_template(): void {
		// Clear the options cache to ensure fresh values.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Set the template to empty string (Default Events Template).
		tribe_update_option( 'tribeEventsTemplate', '' );

		// Clear cache again after setting to ensure the new value is used.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );

		// Verify the setting is set to empty string by checking the raw option value.
		$options = get_option( Tribe__Main::OPTIONNAME, [] );
		$this->assertEquals( '', $options['tribeEventsTemplate'] );

		// Simulate Elementor Pro initialization.
		do_action( 'elementor_pro/init' );

		// Verify the setting remains unchanged.
		$this->assertEquals( '', tribe_get_option( 'tribeEventsTemplate' ) );
	}

	/**
	 * Provides different option values to test normalization and casting.
	 *
	 * @return \Generator
	 */
	public function provider_option_values(): \Generator {
		yield 'string option remains unchanged' => [ 'default-template', 'default-template' ];
		yield 'empty string remains unchanged'  => [ '', '' ];
		yield 'null becomes empty string'       => [ null, '' ];
		yield 'integer is cast to string'       => [ 123, '123' ];
	}

	/**
	 * It should always return a string regardless of the input type.
	 *
	 * @test
	 * @dataProvider provider_option_values
	 *
	 * @param mixed  $input    Input option value.
	 * @param string $expected Expected normalized string output.
	 */
	public function it_always_returns_a_string_from_filter_events_template_setting_option( $input, string $expected ) {
		$controller = tribe( Elementor_Controller::class );

		$result = $controller->filter_events_template_setting_option( $input );

		$this->assertSame( $expected, $result );
	}

}
