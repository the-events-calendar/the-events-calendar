<?php

namespace Tribe\Events\Integrations\QR;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\QR\Redirections;
use Tribe__Events__Main as TEC;

/**
 * Tests QR Redirections functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class RedirectionsTest extends Controller_Test_Case {

	/**
	 * The controller class to test.
	 *
	 * @var string
	 */
	protected $controller_class = Redirections::class;

	/**
	 * The redirections instance.
	 *
	 * @var \TEC\Events\QR\Redirections
	 */
	protected $redirections;

	/**
	 * The test event ID.
	 *
	 * @var int
	 */
	protected $test_event_id;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();

		// Register the redirections
		$this->redirections = tribe( Redirections::class );
		$this->redirections->register();

		// Create a test event
		$this->test_event_id = $this->factory->post->create(
			[
				'post_type'   => TEC::POSTTYPE,
				'post_status' => 'publish',
			]
		);

		// Set event dates
		$now      = current_time( 'mysql' );
		$tomorrow = date( 'Y-m-d H:i:s', strtotime( '+1 day' ) );

		update_post_meta( $this->test_event_id, '_EventStartDate', $now );
		update_post_meta( $this->test_event_id, '_EventEndDate', $tomorrow );
	}

	/**
	 * Test current event URL generation
	 *
	 * @test
	 */
	public function test_current_event_url_generation() {
		$url = $this->redirections->get_current_event_url();

		// Since this is the only event and it's current, it should return the event's permalink
		$this->assertEquals( get_permalink( $this->test_event_id ), $url );
	}

	/**
	 * Test upcoming event URL generation
	 *
	 * @test
	 */
	public function test_upcoming_event_url_generation() {
		// Set event start time to 1 hour after now
		$one_hour_later = date( 'Y-m-d H:i:s', strtotime( '+1 hour' ) );
		update_post_meta( $this->test_event_id, '_EventStartDate', $one_hour_later );

		$url = $this->redirections->get_upcoming_event_url();

		// Since this is the only event and it's upcoming, it should return the event's permalink
		$this->assertEquals( get_permalink( $this->test_event_id ), $url );
	}

	/**
	 * Test specific event URL generation
	 *
	 * @test
	 */
	public function test_specific_event_url_generation() {
		$url = $this->redirections->get_specific_event_url( $this->test_event_id );

		// Should return the event's permalink
		$this->assertEquals( get_permalink( $this->test_event_id ), $url );
	}

	/**
	 * Test next series event URL generation
	 *
	 * @test
	 */
	public function test_next_series_event_url_generation() {
		$url = $this->redirections->get_next_series_event_url( $this->test_event_id );

		// Since this is not a series event, it should return the fallback URL
		$this->assertEquals( home_url(), $url );

		// Make the Event part of a series by adding a parent event
		$parent_event_id = $this->factory->post->create(
			[
				'post_type'   => TEC::POSTTYPE,
				'post_status' => 'publish',
			]
		);

		// Set parent event dates
		$now      = current_time( 'mysql' );
		$tomorrow = date( 'Y-m-d H:i:s', strtotime( '+1 day' ) );
		update_post_meta( $parent_event_id, '_EventStartDate', $now );
		update_post_meta( $parent_event_id, '_EventEndDate', $tomorrow );

		// Set the parent event
		wp_update_post(
			[
				'ID'          => $this->test_event_id,
				'post_parent' => $parent_event_id,
			]
		);

		$url = $this->redirections->get_next_series_event_url( $parent_event_id );

		// Since ECP is not active, it should return the fallback URL
		$this->assertEquals( home_url(), $url );
	}

	/**
	 * Test non-event post type URL generation
	 *
	 * @test
	 */
	public function test_non_event_post_type_url_generation() {
		// Create a non-event post
		$post_id = $this->factory->post->create(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
			]
		);

		$url = $this->redirections->get_specific_event_url( $post_id );

		// Should return the fallback URL for non-event post types
		$this->assertEquals( home_url(), $url );
	}
}
