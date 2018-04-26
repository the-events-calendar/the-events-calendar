<?php

namespace Import\Batch;

use Aggregatorv1Tester;
use Codeception\Example;
use Tribe\Events\Test\Traits\Aggregator\BatchDataMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;

class ValidationCest {
	use RecordMaker;
	use BatchDataMaker;

	/**
	 * It should reject data when the import ID is not an existing one
	 *
	 * @test
	 */
	public function should_reject_data_when_the_import_id_is_not_an_existing_one( Aggregatorv1Tester $I ) {
		$not_existing_import_id = md5( 'not-existing-import-id' );

		$I->sendPOST( "import/{$not_existing_import_id}/batch", $this->make_batch_data() );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}


	/**
	 * It should reject data when the import ID is not one needing data
	 *
	 * @test
	 *
	 * @dataProvider record_statuses_not_needing_data
	 */
	public function should_reject_data_when_the_import_id_is_not_one_needing_data( Aggregatorv1Tester $I, Example $example ) {
		$status             = $example['status'];
		$existing_import_id = md5( 'existing-import-id' );
		$batch_data         = $this->make_batch_data();

		$overrides = [ 'next_batch_hash' => $batch_data['batch_hash'] ];
		$this->make_record( $existing_import_id, $overrides, $status );

		$I->sendPOST( "import/{$existing_import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should reject data when the batch hash is not set
	 *
	 * @test
	 *
	 * @dataProvider record_statuses_needing_data
	 */
	public function should_reject_data_when_the_batch_hash_is_not_set( Aggregatorv1Tester $I, Example $example ) {
		$status             = $example['status'];
		$existing_import_id = md5( 'existing-import-id' );
		$batch_data         = $this->make_batch_data();

		$overrides = [ 'next_batch_hash' => $batch_data['batch_hash'] ];
		$this->make_record( $existing_import_id, $overrides, $status );

		unset( $batch_data['batch_hash'] );
		$I->sendPOST( "import/{$existing_import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should reject data if batch_hash is not expected
	 *
	 * @test
	 *
	 * @dataProvider record_statuses_needing_data
	 */
	public function should_reject_data_if_batch_hash_is_not_expected( Aggregatorv1Tester $I, Example $example ) {
		$status             = $example['status'];
		$existing_import_id = md5( 'existing-import-id' );
		$batch_data         = $this->make_batch_data();

		$overrides = [ 'next_batch_hash' => $batch_data['batch_hash'] ];
		$this->make_record( $existing_import_id, $overrides, $status );

		$batch_data['batch_hash'] = 'unexpected';
		$I->sendPOST( "import/{$existing_import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	protected function record_statuses_needing_data() {
		return [
			'pending' => [ 'status' => 'pending' ],
		];
	}

	protected function record_statuses_not_needing_data() {
		return [
			'success' => [ 'status' => 'success' ],
			'failed' => [ 'status' => 'failed' ],
			'schedule' => [ 'status' => 'schedule' ],
			'draft' => [ 'status' => 'draft' ],
		];
	}
}
