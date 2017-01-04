<?php
namespace Tribe\Events\REST\V1;

use Tribe__Events__REST__V1__Main as Main;

class MainTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var string
	 */
	protected $site_url;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->site_url = get_option( 'siteurl' );
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

		$this->assertInstanceOf( Main::class, $sut );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL prefix
	 */
	public function it_should_return_the_right_rest_url_prefix() {
		$sut = $this->make_instance();

		$this->assertEquals( 'wp-json/tribe/events/v1', $sut->get_url_prefix() );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL prefix when non using built-in REST API function
	 */
	public function it_should_return_the_right_tec_rest_url_prefix_when_non_using_built_in_rest_api_function() {
		add_filter( 'tribe_events_rest_use_builtin', '__return_false' );

		$sut = $this->make_instance();

		$this->assertEquals( 'wp-json/tribe/events/v1', $sut->get_url_prefix() );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL for a path
	 */
	public function it_should_return_the_right_tec_rest_url_for_a_path() {
		$sut = $this->make_instance();

		$this->assertEquals( $this->site_url . '/?rest_route=/tribe/events/v1/some/path', $sut->get_url( 'some/path' ) );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL for a path when not using built-in functions
	 */
	public function it_should_return_the_right_tec_rest_url_for_a_path_when_not_using_built_in_functions() {
		add_filter( 'tribe_events_rest_use_builtin', '__return_false' );

		$sut = $this->make_instance();

		$this->assertEquals( $this->site_url . '/?rest_route=/tribe/events/v1/some/path', $sut->get_url( 'some/path' ) );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL for a path when using built-in functions and permalinks
	 */
	public function it_should_return_the_right_tec_rest_url_for_a_path_when_using_built_in_functions_and_permalinks() {
		$this->set_permalinks();

		$sut = $this->make_instance();

		$this->assertEquals( $this->site_url . '/wp-json/tribe/events/v1/some/path', $sut->get_url( 'some/path' ) );
	}

	/**
	 * @test
	 * it should return the right TEC REST URL for a path when not using built-in functions and permalinks
	 */
	public function it_should_return_the_right_tec_rest_url_for_a_path_when_not_using_built_in_functions_and_permalinks() {
		$this->set_permalinks();
		add_filter( 'tribe_events_rest_use_builtin', '__return_false' );

		$sut = $this->make_instance();

		$this->assertEquals( $this->site_url . '/wp-json/tribe/events/v1/some/path', $sut->get_url( 'some/path' ) );
	}

	/**
	 * @return Main
	 */
	protected function make_instance() {
		return new Main();
	}

	protected function set_permalinks() {
		/** @var \WP_Rewrite */
		global $wp_rewrite;
		$structure = '/%postname%/';
		$wp_rewrite->set_permalink_structure( $structure );
		update_option( 'permalink_structure', $structure );
		$wp_rewrite->init();
		$wp_rewrite->flush_rules( true );
	}
}