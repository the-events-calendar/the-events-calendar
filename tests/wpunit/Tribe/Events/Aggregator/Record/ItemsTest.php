<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe\Events\Test\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe__Events__Aggregator__Record__Items as Items;

class ItemsTest extends Aggregator_TestCase {
	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Items::class, $sut );
	}

	/**
	 * @return Items
	 */
	private function make_instance() {
		return new Items();
	}

	/**
	 * It should return emtpy array if items are not set
	 *
	 * @test
	 */
	public function should_return_emtpy_array_if_items_are_not_set() {
		$sut = $this->make_instance();
		$this->assertEquals( [], $sut->get_items() );
	}

	/**
	 * It should return items unchanged when no linked post is duplicated
	 *
	 * @test
	 */
	public function should_return_items_unchanged_when_no_linked_post_is_duplicated() {
		$items = array_map( function () {
			return $this->factory()->import_record->create_and_get_event_record();
		}, range( 1, 3 ) );
		$sut   = $this->make_instance();
		$sut->set_items( $items );

		$actual = $sut->mark_dependencies()->get_items();

		$this->assertEqualSets( $sut->get_original_items(), $actual );
	}

	/**
	 * It should mark items as dependent when venue is used by more than one
	 *
	 * @test
	 */
	public function should_mark_items_as_dependent_when_venue_is_used_by_more_than_one() {
		$items = array_map( function () {
			return $this->factory()->import_record->create_and_get_event_record();
		}, range( 1, 3 ) );

		$item_0_venue    = $items[0]->venue;
		$items[1]->venue = $item_0_venue;

		$sut = $this->make_instance();
		$sut->set_items( $items );
		$actual = $sut->mark_dependencies()->get_items();

		$this->assertCount( 3, $actual );
		$this->assertEquals( $items[0], $actual[0] );
		$this->assertEquals( $items[2], $actual[2] );
		$this->assertEqualFields( $item_0_venue, $actual[1]->venue );
		$this->assertEquals( [ $item_0_venue->global_id ], $actual[1]->depends_on );
		unset( $items[1]->depends_on );
		$this->assertEquals( $items[1], $actual[1] );
	}

	/**
	 * It should mark items as dependent when organizer is used by more than one
	 *
	 * @test
	 */
	public function should_mark_items_as_dependent_when_organizer_is_used_by_more_than_one() {
		$items = array_map( function () {
			return $this->factory()->import_record->create_and_get_event_record();
		}, range( 1, 3 ) );

		$item_0_organizer    = $items[0]->organizer;
		$items[1]->organizer = $item_0_organizer;

		$sut = $this->make_instance();
		$sut->set_items( $items );
		$actual = $sut->mark_dependencies()->get_items();

		$this->assertCount( 3, $actual );
		$this->assertEquals( $items[0], $actual[0] );
		$this->assertEquals( $items[2], $actual[2] );
		$this->assertEquals( $item_0_organizer, $actual[1]->organizer );
		$this->assertEquals( wp_list_pluck( $item_0_organizer, 'global_id' ), $actual[1]->depends_on );
		unset( $items[1]->depends_on );
		$this->assertEquals( $items[1], $actual[1] );
	}

	/**
	 * It should mark items as dependent when one organizer is used by more than one
	 *
	 * @test
	 */
	public function should_mark_items_as_dependent_when_one_organizer_is_used_by_more_than_one() {
		$items = array_map( function () {
			return $this->factory()->import_record->create_and_get_event_record();
		}, range( 1, 3 ) );

		$item_0_organizer_0     = $items[0]->organizer[0];
		$items[1]->organizer[0] = $item_0_organizer_0;

		$sut = $this->make_instance();
		$sut->set_items( $items );
		$actual = $sut->mark_dependencies()->get_items();

		$this->assertCount( 3, $actual );
		$this->assertEquals( $items[0], $actual[0] );
		$this->assertEquals( $items[2], $actual[2] );
		$this->assertEquals( $item_0_organizer_0, $actual[1]->organizer[0] );
		$this->assertEquals( [ $item_0_organizer_0->global_id ], $actual[1]->depends_on );
		unset( $items[1]->depends_on );
		$this->assertEquals( $items[1], $actual[1] );
	}

	/**
	 * It should mark items as dependent when venue and organizer are used by more than one
	 *
	 * @test
	 */
	public function should_mark_items_as_dependent_when_venue_and_organizer_are_used_by_more_than_one() {
		$items = array_map( function () {
			return $this->factory()->import_record->create_and_get_event_record();
		}, range( 1, 3 ) );

		$item_0_venue           = $items[0]->venue;
		$item_0_organizer_0     = $items[0]->organizer[0];
		$items[1]->venue        = $item_0_venue;
		$items[1]->organizer[0] = $item_0_organizer_0;

		$sut = $this->make_instance();
		$sut->set_items( $items );
		$actual = $sut->mark_dependencies()->get_items();

		$this->assertCount( 3, $actual );
		$this->assertEquals( $items[0], $actual[0] );
		$this->assertEquals( $items[2], $actual[2] );
		$this->assertEquals( $item_0_organizer_0, $actual[1]->organizer[0] );
		$this->assertEquals( [ $item_0_venue->global_id, $item_0_organizer_0->global_id ], $actual[1]->depends_on );
		unset( $items[1]->depends_on );
		$this->assertEquals( $items[1], $actual[1] );
	}
}
