<?php
namespace Tribe\Events\Aggregator;

use Prophecy\Argument;
use Tribe__Events__Aggregator__Service as Service;

class ServiceTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__Events__Aggregator__API__Requests
	 */
	protected $requests;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->requests = $this->prophesize( \Tribe__Events__Aggregator__API__Requests::class );
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
		delete_network_option( null, 'pue_install_key_event_aggregator' );

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
	 * @test
	 * it should count import for main site if network activated and limited
	 */
	public function it_should_count_import_for_main_site_if_network_activated_and_limited() {
		$network_key = 'network-foo-bar';
		$expected    = 23;
		$this->requests->get( Argument::containingString( $network_key ), Argument::type( 'array' ) )
		               ->willReturn( $this->make_import_limit_mock_response( $expected ) );
		update_network_option( null, 'pue_install_key_event_aggregator', $network_key );

		switch_to_blog( get_current_blog_id() );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $expected, $limit );
	}

	/**
	 * @test
	 * it should share imports between subsites if network activated and limited
	 */
	public function it_should_share_imports_between_subsites_if_network_activated_and_limited() {
		$network_key = 'network-foo-bar';
		$expected    = 23;
		$this->requests->get( Argument::containingString( $network_key ), Argument::type( 'array' ) )
		               ->willReturn( $this->make_import_limit_mock_response( $expected ) );
		update_network_option( null, 'pue_install_key_event_aggregator', $network_key );

		$user = $this->factory()->user->create();
		$blog = $this->factory()->blog->create( [ 'domain' => 'sub1', 'path' => '/', 'user_id' => $user ] );

		switch_to_blog( $blog );
		wp_set_current_user( $user );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $expected, $limit );

		$user_2 = $this->factory()->user->create();
		$blog_2 = $this->factory()->blog->create( [ 'domain' => 'sub2', 'path' => '/', 'another_user_id' => $user_2 ] );

		switch_to_blog( $blog_2 );
		wp_set_current_user( $user_2 );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $expected, $limit );
	}

	/**
	 * @test
	 * it should apply subsite limit if subsite has local key
	 */
	public function it_should_apply_subsite_limit_if_subsite_has_local_key() {
		$network_key   = 'network-foo-bar';
		$network_limit = 23;
		$local_key_1   = 'local-key-1';
		$blog_1_limit  = 12;
		$local_key_2   = 'local-key-2';
		$blog_2_limit  = 6;
		$this->requests->get( Argument::containingString( $network_key ), Argument::type( 'array' ) )
		               ->willReturn( $this->make_import_limit_mock_response( $network_limit ) );
		$this->requests->get( Argument::containingString( $local_key_1 ), Argument::type( 'array' ) )
		               ->willReturn( $this->make_import_limit_mock_response( $blog_1_limit ) );
		$this->requests->get( Argument::containingString( $local_key_2 ), Argument::type( 'array' ) )
		               ->willReturn( $this->make_import_limit_mock_response( $blog_2_limit ) );

		update_network_option( null, 'pue_install_key_event_aggregator', $network_key );

		$user_1 = $this->factory()->user->create();
		$blog_1 = $this->factory()->blog->create( [ 'domain' => 'sub1', 'path' => '/', 'user_id' => $user_1 ] );

		switch_to_blog( $blog_1 );
		wp_set_current_user( $user_1 );
		update_option( 'pue_install_key_event_aggregator', $local_key_1 );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $blog_1_limit, $limit );

		$user_2 = $this->factory()->user->create();
		$blog_2 = $this->factory()->blog->create( [ 'domain' => 'sub2', 'path' => '/', 'another_user_id' => $user_2 ] );

		switch_to_blog( $blog_2 );
		wp_set_current_user( $user_2 );
		update_option( 'pue_install_key_event_aggregator', $local_key_2 );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $blog_2_limit, $limit );

		$user_3 = $this->factory()->user->create();
		$blog_3 = $this->factory()->blog->create( [ 'domain' => 'sub3', 'path' => '/', 'another_user_id' => $user_3 ] );

		switch_to_blog( $blog_3 );
		wp_set_current_user( $user_3 );
		delete_option( 'pue_install_key_event_aggregator' );

		$sut   = $this->make_instance();
		$limit = $sut->get_limit( 'import' );

		$this->assertEquals( $network_limit, $limit );
	}

	/**
	 * @return Service
	 */
	private function make_instance() {
		return new Service( $this->requests->reveal() );
	}

	/**
	 * @param $expected
	 *
	 * @return array
	 */
	protected function make_import_limit_mock_response( $expected ) {
		$mock_response = [
			'headers' => [ 'content-type' => 'json' ],
			'body'    => json_encode(
				[
					'status' => 'success',
					'data'   => [
						'limit' => [
							'import' => $expected
						]
					]
				]
			),
		];

		return $mock_response;
	}

}