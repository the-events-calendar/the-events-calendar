<?php
namespace Tribe\Events\Aggregator;

use Tribe__Events__Aggregator__Service as Service;

class ServiceTest extends \Codeception\TestCase\WPTestCase {

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

		$this->assertInstanceOf( Service::class, $sut );
	}

	/**
	 * @test
	 * it should return api object if api key is valid for main site and not set on
	 */
	public function it_should_return_api_object_if_api_key_is_valid_for_main_site() {
		$key = 'foo-bar';
		update_option( 'pue_install_key_event_aggregator', $key );

		switch_to_blog( get_current_blog_id() );
		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $key, $api->key );
	}

	/**
	 * @test
	 * it should return main site local key if network key not set on main site
	 */
	public function it_should_return_main_site_local_key_if_network_key_not_set_on_main_site() {
		$local_key = 'foo-bar';

		delete_network_option( null, 'pue_install_key_event_aggregator' );

		switch_to_blog( get_current_blog_id() );
		update_option( 'pue_install_key_event_aggregator', $local_key );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $local_key, $api->key );
	}

	/**
	 * @test
	 * it should return main site local key if local and network keys are set on main site
	 */
	public function it_should_return_main_site_local_key_if_local_and_network_keys_are_set_on_main_site() {
		$local_key   = 'local-foo-bar';
		$network_key = 'network-foo-bar';

		switch_to_blog( get_current_blog_id() );
		update_option( 'pue_install_key_event_aggregator', $local_key );
		update_network_option( null, 'pue_install_key_event_aggregator', $network_key );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $local_key, $api->key );
	}

	/**
	 * @test
	 * it should return network key if network key set on main site
	 */
	public function it_should_return_network_key_if_network_key_set_on_main_site() {
		$network_key = 'foo-bar';
		delete_option( 'pue_install_key_event_aggregator' );
		update_network_option( null, 'pue_install_key_event_aggregator', $network_key );

		switch_to_blog( get_current_blog_id() );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $network_key, $api->key );
	}

	/**
	 * @test
	 * it should return error if api key is not set for main site
	 */
	public function it_should_return_error_if_api_key_is_not_set_for_main_site() {
		delete_option( 'pue_install_key_event_aggregator' );
		delete_network_option( null, 'pue_install_key_event_aggregator');

		switch_to_blog( get_current_blog_id() );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertWPError( $api );
	}

	/**
	 * @test
	 * it should return api object if key set in network option on subsite
	 */
	public function it_should_return_api_object_if_key_set_in_network_option_on_subsite() {
		$key  = 'foo-bar';
		$user = $this->factory()->user->create();
		$blog = $this->factory()->blog->create( [ 'domain' => 'sub', 'path' => '/', 'user_id' => $user ] );

		update_network_option( null, 'pue_install_key_event_aggregator', $key );

		switch_to_blog( $blog );
		wp_set_current_user( $user );
		delete_option( 'pue_install_key_event_aggregator' );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $key, $api->key );
	}

	/**
	 * @test
	 * it should return subsite api key if different from network key
	 */
	public function it_should_return_subsite_api_key_if_different_from_network_key() {
		$key         = 'foo-bar';
		$subsite_key = 'sub-foo-bar';
		$user        = $this->factory()->user->create();
		$blog        = $this->factory()->blog->create( [ 'domain' => 'sub', 'path' => '/', 'user_id' => $user ] );

		update_network_option( null, 'pue_install_key_event_aggregator', $key );

		switch_to_blog( $blog );
		wp_set_current_user( $user );
		update_option( 'pue_install_key_event_aggregator', $subsite_key );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $subsite_key, $api->key );
	}

	/**
	 * @test
	 * it should return subsite api key if network key not set
	 */
	public function it_should_return_subsite_api_key_if_network_key_not_set() {
		$subsite_key = 'sub-foo-bar';
		$user        = $this->factory()->user->create();
		$blog        = $this->factory()->blog->create( [ 'domain' => 'sub', 'path' => '/', 'user_id' => $user ] );

		delete_network_option( null, 'pue_install_key_event_aggregator' );

		switch_to_blog( $blog );
		wp_set_current_user( $user );
		update_option( 'pue_install_key_event_aggregator', $subsite_key );

		$sut = $this->make_instance();
		$api = $sut->api();

		$this->assertInstanceOf( \stdClass::class, $api );
		$this->assertEquals( $subsite_key, $api->key );
	}

	/**
	 * @return Service
	 */
	private function make_instance() {
		return new Service();
	}

}