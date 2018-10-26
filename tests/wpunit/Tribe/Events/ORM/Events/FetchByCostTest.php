<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

class FetchByCostTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * An array of arrays of event IDs categorized by their post meta.
	 *
	 * @var array
	 */
	protected $events = [];

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
	}

	/**
	 * It should allow fetching events by cost meta
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_cost_meta() {
		$this->create_test_events( 4.5, 1.5 );

		$this->assertEqualSets(
			array_merge( $this->events['without_cost'], $this->events['with_cost'] ),
			tribe_events()->per_page( - 1 )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_equal_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5 )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_equal_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5, '=' )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_lt_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5, '<' )->get_ids()
		);
		$this->assertEqualSets( array_merge(
			$this->events['with_equal_cost'],
			$this->events['with_lt_cost'] ),
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5, '<=' )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_gt_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5, '>' )->get_ids()
		);
		$this->assertEqualSets( array_merge(
			$this->events['with_equal_cost'],
			$this->events['with_gt_cost'] ),
			tribe_events()->per_page( - 1 )->where( 'cost', 4.5, '>=' )->get_ids()
		);
	}

	/**
	 * Creates an event without cost meta and a number of events with different costs
	 * and symbols.
	 *
	 * @param float $cost_pivot The cost pivot.
	 * @param float $cost_delta The cost delta.
	 */
	protected function create_test_events( float $cost_pivot = 3, float $cost_delta = 1 ) {
		$this->events['without_cost'] = [ $this->factory()->event->create() ];
		$this->events['with_cost']    = $this->create_events_with_costs( [
			'usd_symbol'    => '$',
			'usd_iso_code'  => 'USD',
			'cad_symbol'    => '$',
			'cad_iso_code'  => 'CAD',
			'euro_symbol'   => '€',
			'euro_iso_code' => 'EUR',
			'foo_symbol'    => 'ƒ',
			'foo_iso_code'  => 'FOO',
		], $cost_pivot, $cost_delta );

		$this->events['with_equal_cost'] = array_filter( $this->events['with_cost'], function ( $event_id ) use ( $cost_pivot ) {
			return (float) get_post_meta( $event_id, '_EventCost', true ) === $cost_pivot;
		} );
		$this->events['with_lt_cost']    = array_filter( $this->events['with_cost'], function ( $event_id ) use ( $cost_pivot ) {
			return (float) get_post_meta( $event_id, '_EventCost', true ) < $cost_pivot;
		} );
		$this->events['with_gt_cost']    = array_filter( $this->events['with_cost'], function ( $event_id ) use ( $cost_pivot ) {
			return (float) get_post_meta( $event_id, '_EventCost', true ) > $cost_pivot;
		} );

		$this->events['with_dollar_symbol'] = array_filter( $this->events['with_cost'], function ( $event_id ) {
			return get_post_meta( $event_id, '_EventCurrencySymbol', true ) === '$';
		} );
		$this->events['with_usd_symbol']    = array_filter( $this->events['with_cost'], function ( $event_id ) {
			return get_post_meta( $event_id, '_EventCurrencySymbol', true ) === 'USD';
		} );
		$this->events['with_cad_symbol']    = array_filter( $this->events['with_cost'], function ( $event_id ) {
			return get_post_meta( $event_id, '_EventCurrencySymbol', true ) === 'CAD';
		} );
		$this->assertEmpty( array_intersect(
				$this->events['with_equal_cost'],
				$this->events['with_lt_cost'],
				$this->events['with_gt_cost'] )
		);
		$this->assertEmpty( array_intersect(
				$this->events['with_dollar_symbol'],
				$this->events['with_usd_symbol'],
				$this->events['with_cad_symbol'] )
		);
	}

	/**
	 * Returns a map in the shape [ <name> => <ID> ] relating names to event IDs.
	 *
	 * Given an input `$names_and_symbols` like [ 'usd_symbol' => '$', 'usd_iso_symbol'=> 'USD' ]
	 * and a `$cost_pivot` of 3 the function will generate prefix and suffix events with costs below,
	 * equals and greater than the cost pivot:
	 *      [
	 *          'usd_symbol_prefix_lt' => <ID>,
	 *          'usd_symbol_prefix_eq' => <ID>,
	 *          'usd_symbol_prefix_gt' => <ID>,
	 *          'usd_symbol_suffix_lt' => <ID>,
	 *          'usd_symbol_suffix_eq' => <ID>,
	 *          'usd_symbol_suffix_gt' => <ID>,
	 *          'usd_iso_symbol_prefix_lt' => <ID>,
	 *          'usd_iso_symbol_prefix_eq' => <ID>,
	 *          'usd_iso_symbol_prefix_gt' => <ID>,
	 *          'usd_iso_symbol_suffix_lt' => <ID>,
	 *          'usd_iso_symbol_suffix_eq' => <ID>,
	 *          'usd_iso_symbol_suffix_gt' => <ID>,
	 *      ]
	 *
	 * @param array $names_and_symbols An array relating the name base to the symbol that will be set
	 *                                 for the event cost.
	 * @param float $cost_pivot        The cost that will be used to create the tickets.
	 * @param float $cost_delta        The delta that will be used to create events with costs less than and
	 *                                 greater than the cost pivot.
	 *
	 * @return array An event name to event post ID map ready to be `extract`ed.
	 *
	 * @throws \InvalidArgumentException If the cost pivot is less than the cost delta; negative cost tickets do not
	 *                                   make sense.
	 */
	protected function create_events_with_costs( array $names_and_symbols, float $cost_pivot, float $cost_delta = 2 ): array {
		if ( $cost_pivot < $cost_delta ) {
			throw new \InvalidArgumentException( 'The "cost_pivot" should be greater than or equal to 2.' );
		}

		$positions = [ 'prefix', 'suffix' ];
		$costs     = [
			'lt' => $cost_pivot - $cost_delta,
			'eq' => $cost_pivot,
			'gt' => $cost_pivot + $cost_delta
		];

		$events = [];
		foreach ( $names_and_symbols as $name => $symbol ) {
			foreach ( $positions as $position ) {
				foreach (
					$costs as $cost_name => $cost
				) {
					$this_name            = sprintf( '%s_%s_%s', $name, $position, $cost_name );
					$events[ $this_name ] = $this->factory()->event->create( [
						'meta_input' => [
							'_EventCurrencySymbol'   => $symbol,
							'_EventCurrencyPosition' => $position,
							'_EventCost'             => $cost,
						],
					] );
				}
			}
		}

		return $events;
	}

	/**
	 * It should support currency symbol based search
	 *
	 * @test
	 */
	public function should_support_currency_symbol_based_search() {
		$this->create_test_events( 3 );

		$this->assertEqualSets(
			array_intersect( $this->events['with_equal_cost'], $this->events['with_dollar_symbol'] ),
			tribe_events()->per_page( - 1 )->where( 'cost', 3, '=', '$' )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect( $this->events['with_equal_cost'], array_merge( $this->events['with_dollar_symbol'], $this->events['with_usd_symbol'] ) ),
			tribe_events()->per_page( - 1 )->where( 'cost', 3, '=', [ '$', 'USD' ] )->get_ids()
		);
	}

	/**
	 * It should allow fetching events by cost between
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_cost_between() {
		$this->create_test_events( 4.5, 1.5 );

		$this->assertEqualSets(
			$this->events['with_equal_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost_between', 4, 5 )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_equal_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost_between', 3.1, 5.9 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge(
				$this->events['with_lt_cost'],
				$this->events['with_equal_cost']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_between', 3, 5.9 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge(
				$this->events['with_equal_cost'],
				$this->events['with_gt_cost']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_between', 3.1, 6 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge(
				$this->events['with_lt_cost'],
				$this->events['with_equal_cost'],
				$this->events['with_gt_cost']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_between', 3, 6 )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect(
				$this->events['with_cad_symbol'],
				array_merge(
					$this->events['with_lt_cost'],
					$this->events['with_equal_cost'],
					$this->events['with_gt_cost']
				)
			),
			tribe_events()->per_page( - 1 )->where( 'cost_between', 3, 6, 'CAD' )->get_ids()
		);
	}

	/**
	 * It should allow filtering events by cost less than and greater than
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_cost_less_than_and_greater_than() {
		$this->create_test_events( 3, 1.5 );

		$this->assertEqualSets(
			$this->events['with_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost_greater_than', 0 )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect(
				$this->events['with_cost'],
				$this->events['with_usd_symbol']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_greater_than', 0, 'USD' )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_gt_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost_greater_than', 3 )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect(
				$this->events['with_gt_cost'],
				$this->events['with_usd_symbol']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_greater_than', 3, 'USD' )->get_ids()
		);

		$this->assertEmpty(
			tribe_events()->per_page( - 1 )->where( 'cost_less_than', 0 )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect(
				$this->events['with_cost'],
				$this->events['with_usd_symbol']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_less_than', 10, 'USD' )->get_ids()
		);
		$this->assertEqualSets(
			$this->events['with_lt_cost'],
			tribe_events()->per_page( - 1 )->where( 'cost_less_than', 3 )->get_ids()
		);
		$this->assertEqualSets(
			array_intersect(
				$this->events['with_lt_cost'],
				$this->events['with_usd_symbol']
			),
			tribe_events()->per_page( - 1 )->where( 'cost_less_than', 3, 'USD' )->get_ids()
		);
	}
}
