<?php

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

class Event_ReportTest extends \Codeception\TestCase\WPTestCase {
	use CT1_Fixtures;

	/**
	 * It should store default weight correctly
	 *
	 * @test
	 */
	public function should_store_default_weight_correctly() {
		$passing = $this->given_a_non_migrated_single_event();
		$failing = $this->given_a_non_migrated_single_event();

		$event_report = new Event_Report( $passing );
		$event_report->migration_success();
		$event_report = new Event_Report( $failing );
		$event_report->migration_failed( 'for reasons' );

		$failed_weight  = get_post_meta( $failing->ID, Event_Report::META_KEY_ORDER_WEIGHT, true );
		$passing_weight = get_post_meta( $passing->ID, Event_Report::META_KEY_ORDER_WEIGHT, true );
		$this->assertLessThan( $failed_weight, $passing_weight );
	}

	/**
	 * It should allow filtering the weight map and elements
	 *
	 * @test
	 */
	public function should_allow_filtering_the_weight_map_and_elements() {
		$event = $this->given_a_non_migrated_single_event();
		add_filter( 'tec_events_custom_tables_v1_event_report_element_weights', function ( $report_weights ) {
			return array_merge( $report_weights, [
				'foo' => 0,
				'bar' => 1,
				'baz' => 1
			] );
		} );
		add_filter( 'tec_events_custom_tables_v1_event_report_weights_map', function ( $map ) {
			return [
				'foo' => 10 ** 7,
				'bar' => 10 ** 8,
				'baz' => 10 ** 9,
			];
		} );

		$event_report = new Event_Report( $event );
		$event_report->migration_failed( 'for reasons' );

		$this->assertEquals( 10 ** 8 + 10 ** 9, get_post_meta( $event->ID, Event_Report::META_KEY_ORDER_WEIGHT, true ) );
	}
}
