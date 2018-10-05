<?php

namespace Tribe\Events\Aggregator;

use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Record__Abstract as Record;
use Tribe__Events__Aggregator__Records as Records;

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
		$record_one   = $this->make_schedule_record( 'record-one',['source'=>'http://source-one.cal'] );
		$record_two   = $this->make_schedule_record( 'record-two',['source'=>'http://source-two.cal'] );
		$record_three = $this->make_schedule_record( 'record-three',['source'=>'http://source-three.cal'] );

		$records = $this->make_instance();

		foreach ( [ $record_one, $record_two, $record_three] as $record) {
			$match   = $records->find_by_data_hash( $record->meta['source'], $record->get_data_hash() );
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
}