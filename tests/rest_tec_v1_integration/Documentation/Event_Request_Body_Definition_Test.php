<?php

namespace TEC\Events\Tests\REST\TEC\V1\Documentation;

use TEC\Events\REST\TEC\V1\Documentation\Event_Request_Body_Definition;
use Codeception\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class Event_Request_Body_Definition_Test extends WPTestCase {
	use SnapshotAssertions;

	/**
	 * Test the Event_Request_Body_Definition documentation output
	 */
	public function test_event_request_body_definition_json_snapshot() {
		$instance = new Event_Request_Body_Definition();
		$this->assertMatchesJsonSnapshot( wp_json_encode( $instance->get_documentation(), JSON_SNAPSHOT_OPTIONS ) );
	}
}