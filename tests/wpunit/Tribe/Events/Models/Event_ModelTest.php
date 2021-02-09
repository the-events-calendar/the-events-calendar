<?php
namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Models\Post_Types\Event;
use Tribe__Events__Main as Main;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

/**
 * Test that Common is being loaded correctly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Event_ModelTest extends WPTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_return_expected_id() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertEquals( $mock->ID, $event->ID );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_post_type() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertEquals( Main::POSTTYPE, $event->post_type );
	}

	/**
	 * @test
	 */
	public function it_should_return_past() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->is_past );
	}

	/**
	 * @test
	 */
	public function it_should_return_featured() {
		$mock  = $this->get_mock_event( 'events/featured/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->featured );
	}

	/**
	 * @test
	 */
	public function it_should_return_all_day() {
		$mock  = $this->get_mock_event( 'events/all-day/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->all_day );
	}

	/**
	 * @test
	 */
	public function it_should_return_multi_day() {
		$mock  = $this->get_mock_event( 'events/multiday/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertNotEmpty( $event->multiday );
	}
}
