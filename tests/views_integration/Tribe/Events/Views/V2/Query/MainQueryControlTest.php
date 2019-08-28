<?php

namespace Tribe\Events\Views\V2\Query;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Traits\FilterRecorder;

class MainQueryControlTest extends \Codeception\TestCase\WPTestCase {

	use FilterRecorder;

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
		$this->assertTrue( tribe_events_views_v2_is_enabled() );
	}

	/**
	 * It should not fire any filter when no main query request is made
	 *
	 * @test
	 */
	public function should_not_fire_any_filter_when_no_main_query_request_is_made() {
		$this->record_filter_callbacks();

		$v1_filters = $this->get_recorded_filter_callbacks_containing( '/Tribe__Events__(Query|Main|Backcompat)/' );
		$this->assertEmpty(
			$v1_filters,
			'Views v1 filters are not expected to apply when no main query request is done, these applied: '
			. json_encode( $v1_filters, JSON_PRETTY_PRINT )
		);
	}

	/**
	 * It should exclude v1 main query filters from /events page when v2 is active
	 *
	 * @test
	 */
	public function should_exclude_v_1_main_query_filters_from_events_page_when_v_2_is_active() {
		// @todo @be fix this and do not skip it.
		$this->markTestSkipped('Passing when ran alone, not when in test group.');
		$this->record_filter_callbacks();

		$this->go_to( '/events' );

		$v1_filters = $this->get_recorded_filter_callbacks_containing( '/Tribe__Events__(Query|Main|Backcompat)/' );
		$this->assertEmpty(
			$v1_filters,
			'Views v1 filters are not expected to apply to /events main query, these applied: '
			. json_encode( $v1_filters, JSON_PRETTY_PRINT )
		);
	}
}
