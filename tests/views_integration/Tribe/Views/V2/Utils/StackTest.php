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
			'scenario_2' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23, 89 ],
					'2019-01-03' => [ 23, 89 ],
					'2019-01-04' => [ 23, 2389, 1317 ],
					'2019-01-05' => [ 23, 2389, 1317 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, '_', 2389, 1317 ],
						'2019-01-05' => [ 23, '_', 2389, 1317 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 23, 89 ],
						'2019-01-04' => [ 23, 2389, 1317 ],
						'2019-01-05' => [ 23, 2389, 1317 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23, '_', '_' ],
						'2019-01-02' => [ 23, 89, '_' ],
						'2019-01-03' => [ 23, 89, '_' ],
						'2019-01-04' => [ 23, 2389, 1317 ],
						'2019-01-05' => [ 23, 2389, 1317 ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_', '_', '_' ],
						'2019-01-02' => [ 23, 89, '_', '_' ],
						'2019-01-03' => [ 23, 89, '_', '_' ],
						'2019-01-04' => [ 23, '_', 2389, 1317 ],
						'2019-01-05' => [ 23, '_', 2389, 1317 ],
					],
				],
			],
			'scenario_3' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23, 89 ],
					'2019-01-03' => [ 89, 2389 ],
					'2019-01-04' => [ 2389, 1317 ],
					'2019-01-05' => [ 1317 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ '_', 89, 2389 ],
						'2019-01-04' => [ '_', '_', 2389, 1317 ],
						'2019-01-05' => [ '_', '_', '_', 1317 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 2389, 89 ],
						'2019-01-04' => [ 2389, 1317 ],
						'2019-01-05' => [ '_', 1317 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 89 ],
						'2019-01-03' => [ 2389, 89 ],
						'2019-01-04' => [ 2389, 1317 ],
						'2019-01-05' => [ '_', 1317 ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_', '_', '_' ],
						'2019-01-02' => [ 23, 89, '_', '_' ],
						'2019-01-03' => [ '_', 89, 2389, '_' ],
						'2019-01-04' => [ '_', '_', 2389, 1317 ],
						'2019-01-05' => [ '_', '_', '_', 1317 ],
					],
				],
			],
			'scenario_4' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23 ],
					'2019-01-03' => [ 2389 ],
					'2019-01-04' => [ 2389 ],
					'2019-01-05' => [ 2389 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23 ],
						'2019-01-03' => [ 2389 ],
						'2019-01-04' => [ 2389 ],
						'2019-01-05' => [ 2389 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23 ],
						'2019-01-03' => [ 2389 ],
						'2019-01-04' => [ 2389 ],
						'2019-01-05' => [ 2389 ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, '_' ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
				],
			],
			'scenario_5' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23, 2389 ],
					'2019-01-03' => [ 2389 ],
					'2019-01-04' => [ 2389 ],
					'2019-01-05' => [ 2389 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389 ],
						'2019-01-05' => [ '_', 2389 ],
					],
				],
			],

			'scenario_6' => [
				'events_by_day' => [
					'2019-01-01' => [ 23 ],
					'2019-01-02' => [ 23, 2389 ],
					'2019-01-03' => [ 2389 ],
					'2019-01-04' => [ 2389, 89 ],
					'2019-01-05' => [ 2389, 89 ],
				],
				'expected'      => [
					'wo_recycle_wo_normalization' => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ '_', 2389, 89 ],
						'2019-01-05' => [ '_', 2389, 89 ],
					],
					'w_recycle_wo_normalization'  => [
						'2019-01-01' => [ 23 ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ 89, 2389 ],
						'2019-01-05' => [ 89, 2389 ],
					],
					'w_recycle_w_normalization'   => [
						'2019-01-01' => [ 23, '_' ],
						'2019-01-02' => [ 23, 2389 ],
						'2019-01-03' => [ '_', 2389 ],
						'2019-01-04' => [ 89, 2389 ],
						'2019-01-05' => [ 89, 2389 ],
					],
					'wo_recycle_w_normalization'  => [
						'2019-01-01' => [ 23, '_', '_' ],
						'2019-01-02' => [ 23, 2389, '_' ],
						'2019-01-03' => [ '_', 2389, '_' ],
						'2019-01-04' => [ '_', 2389, 89 ],
						'2019-01-05' => [ '_', 2389, 89 ],
					],
				],
			],
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
