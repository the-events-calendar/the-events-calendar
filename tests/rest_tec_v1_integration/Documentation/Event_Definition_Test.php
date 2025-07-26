<?php

namespace TEC\Events\Tests\REST\TEC\V1\Documentation;

use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class Event_Definition_Test extends WPTestCase {
	use SnapshotAssertions;

	/**
	 * Test the Event_Definition documentation output
	 */
	public function test_event_definition_json_snapshot() {
		$instance = new Event_Definition();
		$this->assertMatchesJsonSnapshot( wp_json_encode( $instance->get_documentation(), JSON_SNAPSHOT_OPTIONS ) );
	}
}