<?php
namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Models\Post_Types\Organizer;
use Tribe__Events__Organizer;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

/**
 * Test that Common is being loaded correctly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Organizer_ModelTest extends WPTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_return_expected_id() {
		$mock  = $this->get_mock_organizer( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( $mock->ID, $organizer->ID );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_post_type() {
		$mock  = $this->get_mock_organizer( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( Tribe__Events__Organizer::POSTTYPE, $organizer->post_type );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_phone() {
		$mock  = $this->get_mock_organizer( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_organizer_phone( $mock->ID ), $organizer->phone );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_email() {
		$mock  = $this->get_mock_organizer( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_organizer_email( $mock->ID, false ), $organizer->email );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_website() {
		$mock  = $this->get_mock_organizer( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_organizer_website_url( $mock->ID ), $organizer->website );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_permalink() {
		$mock  = $this->get_mock_venue( 'organizers/1.json' );
		$organizer = Organizer::from_post( $mock )->to_post();

		$this->assertEquals( get_permalink( $mock->ID ), $organizer->permalink );
	}
}
