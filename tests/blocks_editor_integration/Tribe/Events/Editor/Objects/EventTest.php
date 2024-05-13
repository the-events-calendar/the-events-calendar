<?php

namespace Tribe\Events\Editor\Objects;

use Tribe__Utils__Array as Arr;

class EventTest extends \Codeception\TestCase\WPTestCase {
	public static function _setUpBeforeClass() {
		parent::_setUpBeforeClass();
		static::factory()->event = new \Tribe\Events\Test\Factories\Event();
	}

	/**
	 * It should return data for a new event if the post is null and not set globally
	 *
	 * @test
	 */
	public function should_return_data_for_a_new_event_if_the_post_is_null_and_not_set_globally() {
		foreach ( [ $GLOBALS, $_GET, $_REQUEST, $_POST ] as $superglobal ) {
			unset( $superglobal['post'] );
		}

		$event_object = new Event;
		$data         = $event_object->data();

		$this->assertTrue( $data['is_new_post'] );
	}

	/**
	 * It should return the data for a new event if the global post is not a post
	 *
	 * @test
	 */
	public function should_return_the_data_for_a_new_event_if_the_global_post_is_not_a_post() {
		$_GET['post'] = 23;

		$event_object = new Event;
		$data         = $event_object->data();

		$this->assertTrue( $data['is_new_post'] );
	}

	/**
	 * It should return data for new event if global post is not an event
	 *
	 * @test
	 */
	public function should_return_data_for_new_event_if_global_post_is_not_an_event() {
		$_GET['post'] = static::factory()->post->create();

		$event_object = new Event;
		$data         = $event_object->data();

		$this->assertTrue( $data['is_new_post'] );
	}

	/**
	 * It should return the data for the global post if it's an event
	 *
	 * @test
	 */
	public function should_return_the_data_for_the() {
		$_GET['post'] = $event = static::factory()->event->create();

		$event_object = new Event;
		$data         = $event_object->data();

		$this->assertFalse( $data['is_new_post'] );
		$this->assertArrayHasKey( 'meta', $data );
		$this->assertEqualSets( Arr::flatten( get_post_meta( $event ) ), $data['meta'] );
	}

	/**
	 * It should return the data for the specified event
	 *
	 * @test
	 */
	public function should_return_the_data_for_the_specified_event() {
		$_GET['post'] = static::factory()->event->create();
		$event        = static::factory()->event->create();

		$event_object = new Event( $event );
		$data         = $event_object->data();

		$this->assertFalse( $data['is_new_post'] );
		$this->assertArrayHasKey( 'meta', $data );
		$this->assertEqualSets( Arr::flatten( get_post_meta( $event ) ), $data['meta'] );
	}

	/**
	 * It should allow getting all or some data values
	 *
	 * @test
	 */
	public function should_allow_getting_all_or_some_data_values() {
		$event = static::factory()->event->create();

		$event_object = new Event( $event );
		$this->assertFalse( $event_object->data( 'is_new_post', true ) );
		$this->assertEqualSets( Arr::flatten( get_post_meta( $event ) ), $event_object->data( 'meta', [] ) );
		$this->assertEquals( 2389, $event_object->data( 'foo-bar', 2389 ) );
	}

	/**
	 * It should fix meta fields requiring fixes
	 *
	 * @test
	 */
	public function should_fix_meta_fields_requiring_fixes() {
		$event = static::factory()->event->create( [
			'meta_input' => [
				'_EventAllDay'      => 'yes',
				 '_EventCost'        => '23',
				'_EventVenueID'     => '89',
				'_EventShowMap'     => 'no',
				'_EventShowMapLink' => 'yes',
			]
		] );

		// Add as the code on post save would add them.
		foreach ( [ '23', '89', '2389' ] as $id ) {
			add_post_meta( $event, '_EventOrganizerID', $id );
		}

		$meta = ( new Event( $event ) )->data( 'meta' );

		$expected = [
			'_EventAllDay'      => true,
			'_EventOrganizerID' => [ 23, 89, 2389 ],
			'_EventCost'        => '23',
			'_EventVenueID'     => [ 89 ],
			'_EventShowMap'     => false,
			'_EventShowMapLink' => true,
		];
		foreach ( $expected as $key => $value ) {
			$this->assertEquals( $value, $meta[ $key ] );
		}
	}

	public function cost_meta_db_values() {
		yield 'empty' => [ '', '' ];
		yield 'one_value' => [ '23', '23' ];
		yield 'two_values' => [ [ '23', 89 ], '23 – 89' ];
		yield 'three_values' => [ [ '23', 89, 15 ], '15 – 89' ];
		yield 'range_string' => [ [ '23 - 89' ], '23 – 89' ];
	}

	/**
	 * Test cost meta output
	 * @dataProvider cost_meta_db_values
	 */
	public function test_cost_meta_output( $cost_db_values, $expected ) {
		$event = static::factory()->event->create();
		// Add as the code on post save would add them.
		foreach ( (array) $cost_db_values as $db_value ) {
			add_post_meta( $event, '_EventCost', $db_value );
		}

		$meta = ( new Event( $event ) )->data( 'meta' );

		$this->assertEquals( $expected, $meta['_EventCost'] );
	}
}
