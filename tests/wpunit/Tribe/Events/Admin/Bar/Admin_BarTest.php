<?php
namespace Tribe\Events\Admin\Bar;

use Tribe\Events\Test\WP_Screen;
use Tribe__Events__Admin__Bar__Admin_Bar as Admin_Bar;
use Tribe__Events__Constants as Constants;

class Admin_BarTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;

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
	 * it should return admin bar disabled if constant set
	 */
	public function it_should_return_admin_bar_disabled_if_constant_set() {
		$constants                                = new Constants( true );
		$constants['TRIBE_DISABLE_TOOLBAR_ITEMS'] = true;

		$sut = new Admin_Bar( null, $constants );

		$this->assertFalse( $sut->is_enabled() );
	}

	/**
	 * @test
	 * it should return admin bar disabled if is network admin
	 */
	public function it_should_return_admin_bar_disabled_if_is_network_admin() {
		// cannot do this... final class...
		// $screen = $this->prophesize( '\WP_Screen' );
		// $screen->in_admin( 'network' )->willReturn( true );
		// $GLOBALS['current_screen'] = $screen->reveal();

		// so we mock the screen object the 1995 way
		$GLOBALS['current_screen'] = new WP_Screen( ['in_admin'=>true] );

		$sut = new Admin_Bar();

		$this->assertFalse( $sut->is_enabled() );
	}

	/**
	 * @test
	 * it should configure the admin bar
	 */
	public function it_should_configure_the_admin_bar() {
		$constants    = new Constants( true );
		$configurator = $this->prophesize( 'Tribe__Events__Admin__Bar__Configurator_Interface' );
		$admin_bar    = $this->getMockBuilder( 'WP_Admin_Bar' )->disableOriginalConstructor()->getMock();
		$configurator->configure( $admin_bar )->willReturn( true );

		$sut = new Admin_Bar( $configurator->reveal(), $constants );
		$sut->init( $admin_bar );

		$configurator->configure( $admin_bar )->shouldHaveBeenCalled();
	}
}
