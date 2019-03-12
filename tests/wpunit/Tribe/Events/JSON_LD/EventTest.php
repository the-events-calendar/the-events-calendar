<?php
namespace Tribe\Events;

use Tribe__Events__JSON_LD__Event as JSON_LD__Event;

class JSON_LD__EventTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		JSON_LD__Event::unregister_all();

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	*/
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__JSON_LD__Event', $sut );
	}

	/**
	 * @test
	 * It should return an empty array if the input data is empty
	 *
	 * @since TBD
	 */
	public function it_should_return_empty_array_when_passed_empty_values() {
		$this->assertEquals( [], $this->make_instance()->get_data( [], [] ) );
	}

	/**
	 * @test
	 * it should return array with one post in it if trying to get data for one event
	 *
	 * @since TBD
	 */
	public function it_should_return_array_with_one_post_in_it_if_trying_to_get_data_for_one_post() {
		$post = $this->factory()->post->create();

		$sut  = $this->make_instance();
		$data = $sut->get_data( $post );

		$this->assertInternalType( 'array', $data );
		$this->assertCount( 1, $data );
		$this->assertContainsOnly( 'stdClass', $data );
	}

	/**
	 * @return \Tribe__Events__JSON_LD__Event
	 *
	 * @since TBD
	 */
	private function make_instance() {
		return new JSON_LD__Event();
	}
}