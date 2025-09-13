<?php

namespace TEC\Events\Custom_Tables\V1\Migration;

use Closure;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Events_Migration_RepositoryTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

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

		$event_w_duplicate_meta_information = function ( array $postarr = [] ) use ( $good_event_w_all_information ): int {
			$id = $good_event_w_all_information( $postarr );
			add_post_meta( $id, '_EventStartDate', '2019-01-01 10:00:00' );
			add_post_meta( $id, '_EventEndDate', '2019-01-01 11:00:00' );
			add_post_meta( $id, '_EventStartDateUTC', '2019-01-01 15:00:00' );
			add_post_meta( $id, '_EventEndDateUTC', '2019-01-01 16:00:00' );
			add_post_meta( $id, '_EventTimezone', 'America/New_York' );

			return $id;
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
		yield 'event with good information in diff. status' => [ $setup_fixture, 3 ];

		$setup_fixture = static function () use ( $setup_event_missing_meta ) {
			$setup_event_missing_meta( [ '_EventStartDate', ], [ 'post_status' => 'draft' ] );
			$setup_event_missing_meta( [ '_EventStartDateUTC', ], [ 'post_status' => 'trash' ] );
			$setup_event_missing_meta( [ '_EventEndDate', ], [ 'post_status' => 'trash' ] );
			$setup_event_missing_meta( [ '_EventEndDateUTC' ], [ 'post_status' => 'pending' ] );
		};
		yield 'event with partial information in diff. status' => [ $setup_fixture, 4 ];

		$setup_fixture = static function () use ( $event_w_duplicate_meta_information ) {
			$event_w_duplicate_meta_information( [ 'post_status' => 'publish' ] );
			$event_w_duplicate_meta_information( [ 'post_status' => 'publish' ] );
		};
		yield 'valid event with duplicate meta information' => [ $setup_fixture, 2 ];
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

	/**
	 * It should retry get_ids_to_process() in situations with deadlock errors.
	 *
	 * @test
	 * @dataProvider total_events_data_provider
	 */
	public function should_retry_deadlock_gracefully( Closure $setup_fixture, int $expected ): void {
		global $wpdb;
		$setup_fixture();

		// Setup a deadlock mock before our get_ids_to_process() call.
		$queries      = [];
		$query_filter = function ( $query ) use ( &$queries, &$query_filter ) {
			global $wpdb;
			static $original_dbh;
			// Only run once for each query - our retry will try twice.
			if ( stripos( $query, 'post_id' ) ) {
				if ( count( $queries ) === 0 ) {
					// First query.
					$hit          = true;
					$original_dbh = $wpdb->dbh;
					$wpdb->dbh    = (object) [ 'errno' => 1213 ];
					$this->set_fn_return( 'mysqli_errno', 1213 );
					$this->set_fn_return( 'mysqli_error', 'Faux Deadlock - whoops!' );
					$this->set_fn_return( 'mysqli_ping', true );
					$this->set_fn_return( 'mysqli_query', false );

					// Store the query.
					$queries[] = $query;

					// Do not actually run the query.
					return '';
				}

				// Second query.

				// Store the query.
				$queries[] = $query;

				// If we're here, we've filtered the previous query to simulate a deadlock.
				$wpdb->dbh = $original_dbh;

				// Restore the original dbh function return values.
				$this->unset_uopz_returns();

				// Remove the filter from the query: no more required.
				remove_filter( 'query', $query_filter );
			}

			return $query;
		};

		add_filter( 'query', $query_filter );

		// These should retry and run successfully.
		$events         = new Events();
		$ids_to_process = $events->get_ids_to_process( 100 );

		$this->assertCount( $expected, $ids_to_process );
		$this->assertCount( 2, $queries );
		$this->assertSame( $queries[0], $queries[1] );
	}
}
