<?php
namespace Tribe\Events\Pro\Recurrence;

use Prophecy\Argument;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Admin_Notices as Admin_Notices;

class Events_SaverTest extends \Codeception\TestCase\WPTestCase {

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

}