<?php
namespace Tribe\Pro\Recurrence;

use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Recurrence__Sequence as Sequence;

class SequenceTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var array
	 */
	protected $sequence;

	/**
	 * @var int
	 */
	protected $parent_event_id;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should throw if sequence is not in the right format
	 */
	public function it_should_throw_if_sequence_is_not_in_the_right_format() {
		$this->parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );

		$this->sequence = [
			[
				'some' => 'entry'
			]
		];

		$this->expectException( \InvalidArgumentException::class );

		$sut = $this->make_instance();
	}

	/**
	 * @test
	 * it should throw if parent event ID is not valid event ID
	 */
	public function it_should_throw_if_parent_event_id_is_not_valid_event_id() {
		$this->parent_event_id = 3344;

		$this->sequence = [
			[
				'timestamp' => date( 'U' )
			]
		];

		$this->expectException( \InvalidArgumentException::class );

		$sut = $this->make_instance();
	}

	/**
	 * @test
	 * it should sort dates in the sequence by start date
	 */
	public function it_should_sort_dates_in_the_sequence_by_start_date() {
		$this->parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );

		$this->sequence = [
			[ 'timestamp' => strtotime( '2016-07-17 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-15 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-18 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-13 15:00:00' ) ],
		];

		$sut = $this->make_instance();

		$sequence = $sut->get_sorted_sequence_array();

		$this->assertEquals( [
			[ 'timestamp' => strtotime( '2016-07-13 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-15 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-17 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-18 15:00:00' ) ],
		], $sequence );
	}


	/**
	 * @test
	 * it should return a sequence number for each sequence entry
	 */
	public function it_should_return_a_sequence_number_for_each_sequence_entry() {
		$this->parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		
		$this->sequence = [
			[ 'timestamp' => strtotime( '2016-07-17 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-15 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-13 17:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-13 16:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-15 16:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-18 15:00:00' ) ],
			[ 'timestamp' => strtotime( '2016-07-13 15:00:00' ) ],
		];

		$sut = $this->make_instance();

		$sequence = $sut->get_sorted_sequence();

		$this->assertEquals( [
			[ 'timestamp' => strtotime( '2016-07-13 15:00:00' ), 'sequence' => 1 ],
			[ 'timestamp' => strtotime( '2016-07-13 16:00:00' ), 'sequence' => 2 ],
			[ 'timestamp' => strtotime( '2016-07-13 17:00:00' ), 'sequence' => 3 ],
			[ 'timestamp' => strtotime( '2016-07-15 15:00:00' ), 'sequence' => 1 ],
			[ 'timestamp' => strtotime( '2016-07-15 16:00:00' ), 'sequence' => 2 ],
			[ 'timestamp' => strtotime( '2016-07-17 15:00:00' ), 'sequence' => 1 ],
			[ 'timestamp' => strtotime( '2016-07-18 15:00:00' ), 'sequence' => 1 ],
		], $sequence );
	}

	/**
	 * @return Sequence
	 */
	private function make_instance() {
		return new Sequence( $this->sequence, $this->parent_event_id );
	}

}