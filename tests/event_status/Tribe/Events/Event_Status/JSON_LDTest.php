<?php

namespace Tribe\Events\Event_Status;

use Tribe\Events\Test\Factories\Event;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

class JSON_LDTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( JSON_LD::class, $sut );
	}

	/**
	 * @return JSON_LD
	 */
	private function make_instance() {
		return new JSON_LD();
	}

	/**
	 * @test
	 */
	public function it_sets_offline_attendance_mode_default() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$json_data = \Tribe__Events__JSON_LD__Event::instance()->get_data( $event->ID );
		$object    = array_shift( $json_data );

		$this->assertObjectHasAttribute(
			'eventAttendanceMode',
			$object,
			'Function modify_event did not add default attendance mode.'
		);

		$this->assertEquals(
			JSON_LD::OFFLINE_EVENT_ATTENDANCE_MODE,
			$object->eventAttendanceMode,
			'Function modify_event did not add offline for eventAttendanceMode.'
		);
	}

	/**
	 * @test
	 */
	public function it_should_have_event_status_for_canceled() {
		$event = $this->get_mock_event( 'events/single/canceled_status.json' );
		$json_data = \Tribe__Events__JSON_LD__Event::instance()->get_data( $event->ID );
		$object    = array_shift( $json_data );

		$this->assertObjectHasAttribute(
			'eventStatus',
			$object,
			'Function modify_event did not add eventStatus.'
		);

		$this->assertEquals(
			JSON_LD::CANCELED_SCHEMA,
			$object->eventStatus,
			'Function modify_event did not add CANCELED_SCHEMA status.'
		);
	}

	/**
	 * @test
	 */
	public function it_should_have_event_status_for_postponed() {
		$event = $this->get_mock_event( 'events/single/postponed_status.json' );
		$json_data = \Tribe__Events__JSON_LD__Event::instance()->get_data( $event->ID );
		$object    = array_shift( $json_data );

		$this->assertObjectHasAttribute(
			'eventStatus',
			$object,
			'Function modify_event did not add eventStatus.'
		);

		$this->assertEquals(
			JSON_LD::POSTPONED_SCHEMA,
			$object->eventStatus,
			'Function modify_event did not add POSTPONED_SCHEMA status.'
		);
	}
}
