<?php


class SwaggerDocumentationCest extends BaseRestCest {
	/**
	 * @test
	 * it should expose a Swagger documentation endpoint
	 */
	public function it_should_expose_a_swagger_documentation_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * @test
	 * it should return a JSON array containing headers in Swagger format
	 */
	public function it_should_return_a_json_array_containing_headers_in_swagger_format( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse() );
		$I->assertArrayHasKey( 'openapi', $response );
		$I->assertArrayHasKey( 'info', $response );
		$I->assertArrayHasKey( 'servers', $response );
		$I->assertArrayHasKey( 'components', $response );
		$I->assertArrayHasKey( 'paths', $response );
	}

	/**
	 * @test
	 * it should return the correct information
	 */
	public function it_should_return_the_correct_information( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse() );
		$I->assertArrayHasKey( 'info', $response );
		$info = (array) $response['info'];
		//version
		$I->assertArrayHasKey( 'version', $info );
		// title
		$I->assertArrayHasKey( 'title', $info );
		//description
		$I->assertArrayHasKey( 'description', $info );
	}

	/**
	 * @test
	 * it should return the site URL as host
	 */
	public function it_should_return_the_site_url_as_host( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse() );
		$I->assertArrayHasKey( 'servers', $response );
	}

	/**
	 * @test
	 * it should return TEC REST path as base path
	 */
	public function it_should_return_tec_rest_path_as_base_path( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse(), true );
		$server = $response['servers'][0]; // accepts array of server definitions, including the base url relative to the paths in the `paths` object
		$I->assertArrayHasKey( 'servers', $response );
		$I->assertEquals( $this->rest_url, $server['url'] );
	}

	/**
	 * @test
	 * it should contain information about the archive endpoint
	 */
	public function it_should_contain_information_about_the_archive_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse() );
		$I->assertArrayHasKey( 'paths', $response );
		$paths = (array) $response['paths'];
		$I->assertArrayHasKey( '/events', $paths );
		$I->assertArrayHasKey( 'get', (array)$paths['/events'] );
	}

	/**
	 * @test
	 * it should contain information about the single event endpoint
	 */
	public function it_should_contain_information_about_the_single_event_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->documentation_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = (array) json_decode( $I->grabResponse() );
		$I->assertArrayHasKey( 'paths', $response );
		$paths = (array) $response['paths'];
		$I->assertArrayHasKey( '/events/{id}', $paths );
		$I->assertArrayHasKey( 'get', (array)$paths['/events/{id}'] );
	}
}
