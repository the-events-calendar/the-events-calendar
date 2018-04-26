<?php

namespace Import\Batch;


use Aggregatorv1Tester;
use Codeception\Example;
use Tribe\Events\Test\Traits\Aggregator\BatchDataMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Main as Main;

class ForcedImportStatusCest {
	use RecordMaker;
	use BatchDataMaker;

	/**
	 * It should allow forcing the import record to a status
	 *
	 * @test
	 *
	 * @dataProvider forceable_stati
	 */
	public function should_allow_forcing_the_import_record_to_a_status( Aggregatorv1Tester $I, Example $example ) {
		$force_status = $example['status'];

		$posts_table = $I->grabPostsTableName();
		$event_cpt   = Main::POSTTYPE;
		$I->assertCount( 0, $I->grabAllFromDatabase( $posts_table, 'ID', [ 'post_type' => $event_cpt ] ) );
		$import_id  = '234324234234234';
		$batch_data = $this->make_batch_data( [
			'status' => [
				'data' => [
					'total' => 9,
					'done' => 6,
				],
				'batch' => [
					'total' => 3,
					'done' => 2,
				],
			],
			'force_import_status' => $force_status,
		], 3 );

		$previous_hash = $batch_data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$record        = $this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$event_ids = array_column( $I->grabAllFromDatabase( $posts_table, 'ID', [ 'post_type' => $event_cpt ] ), 'ID' );
		$I->assertCount( 0, $event_ids, 'No events should be processed when forcing a status' );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->{$force_status},
			$status_after_processing,
			'After forcing a status the record should have that status'
		);

		$I->canSeeResponseContainsJson( [ 'status' => 'success' ] );
		$I->canSeeResponseContainsJson( [ 'activity' => [ $event_cpt => [ 'created' => [] ] ] ] );
		$next_batch_hash_criteria = [
			'post_id' => $record->post->ID,
			'meta_key' => '_tribe_aggregator_next_batch_hash'
		];
		if ( $force_status === 'pending' ) {
			$next_batch_hash = $I->grabFromDatabase( $I->grabPostMetaTableName(), 'meta_value', $next_batch_hash_criteria );
			$I->assertNotEquals( $previous_hash, $next_batch_hash );
			$I->canSeeResponseContainsJson( [ 'next_batch_hash' => $next_batch_hash ] );
		} else {
			$I->dontSeeInDatabase( $I->grabPostMetaTableName(), $next_batch_hash_criteria );
			$I->cantSeeResponseContainsJson( [ 'next_batch_hash' => '*' ] );
		}
		$I->canSeeResponseContainsJson( [ 'interval' => 10 ] );
	}

	/**
	 * It should reject non force-able stati
	 *
	 * @test
	 *
	 * @dataProvider non_forceable_stati
	 */
	public function should_reject_non_force_able_stati( Aggregatorv1Tester $I, Example $example ) {
		$force_status = $example['status'];

		$posts_table = $I->grabPostsTableName();
		$event_cpt   = Main::POSTTYPE;
		$I->assertCount( 0, $I->grabAllFromDatabase( $posts_table, 'ID', [ 'post_type' => $event_cpt ] ) );
		$import_id  = '234324234234234';
		$batch_data = $this->make_batch_data( [
			'status' => [
				'data' => [
					'total' => 9,
					'done' => 6,
				],
				'batch' => [
					'total' => 3,
					'done' => 2,
				],
			],
			'force_import_status' => $force_status,
		], 3 );

		$previous_hash = $batch_data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$record        = $this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	protected function forceable_stati() {
		return [
			[ 'status' => 'success' ],
			[ 'status' => 'failed' ],
			[ 'status' => 'pending' ],
		];
	}

	protected function non_forceable_stati() {
		return [
			[ 'status' => 'draft' ],
			[ 'status' => 'schedule' ],
			[ 'status' => 'not-even-a-status' ],
		];
	}
}
