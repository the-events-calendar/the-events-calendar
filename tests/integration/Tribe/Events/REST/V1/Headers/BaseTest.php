<?php
namespace Tribe\Events\REST\V1\Headers;

use Tribe__Events__Main as Main;
use Tribe__Events__REST__V1__Headers__Base as Base;

class BaseTest extends \Codeception\TestCase\WPTestCase {

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
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Base::class, $sut );
	}

	/**
	 * @test
	 * it should return the root REST URL when not hitting a single event
	 */
	public function it_should_return_the_root_rest_url_when_hitting_the_site_home() {
		$event = $this->factory()->post->create_and_get( [ 'post_type' => Main::POSTTYPE ] );

		global $post, $wp_query;
		$post = $event;
		$wp_query->is_single = true;

		$sut = $this->make_instance();

		$rest_url = $sut->get_rest_url();

		$this->assertRegExp( '#/events/v1/events/' . $event->ID . '$#', $rest_url );
	}

	/**
	 * @test
	 * it should return the single event GET REST URL when in a single event context
	 */
	public function it_should_return_the_single_event_get_rest_url_when_in_a_single_event_context() {
		global $wp_query;
		$wp_query->is_single = false;

		$sut = $this->make_instance();

		$rest_url = $sut->get_rest_url();

		$this->assertRegExp( '#/events/v1/$#' , $rest_url );
	}

	/**
	 * @return Base
	 */
	protected function make_instance() {
		return new Base();
	}
}