<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Tribe__Documentation__Swagger__Provider_Interface as Documentation_Provider;
use Tribe__Events__REST__V1__Endpoints__Swagger_Documentation as Doc;

class Swagger_DocumentationTest extends \Codeception\TestCase\WPTestCase {

	protected $version = '1.0.0';

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

		$this->assertInstanceOf( Doc::class, $sut );
	}

	/**
	 * @test
	 * it should allow to register documentation providers at paths
	 */
	public function it_should_allow_to_register_documentation_providers_at_paths() {
		$sut = $this->make_instance();

		$sut->register_documentation_provider( '/foo', $this->prophesize( Documentation_Provider::class )->reveal() );
		$sut->register_documentation_provider( '/baz', $this->prophesize( Documentation_Provider::class )->reveal() );
		$sut->register_documentation_provider( '/bar/{var}', $this->prophesize( Documentation_Provider::class )->reveal() );
		$registered_providers = $sut->get_registered_documentation_providers();

		$this->assertCount( 3, $registered_providers );
		$this->assertArrayHasKey( '/foo', $registered_providers );
		$this->assertArrayHasKey( '/baz', $registered_providers );
		$this->assertArrayHasKey( '/bar/{var}', $registered_providers );
	}

	/**
	 * @test
	 * it should recursively call documentation providers for documentation
	 */
	public function it_should_recursively_call_documentation_providers_for_documentation() {
		$sut = $this->make_instance();

		$one = $this->prophesize( Documentation_Provider::class );
		$one->get_documentation()->shouldBeCalled();
		$two = $this->prophesize( Documentation_Provider::class );
		$two->get_documentation()->shouldBeCalled();
		$three = $this->prophesize( Documentation_Provider::class );
		$three->get_documentation()->shouldBeCalled();

		$sut->register_documentation_provider( '/foo', $one->reveal() );
		$sut->register_documentation_provider( '/baz', $two->reveal() );
		$sut->register_documentation_provider( '/bar/{var}', $three->reveal() );

		$sut->get_documentation();
	}

	/**
	 * @test
	 * it should allow registering definition providers
	 */
	public function it_should_allow_registering_definition_providers() {
		$sut = $this->make_instance();

		$sut->register_definition_provider( 'Foo', $this->prophesize( Documentation_Provider::class )->reveal() );
		$sut->register_definition_provider( 'Baz', $this->prophesize( Documentation_Provider::class )->reveal() );
		$sut->register_definition_provider( 'Bar', $this->prophesize( Documentation_Provider::class )->reveal() );
		$registered_providers = $sut->get_registered_definition_providers();

		$this->assertCount( 3, $registered_providers );
		$this->assertArrayHasKey( 'Foo', $registered_providers );
		$this->assertArrayHasKey( 'Baz', $registered_providers );
		$this->assertArrayHasKey( 'Bar', $registered_providers );
	}

	/**
	 * @test
	 * it should call each definition provider for definitions
	 */
	public function it_should_call_each_definition_provider_for_definitions() {
		$sut = $this->make_instance();

		$one = $this->prophesize( Documentation_Provider::class );
		$one->get_documentation()->shouldBeCalled();
		$two = $this->prophesize( Documentation_Provider::class );
		$two->get_documentation()->shouldBeCalled();
		$three = $this->prophesize( Documentation_Provider::class );
		$three->get_documentation()->shouldBeCalled();

		$sut->register_definition_provider( 'Foo', $one->reveal() );
		$sut->register_definition_provider( 'Baz', $two->reveal() );
		$sut->register_definition_provider( 'Bar', $three->reveal() );

		$sut->get_documentation();
	}

	/**
	 * @return Doc
	 */
	private function make_instance() {
		return new Doc( $this->version );
	}

}