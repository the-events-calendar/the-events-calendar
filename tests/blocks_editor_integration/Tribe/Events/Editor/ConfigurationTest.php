<?php

namespace Tribe\Events\Editor;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Editor\Objects\Event;
use Tribe__Events__Editor__Configuration as Configuration;
use Tribe__Events__Main as TEC;

class ConfigurationTest extends WPTestCase {
	public static function _setUpBeforeClass() {
		parent::_setUpBeforeClass();
		static::factory()->event = new \Tribe\Events\Test\Factories\Event();
	}

	/**
	 * It should include the event data in the editor configuration
	 *
	 * @test
	 */
	public function should_include_the_event_data_in_the_editor_configuration() {
		$_GET['post']  = $event = static::factory()->event->create();
		$configuration = new Configuration();
		$editor_config = $this->mocking_settings( static function () use ( $configuration ) {
			return $configuration->editor_config( [] );
		} );

		$this->assertNotEmpty( $editor_config['post_objects'][ TEC::POSTTYPE ] );
		$this->assertEqualSets( $editor_config['post_objects'][ TEC::POSTTYPE ], ( new Event( $event ) )->data() );
	}

	protected function mocking_settings( callable $f ) {
		$backup = tribe()->isBound( 'events.editor.settings' )
			? tribe( 'events.editor.settings' )
			: null;
		tribe_register( 'events.editor.settings', $this->makeEmpty( \Tribe__Events__Editor__Settings::class ) );

		$return = $f();

		if ( null !== $backup ) {
			tribe_register( 'events.editor.settings', $backup );
		}

		return $return;
	}
}
