# Views v2 Integration Testing

## Scope of testing

The objective of this suite (integration testing for Views v2) is to test components (e.g. a datepicker), combination of components (e.g. the List View top navigation section), and whole Views (e.g. the whole List View) at an integration level.  

Where does that "integration" part come from?

It comes from the fact that we're running all the tests in the context of a full-blown WordPress installation.  
That installation, thanks to the suite configuration file in `tests/views_integration.suite.dist.yml`, includes our plugins and, with them, anyone of their dependencies.

## Types of tests in the suite

A test suite is just a convenient way to group tests that share a common setup (i.e. the modules and their configuration).  
Under the `views_integration` umbrella definition we store more than one type of testing.  
The good news (or the bad one, I'm not sure...) is anyone involved in the development of Views v2 **can and should contribute tests**.  

Without further ado, here are the main four types of testing you can find in this suite.  

### Data testing

This kind of tests deals with answering the question: 

> Is this View, or View moving part, fetching the correct data from the database?
> Is this View, or View moving part, providing data, to the code that must consume it, correctly?

Here's an example taken from the Month View test (trimmed down to show only the relevant parts):

```php
<?php

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Month_ViewTest extends ViewTestCase {

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $now->format( 'Y-m-d' ),
			]
		);
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class, $this->context );
    
		$this->assertEmpty( $month_view->found_post_ids() );
	}

	/**
	 * Test render with events
	 */
	public function test_render_with_events() {
		// Create some events starting from the fixed mock date.
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );
    
        // Create some events that will be available in the Month timeframe.
		$events    = array_map(
			static function ( $i ) use ( $now, $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
					]
				)->create();
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck( $events, 'ID' );

		/** @var Month_View $month_view */
		$month_view      = View::make( Month_View::class, $this->context );
		
        // Let's make sure the list of events in the whole month grid, a conflation of each day events, is correct.
		$this->assertEquals( $event_ids, $month_view->found_post_ids() );
    
        // Let's check, now, day by day.
		foreach ( $month_view->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				)
			);
		}
	}
}
```

What's missing from this tests? 
* there is no check on **how** the events are presented
* there is no check on the HTML structure

This test deals with a whole view, but we need to test other components that produce, or manipulate, data.  
To these we dedicate specific "WordPress unit" tests. As an example here the test for the multi-day stack used in the Month View (trimmed down for the sake of brevity):

```php
<?php

namespace Tribe\Views\V2\Utils;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Views\V2\Utils\Stack;

class StackTest extends \Codeception\TestCase\WPTestCase {
	public function _setUp() {
		parent::_setUp();
		static::factory()->event = new Event();
	}

	/**
	 * It should return an empty array provided an empty array
	 *
	 * @test
	 */
	public function should_return_an_empty_array_provided_an_empty_array() {
		$stack = new Stack();
		$this->assertEquals( [], $stack->build_from_events( [] ) );
	}

	public function stack_building_data_sets() {
		$scenarios = [
			'scenario_1' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23, 89 ],
					'2019-01-03' => [ 23, 89 ],
					'2019-01-04' => [ 23, 89 ],
					'2019-01-05' => [ 23 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, 89 ],
						'2019-01-05' => [ 23 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, 89 ],
						'2019-01-05' => [ 23 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, 89 ],
						'2019-01-05' => [ 23, '_' ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, 89 ],
						'2019-01-05' => [ 23, '_' ],
					],
				],
			],
            // [...]
		];

		$sets = [];
		foreach ( $scenarios as $scenario => $data ) {
			foreach ( $data['expected'] as $expected_key => $expected ) {
				$recycle                                 = 0 === strpos( $expected_key, 'w_recycle' );
				$normalize                               = false !== strpos( $expected_key, 'w_normalization' );
				$sets[ $scenario . '-' . $expected_key ] = [ $data['events_by_day'], $expected, $recycle, $normalize ];
			}
		}

		return $sets;
	}

	/**
	 * It should correctly build the stack when not recycling space and not normalizing
	 *
	 * @test
	 * @dataProvider stack_building_data_sets
	 */
	public function should_correctly_build_the_stack( $events_by_day, $expected, $recycle, $normalize ) {
		add_filter( 'tribe_events_views_v2_stack_recycle_spaces', '__return_false' );
		add_filter( 'tribe_events_views_v2_stack_normalize', '__return_false' );
		// All events should make it into the stack.
		add_filter(
			'tribe_events_views_v2_stack_events',
			static function ( array $filtered, array $events ) {
				return $events;
			},
			10,
			2
		);

		$this->mock_events_in_cache(
			[ 23, 89, 2389, 1317 ],
			static::factory()->event->create_many( 4, [ 'time_space' => 24 ] )
		);

		$stack  = new Stack();
		$s      = '_';
		$actual = $stack->build_from_events( $events_by_day, $s, $recycle, $normalize );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * It should fill missing days
	 *
	 * @test
	 */
	public function should_fill_missing_days() {
		$spacer = '__spacer__';
		// All events will be part of the stack.
		add_filter(
			'tribe_events_views_v2_stack_events',
			static function ( array $filtered, array $events ) {
				return $events;
			},
			10,
			2
		);
		$this->mock_events_in_cache(
			[
				23,
				89,
			],
			static::factory()->event->create_many( 2, [ 'time_space' => 24 ] )
		);

		$stack        = new Stack();
		$output_stack = $stack->build_from_events(
			[
				'2019-01-01' => [ 23 ],
				// 2019-01-02 is missing.
				'2019-01-03' => [ 89 ],
			],
			$spacer,
			true,
			true
		);

		$expected_stack = [
			'2019-01-01' => [ 23 ],
			'2019-01-02' => [ $spacer ],
			'2019-01-03' => [ 89 ],
		];
		$this->assertEquals( $expected_stack, $output_stack );
	}

	protected function mock_events_in_cache( array $event_ids, $mock_events ) {
		if ( is_array( $mock_events ) ) {
			if ( count( $mock_events ) !== count( $event_ids ) ) {
				throw new \InvalidArgumentException(
					'The number of events to mock and those to replace should be the same.'
				);
			}
		} else {
			$mock_events = array_fill( 0, count( $event_ids ), $mock_events );
		}

		$iterator = new \MultipleIterator();
		$iterator->attachIterator( new \ArrayIterator( $event_ids ) );
		$iterator->attachIterator( new \ArrayIterator( $mock_events ) );

		foreach ( $iterator as list( $id, $mock_event ) ) {
			wp_cache_set( $id, get_post( $mock_event ), 'posts' );
		}
	}
}
```

This second test example shows what is, probably, the main feature of data-driven testing:

* we create real events to write them
* we manipulate data as the full View, in production, would do

### Snapshot testing
@todo @juanfra

### Component (HTML) Testing
@todo @paulmskim

### Other Testing
@todo @bordoni
