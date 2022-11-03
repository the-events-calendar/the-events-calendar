<?php

namespace Tribe\Events\Event_Status;

use Tribe__Events__Updater as Updater;

class Updater_Test extends \Codeception\TestCase\WPTestCase {

	public static function wpSetUpBeforeClass() {
		static::factory()->event = new \Tribe\Events\Test\Factories\Event();
	}

	/**
	 * @test
	 */
	public function should_migrate_extension_reason_fields_to_central_field() {
		$updater = new Updater( '5.9.2' );
		$status  = 'canceled';
		$reason  = 'Because Tests';
		$event   = static::factory()->event->create( [
			'when'       => '+1 week',
			'meta_input' => [
				Event_Meta::$key_control_status         => $status,
				Event_Meta::$key_status_canceled_reason => $reason,
			],
		] );

		$status_2 = 'postponed';
		$reason_2 = 'Because Tests2';
		$event_2  = static::factory()->event->create( [
			'when'       => '+1 week',
			'meta_input' => [
				Event_Meta::$key_control_status          => $status_2,
				Event_Meta::$key_status_postponed_reason => $reason_2,
			],
		] );

		$status_3          = 'postponed';
		$reason_canceled_3 = 'Because Canceled';
		$reason_3          = '';
		$event_3           = static::factory()->event->create( [
			'when'       => '+1 week',
			'meta_input' => [
				Event_Meta::$key_control_status          => $status_3,
				Event_Meta::$key_status_canceled_reason  => $reason_canceled_3,
				Event_Meta::$key_status_postponed_reason => $reason_3,
			],
		] );

		$updater->migrate_event_status_reason_field();

		// Flush the cache to remove cached and memoized artifacts.
		wp_cache_flush();

		$fetched_event = tribe_get_event( $event, OBJECT, 'raw', true );
		$this->assertEquals( $status, $fetched_event->event_status );
		$this->assertEquals( $reason, $fetched_event->event_status_reason );

		$fetched_event_2 = tribe_get_event( $event_2, OBJECT, 'raw', true );
		$this->assertEquals( $status_2, $fetched_event_2->event_status );
		$this->assertEquals( $reason_2, $fetched_event_2->event_status_reason );

		$fetched_event_3 = tribe_get_event( $event_3, OBJECT, 'raw', true );
		$this->assertEquals( $status_3, $fetched_event_3->event_status );
		$this->assertEquals( $reason_3, $fetched_event_3->event_status_reason );
	}
}
