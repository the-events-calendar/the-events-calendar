<?php

namespace TEC\Events\Custom_Tables\V1\Updates;

use Codeception\TestCase\WPTestCase;
use WP_REST_Request;

class RequestsTest extends WPTestCase {
	public function http_method_provider() {
		return [
			'GET'    => [ 'GET' ],
			'POST'   => [ 'POST' ],
			'PATCH'  => [ 'PATCH' ],
			'PUT'    => [ 'PUT' ],
			'DELETE' => [ 'DELETE' ],
		];
	}

	/**
	 * It should build from HTTP w/ correct method
	 *
	 * @test
	 * @dataProvider http_method_provider
	 */
	public function should_build_from_http_w_correct_method( $method ) {
		$_SERVER['REQUEST_METHOD'] = $method;

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( $method, $request->get_method() );
	}

	public function http_route_provider() {
		return [
			'/'                                  => [ '/', [], [ 'id' => 0 ] ],
			'/wp-admin/post.php'                 => [ '/wp-admin/post.php', [], [ 'id' => 0 ] ],
			'/wp-admin/post.php?foo=bar'         => [
				'/wp-admin/post.php?foo=bar',
				[ 'foo' => 'bar' ],
				[ 'foo' => 'bar', 'id' => 0 ]
			],
			'/wp-admin/post.php?foo=bar&bar=baz' => [
				'/wp-admin/post.php?foo=bar&bar=baz',
				[ 'foo' => 'bar', 'bar' => 'baz' ],
				[ 'foo' => 'bar', 'bar' => 'baz', 'id' => 0 ]
			],
		];
	}

	/**
	 * It should build from HTTP with correct route
	 *
	 * @test
	 * @dataProvider http_route_provider
	 */
	public function should_build_from_http_with_correct_route( $route, $get_params, $expected_query_params ) {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI']    = $route;
		foreach ( $get_params as $key => $value ) {
			$_GET[ $key ] = $value;
		}

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( $route, $request->get_route() );
		$this->assertEquals( $expected_query_params, $request->get_query_params() );
	}

	public function http_body_params_provider() {
		return [

		];
	}

	/**
	 * It should build from http with correct body params
	 *
	 * @test
	 * @dataProvider http_route_provider
	 *
	 * @param $route
	 * @param $body_params
	 */
	public function should_build_from_http_with_correct_body_params( $route, $body_params, $expected_body_params ) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI']    = $route;
		foreach ( $body_params as $key => $value ) {
			$_POST[ $key ] = $value;
		}

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( $route, $request->get_route() );
		$this->assertEquals( $expected_body_params, $request->get_body_params() );
	}

	public function http_request_possible_id_locations() {
		return
			[
				'ID'      => [ 'ID' ],
				'post_id' => [ 'post_id' ],
				'post_ID' => [ 'post_ID' ],
				'id'      => [ 'post_ID' ],
				'post'    => [ 'post' ],
			];
	}

	/**
	 * It should correctly set the id param from diff. HTTP request locations
	 *
	 * @test
	 * @dataProvider http_request_possible_id_locations
	 */
	public function should_correctly_set_the_id_param_from_diff_http_request_locations( $key ) {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI']    = '/wp-admin/post.php';
		$_POST[ $key ]             = 23;

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( 23, $request->get_param( 'id' ) );
		$this->assertEquals( 23, $request['id'] );
	}

	/**
	 * It should allow filtering the HTTP locations keys looked up for the post ID
	 *
	 * @test
	 */
	public function should_allow_filtering_the_http_locations_keys_looked_up_for_the_post_id() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI']    = '/wp-admin/post.php';
		$_POST['_test_post_id']    = 23;

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( 0, $request->get_param( 'id' ) );
		$this->assertEquals( 0, $request['id'] );

		add_filter( 'tec_events_custom_tables_v1_request_factory_post_id_keys', static function ( array $locations = [] ) {
			$locations [] = '_test_post_id';

			return $locations;
		} );

		$request_2 = $requests->from_http_request();

		$this->assertEquals( 23, $request_2->get_param( 'id' ) );
		$this->assertEquals( 23, $request_2['id'] );
	}

	/**
	 * It should allow controlling the post ID location lookup order w/ filter
	 *
	 * @test
	 */
	public function should_allow_controlling_the_post_id_location_lookup_order_w_filter() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI']    = '/wp-admin/post.php';
		$_POST['ID']               = 23;
		$_POST['_test_post_id']    = 89;

		$requests = new Requests();
		$request         = $requests->from_http_request();

		$this->assertEquals( 23, $request->get_param( 'id' ) );
		$this->assertEquals( 23, $request['id'] );

		add_filter( 'tec_events_custom_tables_v1_request_factory_post_id_keys', static function ( array $locations = [] ) {
			array_unshift( $locations, '_test_post_id' );

			return $locations;
		} );

		$request_2 = $requests->from_http_request();

		$this->assertEquals( 89, $request_2->get_param( 'id' ) );
		$this->assertEquals( 89, $request_2['id'] );
	}

	public function http_method_and_post_id_provider() {
		return [
			'GET, no action, no post ID'     => [ 'GET', null, null, false ],
			'GET, trash action, no post ID'  => [ 'GET', '?action=trash', null, false ],
			'GET, delete action, no post ID' => [ 'GET', '?action=delete', null, false ],
			'GET, trash action, w/ post ID'  => [ 'GET', '?action=trash', 23, true ],
			'GET, delete action, w/ post ID' => [ 'GET', '?action=delete', 23, true ],
			'POST, no post ID' => ['POST', null, null, false],
			'POST, w/ post ID' => ['POST', null, 23, true],
			'PUT, no post ID' => ['PUT', null, null, false],
			'PUT, w/ post ID' => ['PUT', null, 23, true],
			'PATCH, no post ID' => ['PATCH', null, null, false],
			'PATCH, w/ post ID' => ['PATCH', null, 23, true],
			'DELETE, no post ID' => ['DELETE', null, null, false],
			'DELETE, w/ post ID' => ['DELETE', null, 23, true],
		];
	}

	/**
	 * It should allow identifying an update request from the method and post ID
	 *
	 * @test
	 * @dataProvider http_method_and_post_id_provider
	 */
	public function should_allow_identifying_an_update_request_from_the_method_and_post_id( $method, $query = null, $post_id = null, $expected = true ) {
		$request  = new WP_REST_Request();
		$request->set_method( $method );
		if ( $query ) {
			wp_parse_str( substr( $query, 1 ), $query_params );
			$request->set_query_params( $query_params );
		}
		if ( $post_id ) {
			$request->set_param( 'id', $post_id );
		}

		$requests          = new Requests();
		$is_update_request = $requests->is_update_request( $request );

		$this->assertEquals( $expected, $is_update_request );
	}
}
