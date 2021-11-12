<?php

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

class Service_ProviderTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	/**
	 * @test
	 *
	 * tests src/Tribe/Event_Status/Compatibility/Filter_Bar/Service_Provider->filter_context_locations()
	 */
	public function it_should_add_event_status_to_context_locations() {
		// Start with a blank slate.
		$locations = apply_filters( 'tribe_context_locations', [] );

		// This will get filtered by other things, so let's just test for subsets.
		$events_event_status_data =  [
			'events_status_data' => [
				'read' => [
					'request_var' => [
						'tribe-events-status'
					]
				]
			]
		];
		$this->assertArraySubset( $events_event_status_data, $locations );
	}
}
