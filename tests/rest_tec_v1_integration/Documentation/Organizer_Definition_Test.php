<?php

namespace TEC\Events\Tests\REST\TEC\V1\Documentation;

use TEC\Events\REST\TEC\V1\Documentation\Organizer_Definition;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class Organizer_Definition_Test extends WPTestCase {
	use SnapshotAssertions;

	/**
	 * Test the Organizer_Definition documentation output
	 */
	public function test_organizer_definition_json_snapshot() {
		$instance = new Organizer_Definition();
		$this->assertMatchesJsonSnapshot( wp_json_encode( $instance->get_documentation(), JSON_SNAPSHOT_OPTIONS ) );
	}
}