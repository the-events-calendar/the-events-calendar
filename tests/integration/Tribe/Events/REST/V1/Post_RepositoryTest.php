<?php

namespace Tribe\Events\REST\V1;

use Tribe\Events\Tests\Factories\Events as Event_Factory;
use Tribe__Events__REST__V1__Messages as Messages;
use Tribe__Events__REST__V1__Post_Repository as Post_Repository;

class Post_RepositoryTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->messages = new Messages();
		$this->factory()->event = new Event_Factory();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Post_Repository::class, $sut );
	}

	/**
	 * @test
	 * it should return a WP_Error when trying to get event data for non existing post
	 */
	public function it_should_return_a_wp_error_when_trying_to_get_event_data_for_non_existing_post() {
		$sut = $this->make_instance();

		$data = $sut->get_event_data( 22131 );

		/** @var \WP_Error $data */
		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return a WP_Error when trying to get event data for non event
	 */
	public function it_should_return_a_wp_error_when_trying_to_get_event_data_for_non_event() {
		$sut = $this->make_instance();

		$data = $sut->get_event_data( $this->factory()->post->create() );

		/** @var \WP_Error $data */
		$this->assertWPError( $data );
		$this->assertEquals( $this->messages->get_message( 'event-not-found' ), $data->get_error_message() );
	}

	/**
	 * @test
	 * it should return an event array representation if event
	 */
	public function it_should_return_an_event_array_representation_if_event() {
		$event = $this->factory()->event->create();

		$sut = $this->make_instance();
		$data = $sut->get_event_data( $event );

		$this->assertInternalType( 'array', $data );
	}


	/**
	 * @return Post_Repository
	 */
	private function make_instance() {
		return new Post_Repository( $this->messages );
	}
}