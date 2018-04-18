<?php
namespace TEC\Tests\functions\template_tags;

use Codeception\TestCase\WPTestCase;

class organizerTest extends WPTestCase {
	/**
	 * Test multiple combinations possibles for the sensitization of organizer lists when an ordered list is presented.
	 *
	 * @since TBD
	 */
	public function test_tribe_sanitize_organizers( ) {
		// Test empty values
		$this->assertEquals( [], tribe_sanitize_organizers());
		$this->assertEquals( [], tribe_sanitize_organizers([], []));
		// Return original value if no order value is present
		$this->assertEquals( [1,2,3], tribe_sanitize_organizers([1,2,3]));
		// Test only values that are not ordered
		$this->assertEquals( [1,2,3], tribe_sanitize_organizers([1,2,3], []));
		// Same values on current and ordered side
		$this->assertEquals( [1,2,3], tribe_sanitize_organizers([1,2,3], [1,2,3]));
		// Test values that are not part of the ordered list
		$this->assertEquals( [4], tribe_sanitize_organizers([4], [1,2,3]));
		// Make sure order is respected if a new values are present
		$this->assertEquals( [1,2,3,4,5,6], tribe_sanitize_organizers([4, 2, 5, 1, 3, 6], [1,2,3]));
		// Test only a single ordered value the remaining are placed are entered
		$this->assertEquals( [2,1,3,4,5,6], tribe_sanitize_organizers([1, 2, 3, 4, 5, 6], [2]));
	}
}
