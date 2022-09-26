<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use Closure;
use Generator;
use Tribe__Events__Main as TEC;

class EventsTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @before
	 */
	public function register_temp_post_type(): void {
		register_post_type( '__temp__' );
	}

	private function update_post_type( int $id ): int {
		global $wpdb;
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_type = %s WHERE ID = %d",
				TEC::POSTTYPE,
				$id
			) );

		if ( $updated !== 1 ) {
			throw new \RuntimeException( 'Failed to update post type' );
		}

		return $id;
	}

	private function create_event( array $postarr ): int {
		return $this->update_post_type( static::factory()->post->create( array_merge( [
			'post_type' => '__temp__',
		], $postarr ) ) );
	}


	public function total_events_data_provider(): Generator {
		$fixture = static function () {
		};
		yield 'no events' => [ $fixture, 0 ];

		// Events are created using a different post type to avoid triggering Event-related filters and actions.

		$good_event_w_all_information = function ( array $postarr = [] ): int {
			return $this->create_event( array_merge( [
				'post_title' => 'Good Event with all information',
				'meta_input' => [
					'_EventStartDate'    => '2019-01-01 10:00:00',
					'_EventEndDate'      => '2019-01-01 11:00:00',
					'_EventStartDateUTC' => '2019-01-01 15:00:00',
					'_EventEndDateUTC'   => '2019-01-01 16:00:00',
					'_EventTimezone'     => 'America/New_York',
				]
			], $postarr ) );
		};

		$setup_event_missing_meta = function ( array $missing_meta, array $postarr = [] ): int {
			return $this->create_event( array_merge( [
				'post_title' => 'Event missing some meta',
				'meta_input' => array_diff_key( [
					'_EventStartDate'    => '2019-01-01 10:00:00',
					'_EventEndDate'      => '2019-01-01 11:00:00',
					'_EventStartDateUTC' => '2019-01-01 15:00:00',
					'_EventEndDateUTC'   => '2019-01-01 16:00:00',
					'_EventTimezone'     => 'America/New_York',
				], array_combine( $missing_meta, $missing_meta ) )
			], $postarr ) );
		};

		$setup_fixture = $good_event_w_all_information;
		yield 'one good event with all information' => [ $setup_fixture, 1 ];

		$setup_fixture = static function () use ( $good_event_w_all_information ) {
			$good_event_w_all_information();
			$good_event_w_all_information();
			$good_event_w_all_information();
		};
		yield 'many events with all information' => [ $setup_fixture, 3 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta, $good_event_w_all_information ) {
			$good_event_w_all_information();
			$setup_event_missing_meta( [ '_EventStartDate' ] );
			$setup_event_missing_meta( [ '_EventStartDateUTC' ] );
			$setup_event_missing_meta( [ '_EventEndDate' ] );
			$setup_event_missing_meta( [ '_EventEndDateUTC' ] );
		};
		yield 'some event with partial and working information' => [ $setup_fixture, 5 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta, $good_event_w_all_information ) {
			$good_event_w_all_information();
			$good_event_w_all_information();
			$setup_event_missing_meta( [ '_EventStartDate', '_EventStartDateUTC' ] );
			$setup_event_missing_meta( [ '_EventEndDate', '_EventEndDateUTC' ] );
		};
		yield 'some event missing information completely' => [ $setup_fixture, 2 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta, $good_event_w_all_information ) {
			$good_event_w_all_information( [ 'post_status' => 'draft' ] );
			$good_event_w_all_information( [ 'post_status' => 'trash' ] );
			$good_event_w_all_information( [ 'post_status' => 'pending' ] );
		};
		yield 'event with good information in diff. stati' => [ $setup_fixture, 3 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta ) {
			$setup_event_missing_meta( [ '_EventStartDate', ], [ 'post_status' => 'draft' ] );
			$setup_event_missing_meta( [ '_EventStartDateUTC', ], [ 'post_status' => 'trash' ] );
			$setup_event_missing_meta( [ '_EventEndDate', ], [ 'post_status' => 'trash' ] );
			$setup_event_missing_meta( [ '_EventEndDateUTC' ], [ 'post_status' => 'pending' ] );
		};
		yield 'event with partial information in diff. stati' => [ $setup_fixture, 4 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta ) {
			$setup_event_missing_meta( [ '_EventStartDate', '_EventStartDateUTC' ], [ 'post_status' => 'draft' ] );
			$setup_event_missing_meta( [ '_EventEndDate', '_EventEndDateUTC' ], [ 'post_status' => 'trash' ] );
			$setup_event_missing_meta( [ '_EventEndDate', '_EventEndDateUTC' ], [ 'post_status' => 'pending' ] );
		};
		yield 'event with missing information in diff. stati' => [ $setup_fixture, 0 ];
	}

	/**
	 * It should not include events missing start end date information in counts
	 *
	 * @test
	 * @dataProvider total_events_data_provider
	 */
	public function should_not_include_events_missing_start_end_date_information_in_counts( Closure $setup_fixture, int $expected ): void {
		$setup_fixture();

		$events = new Events();

		$this->assertEquals( $expected, $events->get_total_events() );
	}

	/**
	 * It should not include events missing start end date information in ids to process
	 *
	 * @test
	 * @dataProvider total_events_data_provider
	 */
	public function should_not_include_events_missing_start_end_date_information_in_ids_to_process( Closure $setup_fixture, int $expected ): void {
		$setup_fixture();

		$events = new Events();

		$this->assertCount( $expected, $events->get_ids_to_process( 100 ) );
	}
}
