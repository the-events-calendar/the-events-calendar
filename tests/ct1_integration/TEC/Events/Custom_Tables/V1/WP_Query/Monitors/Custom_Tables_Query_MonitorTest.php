<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Monitors;

use Tribe\Tests\Traits\With_Uopz;

class Custom_Tables_Query_MonitorTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function setUp() {
		parent::setUp();
		tribe()->singleton( 'ct_query_monitor_test', Custom_Tables_Query_Monitor::class );
	}

	public function tearDown() {
		parent::tearDown();
		tribe()->offsetUnset( 'ct_query_monitor_test' );
	}

	/**
	 * Validate the query modifier implementation filter works properly.
	 *
	 * @test
	 */
	public function should_correctly_filter_modifier_implementations() {
		add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', function ( array $implementations ) {
			// dupe to test filtering
			$implementations[] = 'Duplicate';
			$implementations[] = 'Duplicate';

			return $implementations;
		}, 2, 999 );
		$implementations = tribe( 'ct_query_monitor_test' )->get_implementations();
		// Our faux modifier in the array?
		$this->assertTrue( in_array( 'Duplicate', $implementations ) );
		// Did we dedupe it?
		$this->assertCount( count( array_unique( $implementations ) ), $implementations );
	}

	/**
	 * Will memoize the results of the modifier implementation filter.
	 *
	 * @test
	 */
	public function should_memoize_filter_for_modifier_implementations(): void {
		add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', function ( array $implementations ) {
			$implementations[] = 'Here';

			return $implementations;
		}, 2, 999 );
		// Memoizes the list.
		tribe( 'ct_query_monitor_test' )->get_implementations();
		add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations', function ( array $implementations ) {
			$implementations[] = 'Not here';

			return $implementations;
		}, 2, 999 );
		// Will not get new filters.
		$implementations = tribe( 'ct_query_monitor_test' )->get_implementations();
		$this->assertTrue( in_array( 'Here', $implementations ) );
		$this->assertFalse( in_array( 'Not here', $implementations ) );
	}

	public function run_once_data_provider(): \Generator {
		yield 'before init, not doing init, never filtered' => [
			function () {
				$this->set_fn_return( 'did_action', function ( string $action ) {
					return $action === 'init' ? 0 : did_action( $action );
				}, true );
				$this->set_fn_return( 'doing_action', function ( string $action ) {
					return $action === 'init' ? false : doing_action( $action );
				}, true );

				return [ false, true ];
			}
		];

		yield 'before init, doing init, already filtered' => [
			function () {
				$this->set_fn_return( 'did_action', function ( string $action ) {
					return $action === 'init' ? 0 : did_action( $action );
				}, true );
				$this->set_fn_return( 'doing_action', function ( string $action ) {
					return $action === 'init' ? false : doing_action( $action );
				}, true );


				return [ true, true ];
			}
		];

		yield 'after init, never filtered' => [
			function () {
				$this->set_fn_return( 'did_action', function ( string $action ) {
					return $action === 'init' ? 1 : did_action( $action );
				}, true );
				$this->set_fn_return( 'doing_action', function ( string $action ) {
					return $action === 'init' ? false : doing_action( $action );
				}, true );


				return [ false, true ];
			}
		];

		yield 'after init, already filtered' => [
			function () {
				$this->set_fn_return( 'did_action', function ( string $action ) {
					return $action === 'init' ? 1 : did_action( $action );
				}, true );
				$this->set_fn_return( 'doing_action', function ( string $action ) {
					return $action === 'init' ? false : doing_action( $action );
				}, true );


				return [ true, false ];
			}
		];
	}

	/**
	 * It should filter implementations at least once
	 *
	 * @test
	 * @dataProvider run_once_data_provider
	 */
	public function should_filter_implementations_at_least_once( \Closure $fixture ): void {
		[ $has_filtered, $expected ] = $fixture();

		$monitor = new Custom_Tables_Query_Monitor();
		if ( $has_filtered ) {
			$monitor->get_implementations();
		}

		$did_filter = false;
		add_filter( 'tec_events_custom_tables_v1_query_modifier_implementations',
			static function ( array $implementations ) use ( &$did_filter ) {
				$did_filter = true;

				return $implementations;
			}
		);

		$monitor->get_implementations();

		$this->assertEquals( $expected, $did_filter );
	}
}
