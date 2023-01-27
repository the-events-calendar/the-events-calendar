<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Monitors;


class Custom_Tables_Query_MonitorTest extends \Codeception\TestCase\WPTestCase {

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
	public function should_memoize_filter_for_modifier_implementations() {
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
}
