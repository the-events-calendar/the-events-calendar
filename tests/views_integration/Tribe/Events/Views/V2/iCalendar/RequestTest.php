<?php

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Date_Utils as Dates;

class RequestTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;

	public static $events = [];

	public static function wpSetUpBeforeClass() {
		foreach (
			[
				'-49 days', // single
				'yesterday 9am', // single
				'tomorrow 9am', // single (needs to be featured)
				'+4 days', // single
				'+49 days', // single
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_date,
				'status'     => 'publish',
				'featured'   => 'tomorrow 9am' === $start_date ? 1 : 0,
			] )->create();
		}

		$events[] = tribe_events()->set_args( [
			'start_date' => '+2 day',
			'timezone'   => 'Europe/Paris',
			'duration'   => 28 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - +2 day',
			'status'     => 'publish',
		] )->create();
		$events[] = tribe_events()->set_args( [
			'start_date' => '+3 days',
			'timezone'   => 'Europe/Paris',
			'duration'   => ( 24 * HOUR_IN_SECONDS ) - 1,
			'title'      => 'Test Event - +3 days',
			'status'     => 'publish',
		] )->create();
		$events[] = tribe_events()->set_args( [
			'start_date' => 'yesterday',
			'timezone'   => 'Europe/Paris',
			'duration'   => 36 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - yesterday 36hours',
			'status'     => 'publish',
		] )->create();

		static::$events = $events;
	}

	/**
	 * @return Request
	 */
	private function make_instance( $context ) {
		return new Request( $context );
	}

	public function request_context_data() {
		$data = [
			'without_date_list_view' => [
				[
					'view' => 'list',
				],
				[ 2, 5, 6, 3 ] // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_list_view' => [
				[
					'view' => 'list',
					'event_date' => date( 'Y-m-d', strtotime( '+2 days' ) ),
				],
				[ 5, 6, 3, 4 ]
			],
			'without_date_featured_list_view' => [
				[
					'view' => 'list',
					'featured' => true,
				],
				[ 2 ] // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_featured_list_view' => [
				[
					'view' => 'list',
					'featured' => true,
					'event_date' => date( 'Y-m-d', strtotime( '+2 days' ) ),
				],
				[]
			],
		];

		return $data;
	}

	/**
	 * @dataProvider request_context_data
	 * @test
	 */
	public function it_should_render_based_on_context_arguments( $context_args, $expected_events_index ) {
		$events_indexed = wp_list_pluck( static::$events, 'ID' );
		$dates = wp_list_pluck( static::$events, 'start_date' );
		codecept_debug( $events_indexed );
		codecept_debug( $dates );

		add_filter( 'tribe_ical_feed_posts_per_page', static function () {
			return 4;
		} );

		// Overwrite the context.
		$context = tribe_context()->alter( $context_args );

		$request   = $this->make_instance( $context );
		$event_ids = $request->get_event_ids();

		$expected_events = [];
		foreach ( $expected_events_index as $event_index ) {
			$expected_events[] = $events_indexed[ $event_index ];
		}

		$this->assertEquals( $expected_events, $event_ids );
	}
}
