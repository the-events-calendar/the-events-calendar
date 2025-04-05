<?php

namespace TEC\Events\Custom_Tables\V1\Views;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility;

class By_Day_View_Compatibility_Test extends WPTestCase {
	public function test_prepare_day_results(): void {
		// Set a batch size of 4..
		add_filter( 'tec_events_query_batch_size', fn() => 4 );
		// Create a set of events.
		$ids = [];
		foreach ( range( 1, 7 ) as $k ) {
			$ids[] = tribe_events()->set_args( [
				'title'      => 'Event ' . $k,
				'start_date' => "+$k days",
				'duration'   => 3 * HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )->create()->ID;
			// Fetch the post meta now to cache it and not run a query later.
			get_post_meta(end($ids));
		}

		$by_day_view_compatibility = new By_Day_View_Compatibility();
		$by_day_view_compatibility->prepare_day_results( $ids );

		$this->queries()->assertQueriesCountByMethod(
			4,
			By_Day_View_Compatibility::class,
			'prepare_day_results',
			'There should be 4 queries: 2 to select, 2 to count.'
		);
	}
}
