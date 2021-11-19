<?php

namespace Tribe\Events\Event_Status;

class Event_StatusTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @test
	 */
	public function should_allow_enabling_event_status_w_a_filter() {
		add_filter( 'tec_event_status_enabled', '__return_true' );

		$this->assertTrue( Event_Status_Provider::is_active() );
	}

	/**
	 * @test
	 */
	public function should_allow_disabling_event_status_w_a_filter() {
		add_filter( 'tec_event_status_enabled', '__return_false' );

		$this->assertFalse( Event_Status_Provider::is_active() );
	}
}
