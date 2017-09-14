<?php

namespace Tribe\REST\V1\Endpoints;

use Prophecy\Argument;
use Tribe__Events__REST__V1__Endpoints__Archive_Category as Archive;
use Tribe__Events__Main as Main;

class Archive_CategoryTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var \Tribe__Events__REST__V1__Validator__Interface
	 */
	protected $validator;

	/**
	 * @var \WP_REST_Terms_Controller
	 */
	protected $controller;

	/**
	 * @return Archive
	 */
	private function make_instance() {
		return new Archive( $this->messages->reveal(), $this->repository->reveal(), $this->validator->reveal(), $this->controller->reveal() );
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Archive::class, $sut );
	}

	/**
	 * It should return existing event categories
	 *
	 * @test
	 */
	public function should_return_existing_event_categories() {
		$term_response = new \WP_REST_Response( [ 'some' => 'prepared_data' ] );
		$term_response->header('X-WP-Total', 10);
		$term_response->header('X-WP-TotalPages', 2);
		$this->controller->get_items( Argument::type( \WP_REST_Request::class ) )->willReturn( $term_response );
		$this->repository->prepare_terms_data( Argument::type( 'array' ), Main::TAXONOMY )->willReturn( [ 'some' => 'prepared_data' ] );
		$sut     = $this->make_instance();
		$request = new \WP_REST_Request();

		$got = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $got );
		$this->assertEquals( [ 'some' => 'prepared_data' ], $got->data['categories'] );
	}

	/**
	 * It should return WP_Error ir there are no event categories in db
	 *
	 * @test
	 */
	public function should_return_wp_error_ir_there_are_no_event_categories_in_db() {
		$term_response = new \WP_REST_Response( [] );
		$term_response->header('X-WP-Total', 0);
		$term_response->header('X-WP-TotalPages', 0);
		$this->controller->get_items( Argument::type( \WP_REST_Request::class ) )->willReturn( $term_response );

		$sut     = $this->make_instance();
		$request = new \WP_REST_Request();

		$got = $sut->get( $request );

		$this->assertInstanceOf( \WP_Error::class, $got );
	}

	/**
	 * It should return WP_REST_Terms_Controller response if it is a WP_Error
	 *
	 * @test
	 */
	public function should_return_wp_rest_terms_controller_response_if_it_is_a_wp_error() {
		$this->controller->get_items( Argument::type( \WP_REST_Request::class ) )->willReturn( new \WP_Error() );

		$sut     = $this->make_instance();
		$request = new \WP_REST_Request();

		$got = $sut->get( $request );

		$this->assertInstanceOf( \WP_Error::class, $got );
	}

	public function setUp() {
		$this->messages   = $this->prophesize( \Tribe__REST__Messages_Interface::class );
		$this->repository = $this->prophesize( \Tribe__Events__REST__Interfaces__Post_Repository::class );
		$this->validator  = $this->prophesize( \Tribe__Events__Validator__Interface::class );
		$this->controller = $this->prophesize( \WP_REST_Terms_Controller::class );

		parent::setUp();
	}
}