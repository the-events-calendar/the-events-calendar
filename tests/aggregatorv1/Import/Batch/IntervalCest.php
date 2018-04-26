<?php

namespace Import\Batch;


use Aggregatorv1Tester;
use Codeception\Example;
use Tribe\Events\Test\Traits\Aggregator\BatchDataMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;

// @todo the client should send the first expected hash when creating the import
class IntervalCest {
	use RecordMaker;
	use BatchDataMaker;

	/**
	 * It should allow filtering the batch interval
	 *
	 * @test
	 *
	 * @dataProvider intervals
	 */
	public function should_allow_filtering_the_batch_interval( Aggregatorv1Tester $I, Example $example ) {
		$interval = $example['value'];
		$expected = $example['expected'];

		$code = <<<PHP
add_filter('event_aggregator_event_batch_interval', function(){
	return {$interval};
});
PHP;
		$I->haveMuPlugin( 'batch-interval-filter.php', $code );

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
		$this->make_record( $import_id, $meta, 'pending' );

		$I->sendPOST( "import/{$import_id}/batch", $batch_data );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$I->canSeeResponseContainsJson( [ 'interval' => $expected ] );
	}

	protected function intervals() {
		return [
			'legit-1' => [ 'value' => 10, 'expected' => 10 ],
			'legit-2' => [ 'value' => 30, 'expected' => 30 ],
			'legit-3' => [ 'value' => 200, 'expected' => 200 ],
			'max' => [ 'value' => 600, 'expected' => 600 ],
			'over-max' => [ 'value' => 601, 'expected' => 600 ],
			'null' => [ 'value' => 'null', 'expected' => 10 ],
			'not-a-number' => [ 'value' => '"nan"', 'expected' => 10 ],
			'empty-string' => [ 'value' => '', 'expected' => 10 ],
			'too-fast-cowboy' => [ 'value' => 5, 'expected' => 10 ],
			'zero' => [ 'value' => 5, 'expected' => 10 ],
		];
	}
}
