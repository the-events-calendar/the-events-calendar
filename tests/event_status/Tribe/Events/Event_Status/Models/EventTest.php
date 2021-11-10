<?php

namespace Tribe\Events\Event_Status\Models;

use Tribe\Events\Event_Status\Event_Meta;

class EventTest extends \Codeception\TestCase\WPTestCase {

	public static function wpSetUpBeforeClass() {
		static::factory()->event = new \Tribe\Events\Test\Factories\Event();
	}

	/**
	 * @test
	 */
	public function should_add_status_meta_to_event_model() {
		$status = 'canceled';
		$reason = 'Because Tests';
		$event = static::factory()->event->create( [
			'when'       => '+1 week',
			'meta_input' => [
				Event_Meta::$key_status        => $status,
				Event_Meta::$key_status_reason => $reason,
			],
		] );

		$fetched_event = tribe_get_event( $event );
		$this->assertEquals( $status, $fetched_event->event_status );
		$this->assertEquals( $reason, $fetched_event->event_status_reason );
	}
}
