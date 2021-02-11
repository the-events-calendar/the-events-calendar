<?php
namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Models\Post_Types\Venue;
use Tribe__Events__Venue;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

/**
 * Test that Common is being loaded correctly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Venue_ModelTest extends WPTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_return_expected_id() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( $mock->ID, $venue->ID );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_post_type() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( Tribe__Events__Venue::POSTTYPE, $venue->post_type );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_address() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_address( $mock->ID ), $venue->address );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_country() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_country( $mock->ID ), $venue->country );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_city() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_city( $mock->ID ), $venue->city );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_state_province() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_stateprovince( $mock->ID ), $venue->state_province );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_state() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_state( $mock->ID ), $venue->state );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_province() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_province( $mock->ID ), $venue->province );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_zip() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_zip( $mock->ID ), $venue->zip );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_phone() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_phone( $mock->ID ), $venue->phone );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_permalink() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( get_permalink( $mock->ID ), $venue->permalink );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_directions_link() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_map_link( $mock->ID ), $venue->directions_link );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_website() {
		$mock  = $this->get_mock_venue( 'venues/1.json' );
		$venue = Venue::from_post( $mock )->to_post();

		$this->assertEquals( tribe_get_venue_website_url( $mock->ID ), $venue->website );
	}
}
