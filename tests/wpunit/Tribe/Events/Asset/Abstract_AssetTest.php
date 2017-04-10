<?php
namespace Tribe\Events\Asset;

require_once codecept_data_dir( 'classes/Asset/Dummy_Asset.php' );

use Tribe__Events__Asset__Dummy_Asset as Asset;

class Abstract_AssetTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Asset::class, $sut );
	}

	/**
	 * @return Asset
	 */
	private function make_instance() {
		return new Asset();
	}

	/**
	 * @test
	 * it should not have alias if there are no aliases specified
	 */
	public function it_should_not_have_alias_if_there_are_no_aliases_specified() {
		$sut = $this->make_instance();

		$this->assertFalse( $sut->has_script_alias( 'foo' ) );

	}

	/**
	 * @test
	 * it should not have alias if the expected plugin is not active
	 */
	public function it_should_not_have_alias_if_the_expected_plugin_is_not_active() {
		$sut = $this->make_instance();

		$sut->set_aliases(
			array(
				'foo' => array( 'some-plugin/plugin.php' => 'foo-script' ),
			)
		);

		$this->assertFalse( $sut->has_script_alias( 'foo' ) );
	}

	/**
	 * @test
	 * it should not have alias if plugin is active but script was not queued
	 */
	public function it_should_not_have_alias_if_plugin_is_active_but_script_was_not_queued() {
		$sut = $this->make_instance();

		$sut->set_aliases(
			array(
				'foo' => array( 'the-events-calendar/the-events-calendar.php' => 'foo-script' ),
			)
		);

		$this->assertFalse( $sut->has_script_alias( 'foo' ) );
	}

	/**
	 * @test
	 * it should have alias if plugin is loaded and queued the script
	 */
	public function it_should_have_alias_if_plugin_is_loaded_and_queued_the_script() {
		$sut = $this->make_instance();
		global $wp_scripts;
		$wp_scripts->queue[] = 'foo-script';

		$sut->set_aliases(
			array(
				'foo' => array( 'the-events-calendar/the-events-calendar.php' => 'foo-script' ),
			)
		);

		$this->assertTrue( $sut->has_script_alias( 'foo' ) );
	}

	/**
	 * @test
	 * it should have alias when more than one alias is specified per slug
	 */
	public function it_should_have_alias_when_more_than_one_alias_is_specified_per_slug() {
		$sut = $this->make_instance();
		global $wp_scripts;
		$wp_scripts->queue[] = 'foo-script';

		$sut->set_aliases(
			array(
				'foo' => array(
					'not-a-plugin/plugin.php'                     => 'foo-js',
					'the-events-calendar/the-events-calendar.php' => 'foo-script'
				),
			)
		);

		$this->assertTrue( $sut->has_script_alias( 'foo' ) );
	}

	/**
	 * @test
	 * it should allow registering callbacks to test for aliases
	 */
	public function it_should_allow_registering_callbacks_to_test_for_aliases() {
		$sut = $this->make_instance();
		global $wp_scripts;
		$wp_scripts->queue[] = 'foo-script';

		$called_with = false;
		$f           = function ( $slug ) use ( &$called_with ) {
			$called_with = $slug;

			return true;
		};

		$sut->set_aliases(
			array(
				'foo' => array(
					'the-events-calendar/the-events-calendar.php' => $f
				),
			)
		);

		$this->assertTrue( $sut->has_script_alias( 'foo' ) );
		$this->assertEquals( 'foo', $called_with );
	}
}