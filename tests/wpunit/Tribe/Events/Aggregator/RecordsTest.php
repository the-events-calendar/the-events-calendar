<?php

namespace Tribe\Events\Aggregator;

use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Record__Abstract as Record;
use Tribe__Events__Aggregator__Record__Queue_Processor as Queue_Processor;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Main as TEC;
use WP_Query;
use WP_REST_Request;

class RecordsTest extends \Codeception\TestCase\WPTestCase {
	use RecordMaker;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Records::class, $sut );
	}

	/**
	 * @return Records
	 */
	private function make_instance() {
		return new Records();
	}

	/**
	 * It should allow getting a record by its data hash
	 *
	 * @test
	 */
	public function should_allow_getting_a_record_by_its_data_hash() {
		$record_one   = $this->make_schedule_record( 'record-one', [ 'source' => 'http://source-one.cal' ] );
		$record_two   = $this->make_schedule_record( 'record-two', [ 'source' => 'http://source-two.cal' ] );
		$record_three = $this->make_schedule_record( 'record-three', [ 'source' => 'http://source-three.cal' ] );

		$records = $this->make_instance();

		foreach ( [ $record_one, $record_two, $record_three ] as $record ) {
			$match = $records->find_by_data_hash( $record->meta['source'], $record->get_data_hash() );
			$this->assertInstanceOf( Record::class, $match );
			$this->assertEquals( $record->id, $match->id );
		}
	}

	/**
	 * It should return false if no record was found for a data hash
	 *
	 * @test
	 */
	public function should_return_false_if_no_record_was_found_for_a_data_hash() {
		$record_one   = $this->make_schedule_record( 'record-one', [ 'source' => 'http://source-one.cal' ] );
		$record_two   = $this->make_schedule_record( 'record-two', [ 'source' => 'http://source-two.cal' ] );
		$record_three = $this->make_schedule_record( 'record-three', [ 'source' => 'http://source-three.cal' ] );

		$records = $this->make_instance();

		$this->assertFalse( $records->find_by_data_hash( $record_one->meta['source'], 'foo-bar' ) );
		$this->assertFalse( $records->find_by_data_hash( $record_two->meta['source'], $record_one->get_data_hash() ) );
	}

	/**
	 * should prevent to change the status of a scheduled import
	 *
	 * @test
	 */
	public function should_prevent_to_change_the_status_of_a_scheduled_import() {
		/** @var Record $scheduled */
		$scheduled = $this->make_schedule_record( 'record-one', [ 'source' => 'http://source-one.cal' ] );

		$this->assertTrue( $scheduled->is_schedule );
		$this->assertInstanceOf( \WP_Post::class, $scheduled->post );
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$scheduled->set_status_as_failed();
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$scheduled->set_status_as_pending();
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$scheduled->set_status_as_success();
		$this->assertEquals( Records::$status->schedule, $scheduled->post->post_status );

		$this->assertFalse( $scheduled->set_status( 'draft' ) );
		$this->assertFalse( $scheduled->set_status( 'failed' ) );
		$this->assertFalse( $scheduled->set_status( 'success' ) );
		$this->assertFalse( $scheduled->set_status( 'pending' ) );
		$this->assertFalse( $scheduled->set_status( 'invalid one' ) );
	}

	public function record_statuses() {
		return [
			'success'  => [ 'success' ],
			'failed'   => [ 'failed' ],
			'schedule' => [ 'schedule' ],
			'pending'  => [ 'pending' ],
			'draft'    => [ 'draft' ],
		];
	}

	/**
	 * It should not let a logged-out visitor see an event with a record status
	 *
	 * @test
	 * @dataProvider record_statuses
	 */
	public function should_not_let_a_logged_out_visitor_see_an_event_with_a_record_status( $status ) {
		$event_id = static::factory()->post->create(
			[
				'post_type'   => TEC::POSTTYPE,
				'post_status' => Records::$status->{$status},
			]
		);
		wp_set_current_user( 0 );

		$query = new WP_Query( [ 'p' => $event_id, 'post_type' => TEC::POSTTYPE ] );

		$this->assertEmpty( $query->posts );
	}

	/**
	 * It should not let a contributor give an event a record status through the core REST API
	 *
	 * @test
	 * @dataProvider record_statuses
	 */
	public function should_not_let_a_contributor_give_an_event_a_record_status_through_the_core_rest_api( $status ) {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'contributor' ] ) );

		$request = new WP_REST_Request( 'POST', '/wp/v2/' . TEC::POSTTYPE );
		$request->set_body_params( [ 'title' => 'An event', 'status' => Records::$status->{$status} ] );
		$response = rest_do_request( $request );

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEmpty( get_posts( [ 'post_type' => TEC::POSTTYPE, 'post_status' => Records::$status->{$status} ] ) );
	}

	/**
	 * It should find its records by status
	 *
	 * @test
	 */
	public function should_find_its_records_by_status() {
		$ids = [];
		foreach ( array_keys( $this->record_statuses() ) as $status ) {
			$ids[ $status ] = static::factory()->post->create(
				[
					'post_type'      => Records::$post_type,
					'post_status'    => Records::$status->{$status},
					'post_mime_type' => 'ea/ical',
					'meta_input'     => [
						Record::$meta_key_prefix . 'origin'    => 'ical',
						Record::$meta_key_prefix . 'import_id' => "import-{$status}",
					],
				]
			);
		}
		update_post_meta( $ids['pending'], Record::$meta_key_prefix . 'queue', [ 'item' ] );
		// Scheduled imports and the queue run from cron, with no user.
		wp_set_current_user( 0 );

		$records = $this->make_instance();

		$history = $records->query( [ 'fields' => 'ids' ] )->posts;
		$this->assertEqualSets( [ $ids['success'], $ids['failed'], $ids['pending'] ], $history );

		$scheduled = $records->query( [ 'fields' => 'ids', 'post_status' => Records::$status->schedule ] )->posts;
		$this->assertEquals( [ $ids['schedule'] ], $scheduled );

		foreach ( [ 'draft', 'pending', 'success' ] as $status ) {
			$this->assertEquals( $ids[ $status ], $records->get_by_import_id( "import-{$status}" )->id );
		}

		$this->assertEquals( $ids['pending'], ( new Queue_Processor() )->next_waiting_record() );
	}
}
