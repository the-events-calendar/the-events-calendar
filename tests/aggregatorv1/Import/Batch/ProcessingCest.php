<?php

namespace Import\Batch;


use Aggregatorv1Tester;
use Tribe\Events\Test\Traits\Aggregator\BatchDataMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Main as Main;

// @todo the client should send the first expected hash when creating the import
// @todo test for the filterable interval
class ProcessingCest {
	use RecordMaker;
	use BatchDataMaker;

	public function _before( Aggregatorv1Tester $I ) {
	}

	public function _after( Aggregatorv1Tester $I ) {
	}

	/**
	 * It should correctly process the first batch of data
	 *
	 * @test
	 */
	public function should_correctly_process_the_first_batch_of_data( Aggregatorv1Tester $I ) {
		$posts_table = $I->grabPostsTableName();
		$event_cpt   = Main::POSTTYPE;
		$I->assertCount( 0, $I->grabAllFromDatabase( $posts_table, 'ID', [ 'post_type' => $event_cpt ] ) );
		$import_id = '234324234234234';
		// 9 events total, 3 batches of 3 events, 0 processed so far
		$batch_data = $this->make_batch_data( [
			'status' => [
				'data' => [
					'total' => 9,
					'done' => 0,
				],
				'batch' => [
					'total' => 3,
					'done' => 0,
				],
			]
		], 3 );

		$previous_hash = $batch_data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$record = $this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$event_ids = array_column( $I->grabAllFromDatabase( $posts_table, 'ID', [ 'post_type' => $event_cpt ] ), 'ID' );
		$I->assertCount( 3, $event_ids );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->pending,
			$status_after_processing,
			'After processing the first batch a post status should be pending'
		);

		$I->canSeeResponseContainsJson( [ 'status' => 'success' ] );
		$I->canSeeResponseContainsJson( [ 'activity' => [ $event_cpt => [ 'created' => $event_ids ] ] ] );
		$new_hash = $I->grabFromDatabase( $I->grabPostMetaTableName(), 'meta_value', [ 'meta_key' => '_tribe_aggregator_next_batch_hash' ] );
		$I->assertNotEquals( $previous_hash, $new_hash, 'After processing a batch the record should update the `next_batch_hash` meta' );
		$I->canSeeResponseContainsJson( [ 'next_batch_hash' => $new_hash ] );
		$I->canSeeResponseContainsJson( [ 'interval' => 10 ] );
	}

	/**
	 * It should correctly process a batch of data after the first
	 *
	 * @test
	 */
	public function should_correctly_process_a_batch_of_data_after_the_first( Aggregatorv1Tester $I ) {

	}

	/**
	 * It should correctly process the last batch of data
	 *
	 * @test
	 */
	public function should_correctly_process_the_last_batch_of_data( Aggregatorv1Tester $I ) {

	}
}
