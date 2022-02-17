<?php
/**
 * Provides and API to interact with the migration reports in a per-event and
 * per-site basis.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Reports.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Reports {

	/**
	 * Builds and returns the site migration report in array format.
	 *
	 * @since TBD
	 *
	 * @return Site_Report A reference the site migration report instance.
	 */
	public function build() {
		// @todo pull site-wide stats
		// @todo pull per-event stats


		$state = tribe( State::class );

		// @todo Update this to use the dynamic code in ... this structure? Some of this probably should move inside Site_Report

		$report_meta = [ 'complete_timestamp' => strtotime( 'yesterday 4pm' ) ];

		$data = [
			'estimated_time_in_hours' => $state->get( 'migrate', 'estimated_time_in_seconds' ) * 60 * 60,
			'date_completed'          => (new \DateTimeImmutable( date( 'Y-m-d H:i:s', $report_meta['complete_timestamp'] ) ))->format( 'F j, Y, g:i a' ),
			'event_total'             => $state->get( 'events', 'total' ),
			'has_changes'                 => true,
			'events'                  => [
				[
					'source_event_post_id' => 1234,
					'events'               => [
						1234 => (object) [
							'ID'         => 1234,
							'post_title' => 'Cabbage Party',
						],
						1235 => (object) [
							'ID'         => 1235,
							'post_title' => 'Cabbage Party',
						],
					],
					'status'               => 'success',
					'reason'               => null,
					'series_post_id'       => 1250,
					'series'               => (object) [
						'ID'         => 1250,
						'post_title' => 'Cabbage Party Series',
					],
					'actions_taken'        => [
						'split',
					],
				],
				[
					'source_event_post_id' => 1244,
					'events'               => [
						1244 => (object) [
							'ID'         => 1244,
							'post_title' => 'Broccoli Shindig',
						],
						1245 => (object) [
							'ID'         => 1245,
							'post_title' => 'Broccoli Shindig',
						],
					],
					'status'               => 'success',
					'reason'               => null,
					'actions_taken'        => [
						'modified-rules',
					],
				],
				[
					'source_event_post_id' => 1254,
					'events'               => [
						1254 => (object) [
							'ID'         => 1254,
							'post_title' => 'Carrot Club',
						],
						1255 => (object) [
							'ID'         => 1255,
							'post_title' => 'Carrot Club',
						],
					],
					'status'               => 'success',
					'reason'               => null,
					'actions_taken'        => [
						'modified-exclusions',
					],
				],
			],
		];

		return new Site_Report( $data );
	}
}