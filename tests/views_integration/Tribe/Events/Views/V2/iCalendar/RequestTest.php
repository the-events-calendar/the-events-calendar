<?php

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

class RequestTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;

	public static $events = [];

	public static function wpSetUpBeforeClass() {
		$featured = [
			'today',
			'next week monday',
		];

		foreach (
			[
				'-49 days', // single
				'last week monday', // single
				'next week monday', // single (needs to be featured)
				'next week thursday', // single
				'+49 days', // single
				'today',
			] as $start_date
		) {
			$events[ $start_date ] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_date,
				'status'     => 'publish',
				'featured'   => in_array( $start_date, $featured ),
			] )->create();
		}

		$events[ 'next week tuesday' ] = tribe_events()->set_args( [
			'start_date' => 'next week tuesday',
			'timezone'   => 'Europe/Paris',
			'duration'   => 28 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - next tuesday',
			'status'     => 'publish',
		] )->create();
		$events[ 'next week wednesday' ] = tribe_events()->set_args( [
			'start_date' => 'next week wednesday',
			'timezone'   => 'Europe/Paris',
			'duration'   => ( 24 * HOUR_IN_SECONDS ) - 1,
			'title'      => 'Test Event - next wednesday',
			'status'     => 'publish',
		] )->create();
		$events[ 'next sunday' ] = tribe_events()->set_args( [
			'start_date' => 'next sunday',
			'timezone'   => 'Europe/Paris',
			'duration'   => 36 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - next sunday 36 hours',
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
				[
					'next sunday',
					'next week monday',
					'next week tuesday',
					'next week wednesday',
				], // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_list_view' => [
				[
					'view' => 'list',
					'event_date' => date( 'Y-m-d', strtotime( 'next week tuesday' ) ),
				],
				[
					'next week tuesday',
					'next week wednesday',
					'next week thursday',
					'+49 days',
				], // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'without_date_featured_list_view' => [
				[
					'view' => 'list',
					'featured' => true,
				],
				[ 'next week monday' ] // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_featured_list_view' => [
				[
					'view' => 'list',
					'featured' => true,
					'event_date' => date( 'Y-m-d', strtotime( 'next week tuesday' ) ),
				],
				[]
			],
			'without_date_day_view' => [
				[
					'view' => 'day',
				],
				[
					'today',
				], // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_day_view' => [
				[
					'view' => 'day',
					'event_date' => date( 'Y-m-d', strtotime( 'next week tuesday' ) ),
				],
				[
					'next week tuesday',
				], // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'without_date_featured_day_view' => [
				[
					'view' => 'day',
					'featured' => true,
				],
				[ 'today' ] // @todo @bordoni Take care of multi-day on the bleeding edges of the event date filtering.
			],
			'with_date_featured_day_view' => [
				[
					'view' => 'day',
					'featured' => true,
					'event_date' => date( 'Y-m-d', strtotime( 'next week tuesday' ) ),
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
