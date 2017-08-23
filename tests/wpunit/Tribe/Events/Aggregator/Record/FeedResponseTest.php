<?php


namespace Tribe\Events\Aggregator\Record;

use Tribe\Events\Tests\Testcases\Events_TestCase;

class FeedResponseTest extends Events_TestCase {

	/**
	 * Test the feed responses does not contain duplicates
	 */
	public function test_the_feed_response_does_not_contain_duplicates() {
		$source_dir = codecept_data_dir( 'ea-responses' );

		foreach ( glob( $source_dir . '/*.json' ) as $response_file ) {
			$response_json         = file_get_contents( $response_file );
			$response_basename     = basename( $response_file );
			$decoded_response_json = json_decode( $response_json );
			$events                = $decoded_response_json->data->events;

			$uids = [];

			foreach ( $events as $event ) {
				if ( empty( $event->uid ) ) {
					continue;
				}

				$event_uid = $event->uid;
				if ( array_key_exists( $event_uid, $uids ) ) {
					throw new \RuntimeException( "File {$response_basename}: an event with UID {$event_uid} already exists ({$uids[$event_uid]})" );
				}

				$uids[ $event_uid ] = "{$event->title}, {$event->start_date} to {$event->end_date}";
			}

			$events_count = count( $events );
			$uids_count   = count( $uids );
			$this->assertEquals(
				$events_count,
				$uids_count,
				"File {$response_basename}: the number of UIDs in the feed ({$uids_count}) does not match the number of events in the feed ({$events_count})"
			);
		}
	}
}