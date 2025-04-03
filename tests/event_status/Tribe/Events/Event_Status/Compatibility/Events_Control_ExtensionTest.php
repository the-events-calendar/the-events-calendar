<?php

namespace Tribe\Events\Event_Status\Compatibility\Events_Control_Extension;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Event_Status\Admin_Template;
use Tribe\Events\Test\Factories\Event;
use Tribe\Tests\Traits\With_Uopz;

class Events_Control_ExtensionTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Uopz;

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();

		// Activate the Compatibility provider directly, skipping the `is_plugin_active` check.
		$this->service_provider = new Service_Provider ( tribe() );
		$this->service_provider->register();

		// Simulate the `tribe_plugins_loaded` action to ensure all the provider methods are hooked.
		$this->service_provider->handle_filters();

		$this->set_fn_return( 'wp_create_nonce', '123123' );
	}

	/**
	 * @test
	 */
	public function it_should_return_empty_template_when_no_extension_found() {
		$html = $this->service_provider->replace_metabox_template( 'file', 'name', tribe( Admin_Template::class ) );

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * @test
	 */
	public function it_should_return_template_when_extension_found() {
		if ( ! class_exists( '\\Events_Control_Main' ) ) {
			require_once codecept_data_dir( 'classes/Extensions/EventsControl/Main.php' );
		}
		if ( ! class_exists( '\\Events_Control_Metabox' ) ) {
			require_once codecept_data_dir( 'classes/Extensions/EventsControl/Metabox.php' );
		}
		if ( ! class_exists( '\\Events_Control_Hooks' ) ) {
			require_once codecept_data_dir( 'classes/Extensions/EventsControl/Hooks.php' );
		}
		if ( ! class_exists( '\\Events_Control_Event_Meta' ) ) {
			require_once codecept_data_dir( 'classes/Extensions/EventsControl/Event_Meta.php' );
		}

		$html = $this->service_provider->replace_metabox_template( 'file', 'name', tribe( Admin_Template::class ) );

		$this->assertMatchesSnapshot( $html );
	}
}
