<?php

use TEC\Events\Custom_Tables\V1\Migration\Site_Report;
use TEC\Events\Custom_Tables\V1\Migration\State;

$state = tribe( State::class );

// @todo update this to use the new code.

$report_meta = [ 'complete_timestamp' => strtotime( 'yesterday 4pm' ) ];

$report = (object) [
	'estimated_time_in_hours' => $state->get( 'migrate', 'estimated_time_in_seconds' ) * 60 * 60,
	'date_completed'          => new \DateTimeImmutable( date( 'Y-m-d H:i:s', $report_meta['complete_timestamp'] ) ),
	'event_total'             => $state->get( 'events', 'total' ),
	'changes'                 => true,
	'events'                  => [
		1234 => (object) [
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
		1244 => (object) [
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
		1254 => (object) [
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
