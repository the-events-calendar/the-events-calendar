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
		$done         = $data['percentage_complete'];

		$posts_table = $I->grabPostsTableName();
		$import_id   = '234324234234234';

		$previous_hash = $data['batch_hash'];
		$meta          = [ 'next_batch_hash' => $previous_hash ];
		$record        = $this->make_manual_record( $import_id, $meta, 'pending' );

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
			'post_id'  => $record->post->ID,
			'meta_key' => '_tribe_aggregator_next_batch_hash',
		];
		if ( $force_status === 'pending' ) {
			$next_batch_hash = $I->grabFromDatabase( $I->grabPostMetaTableName(), 'meta_value', $next_batch_hash_criteria );
			$I->assertEquals( $previous_hash, $next_batch_hash );
		} else {
			$I->dontSeeInDatabase( $I->grabPostMetaTableName(), $next_batch_hash_criteria );
		}
		$done_criteria = [
			'post_id'  => $record->post->ID,
			'meta_key' => '_tribe_aggregator_percentage_complete',
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
		$this->make_manual_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/state", $data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should reject a failure report when no next batch hash is present on the record.
	 *
	 * @test
	 */
	public function should_reject_a_failure_report_when_no_next_hash_is_present( Aggregatorv1Tester $I ) {
		$import_id = '234324234234234';

		$record      = $this->make_manual_record( $import_id, [], 'pending' );
		$posts_table = $I->grabPostsTableName();
		$status      = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );

		$I->assertEquals( Records::$status->pending, $status );

		$data = $this->make_status_data( [ 'status' => 'failed' ] );
		$I->sendPOST( "import/{$import_id}/state", $data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->pending,
			$status_after_processing,
			'A rejected failure report must leave the record in its original pending status'
		);

		$I->dontSeeInDatabase(
			$I->grabCommentsTableName(),
			[
				'comment_post_ID' => $record->post->ID,
				'comment_type'    => 'tribe-ea-error',
			]
		);
	}

	/**
	 * It should reject a failure report when the batch hash does not match the expected one.
	 *
	 * @test
	 */
	public function should_reject_a_failure_report_when_the_batch_hash_is_not_expected( Aggregatorv1Tester $I ) {
		$import_id = '234324234234234';

		$record      = $this->make_manual_record( $import_id, [ 'next_batch_hash' => 'the-real-secret' ], 'pending' );
		$posts_table = $I->grabPostsTableName();

		$data = $this->make_status_data(
			[
				'status'     => 'failed',
				'batch_hash' => 'junk-no-secret-needed',
			]
		);
		$I->sendPOST( "import/{$import_id}/state", $data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->pending,
			$status_after_processing,
			'A failure report with a wrong batch hash must leave the record in its original pending status'
		);

		$I->dontSeeInDatabase(
			$I->grabCommentsTableName(),
			[
				'comment_post_ID' => $record->post->ID,
				'comment_type'    => 'tribe-ea-error',
			]
		);
	}

	/**
	 * It should allow a failure report when the batch hash matches the expected one.
	 *
	 * Confirms the legitimate Event Aggregator flow still works: the service holds the negotiated
	 * hash and can report a failure with it.
	 *
	 * @test
	 */
	public function should_allow_a_failure_report_when_the_batch_hash_is_expected( Aggregatorv1Tester $I ) {
		$import_id = '234324234234234';

		$record      = $this->make_manual_record( $import_id, [ 'next_batch_hash' => 'the-real-secret' ], 'pending' );
		$posts_table = $I->grabPostsTableName();

		$data = $this->make_status_data(
			[
				'status'     => 'failed',
				'batch_hash' => 'the-real-secret',
			]
		);
		$I->sendPOST( "import/{$import_id}/state", $data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$status_after_processing = $I->grabFromDatabase( $posts_table, 'post_status', [ 'ID' => $record->post->ID ] );
		$I->assertEquals(
			Records::$status->failed,
			$status_after_processing,
			'A failure report with the expected batch hash should mark the record as failed'
		);
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
