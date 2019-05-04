<?php

namespace Tribe\Events\Views\V2\Query;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Traits\FilterRecorder;

class MainQueryPostsInjectionTest extends \Codeception\TestCase\WPTestCase {
	use FilterRecorder;

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * It should exclude v1 query filters completely when Views v2 are active
	 *
	 * @test
	 */
	public function should_exclude_v_1_query_filters_completely_when_views_v_2_are_active() {
		$this->record_filter_callbacks();

		$query      = new \WP_Query();
		$query->get_posts( [ 'post_type' => 'tribe_events' ] );

		$v1_filters = $this->get_recorded_filter_callbacks_containing( '/Tribe__Events__(Query|Main)/' );
		$this->assertEmpty(
			$v1_filters,
			'v1 filters are not expected to apply to the query, the following applied: '
			. json_encode( $v1_filters, JSON_PRETTY_PRINT )
		);
	}
}
