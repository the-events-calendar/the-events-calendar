<?php

namespace Import\Batch;


use Aggregatorv1Tester;
use Codeception\Example;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe\Events\Test\Traits\Aggregator\StatusDataMaker;
use Tribe__Events__Aggregator__Records as Records;

class ForcedImportStatusCest {
	use RecordMaker;
	use StatusDataMaker;

	/**
	 * It should allow forcing the import record to a status
	 *
	 * @test
	 *
	 * @dataProvider forceable_stati
	 */
	public function should_allow_forcing_the_import_record_to_a_status( Aggregatorv1Tester $I, Example $example ) {
		$force_status = $example['status'];
		$data         = $this->make_status_data( [ 'status' => $force_status ] );
		$done         = $data['done'];

		$posts_table = $I->grabPostsTableName();
		$import_id   = '234324234234234';

		$previous_hash = $data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$record        = $this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/state", $data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->{$force_status},
			$status_after_processing,
			'After forcing a status the record should have that status'
		);

		$I->canSeeResponseContainsJson( [ 'status' => 'success' ] );
		$next_batch_hash_criteria = [
			'post_id' => $record->post->ID,
			'meta_key' => '_tribe_aggregator_next_batch_hash'
		];
		if ( $force_status === 'pending' ) {
			$next_batch_hash = $I->grabFromDatabase( $I->grabPostMetaTableName(), 'meta_value', $next_batch_hash_criteria );
			$I->assertEquals( $previous_hash, $next_batch_hash );
		} else {
			$I->dontSeeInDatabase( $I->grabPostMetaTableName(), $next_batch_hash_criteria );
		}
		$done_criteria = [
			'post_id' => $record->post->ID,
			'meta_key' => '_tribe_aggregator_done'
		];
		$done_in_db    = $I->grabFromDatabase( $I->grabPostMetaTableName(), 'meta_value', $done_criteria );
		$I->assertEquals( $done, $done_in_db );
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
		$data         = $this->make_status_data( [ 'status' => $force_status ] );
		$import_id    = '234324234234234';

		$previous_hash = $data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/state", $data );

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
