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

	/**
	 * remove_exclusions will remove exclusions
	 */
	public function test_remove_exclusions_will_remove_exclusions() {
		$date_durations  = '[{"timestamp":1448438400,"duration":32400},{"timestamp":1449043200,"duration":32400},{"timestamp":1449648000,"duration":32400},{"timestamp":1450252800,"duration":32400},{"timestamp":1450857600,"duration":32400},{"timestamp":1451462400,"duration":32400},{"timestamp":1452067200,"duration":32400},{"timestamp":1452672000,"duration":32400},{"timestamp":1453276800,"duration":32400},{"timestamp":1453881600,"duration":32400},{"timestamp":1454486400,"duration":32400},{"timestamp":1455091200,"duration":32400},{"timestamp":1455696000,"duration":32400}]';
		$exclusion_dates = '[{"timestamp":1449043200,"duration":0},{"timestamp":1452067200,"duration":0},{"timestamp":1454486400,"duration":0}]';
	}
}