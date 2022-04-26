<?php

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Events\Test\Factories\Event;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\Traits\With_Context;

class RequestTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;
	use With_Context;

	public static $events = [];

	public function setUp(  ) {
		parent::setUp();
		tribe_singleton( 'context', new \Tribe__Context() );
		$this->backup_context();
		while ( tribe_events()->found() ) {
			tribe_events()->delete();
		}
	}

	public function tearDown() {
		parent::tearDown();
		if ( empty( static::$events ) ) {
			return;
		}

		$this->restore_context();
	}

	protected function create_and_get_generic_events() {
		static::$events = [];
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
		$events['this week sunday'] = tribe_events()->set_args( [
			'start_date' => 'next sunday',
			'timezone'   => 'Europe/Paris',
			'duration'   => 36 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - this week sunday 36 hours',
			'status'     => 'publish',
		] )->create();

		static::$events = $events;
		return static::$events;
	}


	protected function create_and_get_month_events() {
		static::$events = [];
		$featured = [
			date( 'Y-m-07' ),
		];

		foreach (
			[
				date( 'Y-m-28', strtotime( 'last month' ) ), // single
				date( 'Y-m-01' ), // single
				date( 'Y-m-07' ), // single (needs to be featured)
				date( 'Y-m-15' ), // single
				date( 'Y-m-01', strtotime( 'next month' ) ), // single
				date( 'Y-m-10', strtotime( 'next month' ) ), // single
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

		$events[ date( 'Y-m-18' ) ] = tribe_events()->set_args( [
			'start_date' => date( 'Y-m-18' ),
			'timezone'   => 'Europe/Paris',
			'duration'   => 28 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - ' . date( 'Y-m-18' ),
			'status'     => 'publish',
		] )->create();
		$events[ date( 'Y-m-21' ) ] = tribe_events()->set_args( [
			'start_date' => date( 'Y-m-21' ),
			'timezone'   => 'Europe/Paris',
			'duration'   => ( 24 * HOUR_IN_SECONDS ) - 1,
			'title'      => 'Test Event - ' . date( 'Y-m-21' ),
			'status'     => 'publish',
		] )->create();
		$events[ date( 'Y-m-25' ) ] = tribe_events()->set_args( [
			'start_date' => date( 'Y-m-25' ),
			'timezone'   => 'Europe/Paris',
			'duration'   => 36 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - ' . date( 'Y-m-25' ),
			'status'     => 'publish',
		] )->create();

		static::$events = $events;
		return static::$events;
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
					'this week sunday',
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
			/* Skipping for now as the iCalendar feed does not follow the month view list any more.
			'without_date_month_view' => [
				[
					'view' => 'month',
				],
				[], // This gets populated in the calling method below, since ical feeds for month defaults to the current day forward.
				'create_and_get_month_events',
			], */
			'with_date_month_view' => [
				[
					'view' => 'month',
					'event_date' => date( 'Y-m', strtotime( 'next month' ) ),
				],
				[
					date( 'Y-m-01', strtotime( 'next month' ) ),
					date( 'Y-m-10', strtotime( 'next month' ) ),
				],
				'create_and_get_month_events',
			],
			'without_date_featured_month_view' => [
				[
					'view' => 'month',
					'featured' => true,
				],
				[], // This gets populated in the calling method below, since ical feeds for month defaults to the current day forward.
				'create_and_get_month_events',
			],
			'with_date_featured_month_view' => [
				[
					'view' => 'month',
					'featured' => true,
					'event_date' => date( 'Y-m-10', strtotime( 'last month' ) ),
				],
				[],
				'create_and_get_month_events',
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
	public function it_should_render_based_on_context_arguments( $context_args, $expected_events_index, $method = 'create_and_get_generic_events' ) {
		// create the events.
		call_user_func_array( [ $this, $method ], [] );

		/**
		 * If we are looking at the month view ical data, the current month only grabs events for the current day forward.
		 * So we have to do some extra work to make sure we get the right events based on the current calendar day that the
		 * tests are being run vs the data that is being generated via the create_and_get_month_events() method.
		 */
		if (
			'month' === $context_args['view']
			&& empty( $context_args['event_date'] )
		) {
			$now = date( 'Y-m-d' );
			foreach ( static::$events as $date => $event ) {
				if ( $date < $now ) {
					continue;
				}

				if ( ! empty( $context_args['featured'] ) && empty( $event->featured ) ) {
					continue;
				}

				$expected_events_index[] = $date;
			}

			$expected_events_index = array_slice( $expected_events_index, 0, 4 );
		}

		$events_indexed = wp_list_pluck( static::$events, 'ID' );


		codecept_debug( $events_indexed );
		codecept_debug( $expected_events_index );


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

		$this->assertEqualsCanonicalizing( $expected_events, $event_ids );
	}

	/**
	 * It should return the single event request ID when creating export for single-event
	 *
	 * @test
	 */
	public function should_return_the_single_event_request_id_when_creating_export_for_single_event() {
		$post_id = ( new Event() )->create();
		$context = tribe_context()->alter( [
			'view'    => 'single-event',
			'post_id' => $post_id,
		] );

		$request   = $this->make_instance( $context );
		$event_ids = $request->get_event_ids();

		$this->assertEquals( [ $post_id ], $event_ids );
	}
}
