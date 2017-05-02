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
	 * @test
	 * it should return a category filtered root URL when hitting an event archive
	 */
	public function it_should_return_a_category_filtered_root_url_when_hitting_an_event_archive() {
		global $wp_query;
		$wp_query->is_single = false;
		$wp_query->tribe_is_event_category = true;
		$wp_query->set(Main::TAXONOMY,'cat1');

		$sut = $this->make_instance();

		$rest_url = $sut->get_rest_url();

		$this->assertRegExp( '#categories=cat1#' , $rest_url );
	}

	/**
	 * @test
	 * it should return a tag filtered root URL when hitting a tag archive
	 */
	public function it_should_return_a_tag_filtered_root_url_when_hitting_a_tag_archive() {
		global $wp_query;
		$wp_query->is_single = false;
		$wp_query->is_tag    = true;
		$wp_query->set( 'tag', 'tag1' );

		$sut = $this->make_instance();

		$rest_url = $sut->get_rest_url();

		$this->assertRegExp( '#tags=tag1#', $rest_url );
	}

	/**
	 * @return Base
	 */
	protected function make_instance() {
		return new Base();
	}
}