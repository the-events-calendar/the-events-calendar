<?php

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

class Service_ProviderTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
	}

	/**
	 * @test
	 *
	 * tests src/Tribe/Compatibility/Filter_Bar/Service_Provider->filter_context_locations()
	 */
	public function it_should_add_virtual_to_context_locations() {
		// Start with a blank slate.
		$locations = apply_filters( 'tribe_context_locations', [] );

		// This will get filtered by other things, so let's just test for subsets.
		$events_virtual_data =  [
			'events_virtual_data' => [
				'read' => [
					'request_var' => [
						'tribe-events-virtual'
					]
				]
			]
		];
		$this->assertArraySubset( $events_virtual_data, $locations );

		$events_virtual_request = [
			'events_virtual_request' => [
				'read' => [
					'request_var' => [
						'ev_request',
						'state',
					]
				]
			]
		];
		$this->assertArraySubset( $events_virtual_request, $locations );

		$virtual = [
			'virtual' => [
				'read' => [
					'request_var' =>  [ 'virtual' ],
					'query_var'   => [ 'virtual' ],
				]
			]
		];
		$this->assertArraySubset( $virtual, $locations );
	}
}
